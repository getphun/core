<?php
/**
 * Default system controller
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

use Core\Library\Router;

class Controller
{
    /**
     * Send data as JSON
     * @param array $data The data to send.
     */
    public function ajax($data){
        $data = json_encode($data);
        $this->res->send($data, ['Content-Type' =>  'application/json; charset=UTF-8']);
    }
    
    /**
     * Redirect to other URL
     * @param string $url Target redirect URL
     * @param integer $code HTTP Status code, default 302
     */
    public function redirect($url, $code=302){
        $this->res->redirect($url, $code);
    }
    
    /**
     * Redirect to some router URL
     * @param string $name Router name
     * @param array $args Router args
     * @param array $query Router query
     * @param integer $code HTTP Status code, default 302
     */
    public function redirectUrl($name, $args=[], $query=[], $code=302){
        $url = $this->router->to($name, $args, $query);
        $this->redirect($url, $code);
    }
    
    /**
     * Render and send view
     * @param string $view The view path relative to gate theme
     * @param array $params Data to send to view on rendering
     * @param integer $cache Cache the view for this seconds. Null to not to cache
     */
    public function respond($view, $params=[], $cache=null){
        $this->res->render(Router::$gate, $view, $params);
        
        if($cache)
            $this->res->cache($cache);
        
        $this->res->send();
    }
    
    /**
     * Global show 404
     */
    public function show404(){
        $gate = Router::$gate;
        $routes = Phun::$config['_routes'][$gate];
        if(!isset($routes['404']))
            return $this->ajax(['error'=>'404']);
        
        $route = $routes['404'];
        
        $handler = explode('::', $route['handler']);
        $cls = $handler[0] . 'Controller';
        $disp = new $cls();
        $disp->{$handler[1] . 'Action'}();
    }
    
    public function __get($name){
        // services?
        if(is_string(Phun::$services[$name])){
            $class = Phun::$services[$name];
            Phun::$services[$name] = new $class();
        }
        
        return Phun::$services[$name];
    }
}