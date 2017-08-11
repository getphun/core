<?php
/**
 * Cache service
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Service;

class Cache
{
    protected $memory = [];
    protected $output_name;
    
    public function get($name){
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        if(!is_file($cache_file))
            return null;
        return include $cache_file;
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
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        if(is_file($cache_file))
            return unlink($cache_file);
    }
    
    public function removeOutput($page){
        $cache_name = 'req-' . md5($page);
        $this->remove($cache_name);
    }
    
    public function save($name, $content, $expiration){
        $nl = PHP_EOL;
        $expired = time() + $expiration;
        
        $tx = '<?php' . $nl . $nl;
        $tx.= 'if(time() > ' . $expired . ')' . $nl;
        $tx.= '    return !unlink(__FILE__);' . $nl;
        
        $php = var_export($content, true);
        $php = str_replace('stdClass::__set_state', '(object)', $php); // Hope my mom never find this.
        
        $tx.= 'return ' . $php . ';';
        
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        $f = fopen($cache_file, 'w');
        fwrite($f, $tx);
        fclose($f);
        
        return true;
    }
    
    public function save_output($res, $expiration){
        $nl = PHP_EOL;
        $expired = time() + $expiration;
        
        $name = $this->getOutputName();
        
        $tx = '<?php' . $nl . $nl;
        $tx.= 'if(time() > ' . $expired . ')' . $nl;
        $tx.= '    return unlink(__FILE__);' . $nl . $nl;
        
        foreach($res['headers'] as $key => $value){
            if(is_string($value))
                $tx.= 'header(\'' . $key . ': ' . $value . '\');' . $nl;
            elseif(is_array($value)){
                foreach($value as $val)
                    $tx.= 'header(\'' . $key . ': ' . $val . '\');' . $nl;
            }
        }
        $tx.= '?>' . $res['content'] . '<?php exit; ?>';
        
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        $f = fopen($cache_file, 'w');
        fwrite($f, $tx);
        fclose($f);
        
        return true;
    }
    
    public function total(){
        return count( array_diff( scandir( BASEPATH . '/etc/cache/' ), ['.','..','.gitkeep'] ) );
    }
    
    public function truncate(){
        $files = array_diff( scandir( BASEPATH . '/etc/cache/' ), ['.', '..', '.gitkeep'] );
        foreach($files as $file){
            $file_abs = BASEPATH . '/etc/cache/' . $file;
            if(is_file($file_abs))
                unlink($file_abs);
        }
    }
}