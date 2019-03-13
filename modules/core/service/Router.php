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
    public $protocol = 'http';
    
    public function __construct(){
        if(\Phun::$dispatcher->config->secure)
            $this->protocol = 'https';
    }
    
    public function exists($name){
        return isset( \Core\Library\Router::$config['_name_gate'][$name] );
    }
    
    public function to($name, $args=[], $query=false){
        $config = \Core\Library\Router::$config;
        $gate_name = $config['_name_gate'][$name];
        $gate = $config['gates'][$gate_name];
        $route = $config['routes'][$gate_name][$name];
        
        $result = '';
        if(isset($gate['host']))
            $result = $this->protocol . '://' . $gate['host'];
        
        $result.= $route['rule'];

        $keys = array_map('strlen', array_keys($args));
        array_multisort($keys, SORT_DESC, $args);
        
        foreach($args as $name => $value){
            if(is_string($value) || is_numeric($value))
                $result = str_replace(':' . $name, $value, $result);
        }
        
        if($query)
            $result.= '?' . http_build_query($query);
        
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