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
        $this->configs = \Phun::$config;
    }
    
    public function __get($name){
        return $this->configs[$name] ?? null;
    }
}