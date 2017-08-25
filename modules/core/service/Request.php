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
    public $ip = '';
    public $_INPUT = null;
    
    public function __construct(){
        $this->method = $this->getServer('REQUEST_METHOD');
        $this->uri = \Phun::$req_uri;
        $this->url = $this->getServer('REQUEST_SCHEME')
                   . '://'
                   . $this->getServer('HTTP_HOST')
                   . $this->getServer('REQUEST_URI');
    }
    
    public function get($name, $def=null){
        return $this->getPost($name)
            ?? $this->getQuery($name)
            ?? $this->getFile($name)
            ?? $def;
    }
    
    public function getCookie($name, $def=null){
        return $_COOKIE[$name] ?? $def;
    }
    
    public function getFile($name){
        return $_FILES[$name] ?? null;
    }
    
    public function getIP(){
        if($this->ip)
            return $this->ip;
        
        if (getenv('HTTP_CLIENT_IP'))
            $this->ip = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $this->ip = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $this->ip = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $this->ip = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
        $this->ip = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $this->ip = getenv('REMOTE_ADDR');
        else
            $this->ip = 'UNKNOWN';
        
        return $this->ip;
    }
    
    public function getPost($name, $def=null){
        if($this->method !== 'POST'){
            if(is_null($this->_INPUT)){
                parse_str(file_get_contents("php://input"), $input_data);
                $this->_INPUT = $input_data;
            }
            return $this->_INPUT[$name] ?? $def;
        }
        
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