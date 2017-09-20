<?php
/**
 * Response service
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Service;
use Core\Library\View;

class Response
{
    
    private $cache = 0;
    
    private $res = [
        'cookies' => [],
        'headers' => [
            'X-Powered-By' => 'Phun',
            'X-Message' => 'Let get some phun together'
        ],
        'content' => ''
    ];
    
    public function __construct(){
        
    }
    
    private function _sendContent(){
        echo $this->res['content'];
    }
    
    private function _sendHeader(){
        foreach($this->res['headers'] as $header => $value){
            if(!is_array($value))
                header($header . ': ' . $value);
            else{
                $value = array_unique($value);
                foreach($value as $val)
                    header($header . ': ' . $val);
            }
        }
    }
    
    public function addContent(String $text){
        $this->res['content'].= $text;
    }
    
    public function addCookie($name, $value, $expiration){
        $host = \Phun::$dispatcher->config->host;
        setcookie($name, $value, time() + $expiration, '/', $host);
    }
    
    public function addHeader(String $name, $content){
        $this->res['headers'][$name] = $content;
    }
    
    public function cache($expiration){
        $this->cache = $expiration;
    }
    
    public function getHeader($name){
        return $this->res['headers'][$name] ?? null;
    }
    
    public function redirect(String $url, $code=302){
        header('Location: ' . $url, true, $code);
        exit;
    }
    
    public function render($gate, $view, $params=[]){
        $html = new View($gate, $view, $params);
        $this->addContent($html->content);
        unset($html);
    }
    
    public function send($content=null, $headers=[]){
        if($headers){
            foreach($headers as $name => $value)
                $this->addHeader($name, $value);
        }
        
        if($content)
            $this->res['content'] = $content;
        
        // add header Content-Length
        if(!$this->getHeader('Content-Length'))
            $this->addHeader('Content-Length', strlen($this->res['content']));
        
        if($this->cache){
            if(!is_dev()){
                if(!$this->getHeader('Cache-Control'))
                    $this->addHeader('Cache-Control', 'max-age=60');
                if(!$this->getHeader('ETag')){
                    $etag = md5($this->res['content']) . '-' . $this->cache;
                    $this->addHeader('ETag',  $etag);
                }
            }
            
            \Phun::$dispatcher->cache->save_output($this->res, $this->cache);
        }
        
        $this->_sendHeader();
        $this->_sendContent();
        exit;
    }
    
    // this won't included on caches
    public function setStatus($status){
        http_response_code($status);
    }
}