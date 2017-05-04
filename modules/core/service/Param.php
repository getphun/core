<?php 
/**
 * Param service
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Service;

class Param
{
    protected $params = [];
    
    public function __construct(){
        $this->params = \Phun::$req_params;
    }
    
    public function __get($name){
        return $this->params[$name] ?? null;
    }
}