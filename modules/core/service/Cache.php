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
    
    public function get($name){
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        if(!is_file($cache_file))
            return null;
        return include $cache_file;
    }
    
    public function remove($name){
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        if(is_file($cache_file))
            return unlink($cache_file);
    }
    
    public function save($name, $content, $expiration){
        $nl = PHP_EOL;
        $expired = time() + $expiration;
        
        $tx = '<?php' . $nl . $nl;
        $tx.= 'if(time() > ' . $expired . ')' . $nl;
        $tx.= '    return !unlink(__FILE__);' . $nl;
        $tx.= 'return ' . var_export($content, true) . ';';
        
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        $f = fopen($cache_file, 'w');
        fwrite($f, $tx);
        fclose($f);
        
        return true;
    }
    
    public function save_output($name, $res, $expiration){
        $nl = PHP_EOL;
        $expired = time() + $expiration;
        
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