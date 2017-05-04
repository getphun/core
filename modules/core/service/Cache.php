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
        $cache_file = BASEPATH . '/etc/cache/' . $name;
        if(!is_file($cache_file))
            return null;
        
        $ctn = file_get_contents($cache_file);
        $ctn = unserialize($ctn);
        
        if($ctn['expired'] < time()){
            unlink($cache_file);
            return null;
        }
        
        return $ctn['content'];
    }
    
    public function remove($name){
        $cache_file = BASEPATH . '/etc/cache/' . $name;
        if(is_file($cache_file))
            unlink($cache_file);
        return true;
    }
    
    public function save($name, $content, $expiration){
        $ctn = [
            'content' => $content,
            'expired' => time() + $expiration
        ];
        
        $cache_file = BASEPATH . '/etc/cache/' . $name;
        $f = fopen($cache_file, 'w');
        fwrite($f, serialize($ctn));
        fclose($f);
        
        return true;
    }
}