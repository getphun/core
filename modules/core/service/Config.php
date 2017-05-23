<?php
/**
 * Config service
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Service;

class Config
{
    protected $configs = [];
    
    public function __construct(){
        $this->configs = &\Phun::$config;
    }
    
    public function __get($name){
        return $this->configs[$name] ?? null;
    }
    
    public function set(){
        $args = func_get_args();
        $arr  = [];
        $value= array_pop($args);
        
        $sarr = &$arr;
        foreach($args as $arg){
            $sarr[$arg] = [];
            $sarr = &$sarr[$arg];
        }
        $sarr = $value;
        
        $this->configs = array_replace_recursive($this->configs, $arr);
    }
}