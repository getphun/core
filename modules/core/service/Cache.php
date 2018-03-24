<?php
/**
 * Cache service
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Service;
use Core\Cache\File as CFile;
use Core\Cache\Redis as CRedis;

class Cache
{
    protected $output_name;
    protected $driver;

    public function __construct(){
        $dis = \Phun::$dispatcher;
        $cache_config = $dis->config->cache;

        switch($cache_config['driver']){
            case 'file':
                $this->driver = new CFile();
                break;
            case 'redis':
                $this->driver = new CRedis($cache_config['redis']);
                break;
        }

        if(!$this->driver)
            throw new \Exception('Cache driver is invalid');
    }
    
    public function get($name){
        return $this->driver->get($name);
    }
    
    public function getOutputName(){
        if($this->output_name)
            return $this->output_name;
        
        $dis = \Phun::$dispatcher;
        
        $cache_name  = \Phun::$req_url;
        $query_cache = $dis->config->query_cache ?? [];
        $cache_query = [];
        
        foreach($query_cache as $name){
            if($dis->req->getQuery($name))
                $cache_query[$name] = $dis->req->getQuery($name);
        }
        
        if($cache_query)
            $cache_name.= '?' . http_build_query($cache_query);
        
        $cache_name = 'req-' . md5($cache_name);
        
        $this->output_name = $cache_name;
        return $cache_name;
    }
    
    public function remove($name){
        return $this->driver->remove($name);
    }
    
    public function removeOutput($page){
        $name = 'req-' . md5($page);

        $cache_file = BASEPATH . '/etc/cache/' . $name . '.text';
        if(is_file($cache_file))
            unlink($cache_file);
        
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        if(is_file($cache_file))
            return unlink($cache_file);
    }
    
    public function save($name, $content, $expiration){
        return $this->driver->save($name, $content, $expiration);
    }
    
    public function saveOutput($res, $expiration){
        $nl = PHP_EOL;
        $expired = time() + $expiration;
        
        $name = $this->getOutputName();
        
        $tx = '<?php' . $nl . $nl;
        $tx.= 'if(time() > ' . $expired . ')' . $nl;
        $tx.= '    return unlink(__FILE__);' . $nl . $nl;
        
        $token = $res['headers']['ETag'] ?? null;
        if(!is_dev() && $token){
            $tx.= '$token = $_SERVER[\'HTTP_IF_NONE_MATCH\'] ?? NULL;' . $nl;
            $tx.= 'if($token === \''.$token.'\'){' . $nl;
            $tx.= '    http_response_code(304);' . $nl;
            $tx.= '    header(\'ETag: ' . $token . '\');' . $nl;
            $tx.= '    exit;' . $nl;
            $tx.= '}' . $nl . $nl;
        }
        
        foreach($res['headers'] as $key => $value){
            if(!is_array($value))
                $tx.= 'header(\'' . $key . ': ' . $value . '\');' . $nl;
            elseif(is_array($value)){
                foreach($value as $val)
                    $tx.= 'header(\'' . $key . ': ' . $val . '\');' . $nl;
            }
        }
        $tx.= 'echo file_get_contents(__DIR__ . \'/' . $name . '.text\');' . $nl;
        $tx.= 'exit;';
        
        // php file
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        $f = fopen($cache_file, 'w');
        fwrite($f, $tx);
        fclose($f);
        
        // text file
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.text';
        $f = fopen($cache_file, 'w');
        fwrite($f, $res['content']);
        fclose($f);
        
        return true;
    }
    
    public function total(){
        return $this->driver->total();
    }
    
    public function truncate(){
        return $this->driver->truncate();
    }
}