<?php
/**
 * Router service
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Service;

class Router
{

    public function to($name, $args=[]){
        $config = \Core\Library\Router::$config;
        $gate_name = $config['_name_gate'][$name];
        $gate = $config['gates'][$gate_name];
        $route = $config['routes'][$gate_name][$name];
        
        $result = '';
        if(isset($gate['host']))
            $result = 'http://' . $gate['host'];
        
        $result.= $route['rule'];
        
        foreach($args as $name => $value)
            $result = str_replace(':' . $name, $value, $result);
        
        return $result;
    }
    
    public function __get($name){
        if($name === 'gate'){
            $gates = \Core\Library\Router::$gates;
            $gate  = \Core\Library\Router::$gate;
            return $gates[$gate];
        }
        if($name === 'route')
            return \Core\Library\Router::$route;
    }
}