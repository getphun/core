<?php
/**
 * View management
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Library;

class View
{
    public $content = '';
    
    private $gate = 'site';
    private $view = 'index';
    private $params = [];
    private $base = '';
    
    public function __construct($gate, $view, $params){
        $this->gate = $gate;
        $this->view = $view;
        $this->params = $params;
        $this->base = BASEPATH . '/theme/' . $this->gate;
        
        $theme_file = $this->base . '/' . $view . '.phtml';
        
        ob_start();
        if($params)
            extract($params);
        include $theme_file;
        $this->content = ob_get_contents();
        ob_end_clean();
    }
    
    public function asset($file){
        if(substr($file,0,2) == '//' || substr($file,0,4) == 'http')
            return $file;
        return '/theme/' . $this->gate . '/static/' . $file;
    }
    
    public function partial($view, $params=[]){
        if($this->params)
            extract($this->params);
        if($params)
            extract($params);
        $theme_file = $this->base . '/' . $view . '.phtml';
        include $theme_file;
    }
    
    public function __get($name){
        return \Phun::$dispatcher->$name;
    }
}