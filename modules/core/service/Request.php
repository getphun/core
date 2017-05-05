<?php
/**
 * Request service
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Service;

class Request
{
    public $method = 'GET';
    public $uri = '/';
    
    public function __construct(){
        $this->method = $this->getServer('REQUEST_METHOD');
        $this->uri = \Phun::$req_uri;
    }
    
    public function get($name, $def=null){
        return $this->getQuery($name)
            ?? $this->getPost($name)
            ?? $this->getFile($name)
            ?? $def;
    }
    
    public function getCookie($name, $def=null){
        return $_COOKIE[$name] ?? $def;
    }
    
    public function getFile($name){
        return $_FILES[$name] ?? null;
    }
    
    public function getPost($name, $def=null){
        return $_POST[$name] ?? $def;
    }
    
    public function getQuery($name, $def=null){
        return $_GET[$name] ?? $def;
    }
    
    public function getServer($name, $def=null){
        return $_SERVER[$name] ?? $def;
    }
    
    public function isAjax(){
        $header = $this->getServer('HTTP_X_REQUESTED_WITH');
        return $header && strtolower($header) === 'xmlhttprequest';
    }
}