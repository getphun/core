<?php
/**
 * Main router
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Library;

class Router
{
    static $config;
    
    static $routes;
    static $gates;
    
    static $route;
    static $gate;
    
    static $params = [];
    static $params_host = [];
    static $params_path = [];
    
    static private function matchGates(){
        $host = $_SERVER['SERVER_NAME'];
        $host_arr = explode('.', $host);
        $path = \Phun::$req_uri;
        
        foreach(self::$gates as $name => $gate){
        
            if(isset($gate['host'])){
                
                if(isset($gate['host_arr'])){
                    
                    foreach($gate['host_arr'] as $index => $part){
                        if(!isset($host_arr[$index])){
                            self::$params_host = [];
                            continue 2;
                        }
                        
                        if(substr($part,0,1) === ':'){
                            $part_name = substr($part,1);
                            self::$params_host[$part_name] = $host_arr[$index];
                            
                        }else{
                            if($part != $host_arr[$index]){
                                self::$params_host = [];
                                continue 2;
                            }
                        }
                    }
                    
                }elseif($host != $gate['host']){
                    continue;
                    
                }
            }
            
            // now match the path
            $path_lenth = strlen($gate['path']);
            if(substr($path, 0, $path_lenth) !== $gate['path']){
                self::$params_host = [];
                continue;
            }
            
            self::$gate = $name;
            break;
        }
        
        if(!self::$gate)
            self::$gate = 'site';
    }
    
    static private function matchRoutes(){
        $path = \Phun::$req_uri;
        $path_arr = explode('/', $path);
        
        $routes = self::$routes[self::$gate];
        $gate   = self::$gates[self::$gate];
        $gate_path = $gate['path'];
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        foreach($routes as $name => $route){
            if($name == '404' || $name == '500')
                continue;
            
            if($route['method'] != 'ANY' && $method != $route['method'])
                continue;
            
            if(isset($route['rule_arr'])){
                $temp_route = [];
                
                foreach($route['rule_arr'] as $index => $part){
                    if(!array_key_exists($index, $path_arr)){
                        self::$params_path = [];
                        continue 2;
                    }
                    
                    if(substr($part,0,1) === ':'){
                        $part_name = substr($part,1);
                        self::$params_path[$part_name] = $path_arr[$index];
                        
                    }else{
                        if($part != $path_arr[$index]){
                            self::$params_path = [];
                            continue 2;
                        }
                    }
                    
                    $temp_route[] = $path_arr[$index];
                }
                
                $temp_route = implode('/', $temp_route);
                
                if($temp_route != $path){
                    self::$params_path = [];
                    continue;
                }
                
                self::$route = $route;
                break;
            }else{
                if($route['rule'] === $path){
                    self::$route = $route;
                    break;
                }
            }
        }
        
        if(!self::$route)
            self::$route = $routes['404'];
    }
    
    static function run(){
        $config_cache_file = BASEPATH . '/etc/cache/routes.php';
        $config = null;
        
        if(is_file($config_cache_file))
            $config = include $config_cache_file;
        
        if(ENVIRONMENT === 'development' || !$config || !isset($config['routes'])){
            $config = [
                '_name_gate' => []
            ];
            
            // format gates
            $gates = \Phun::$config['_gates'];
            foreach($gates as $name => $gate){
                $gate['name'] = $name;
                
                if(isset($gate['host'])){
                    $gate['host'] = str_replace('HOST', \Phun::$config['host'], $gate['host']);
                    if(strstr($gate['host'], ':'))
                        $gate['host_arr'] = explode('.', $gate['host']);
                }
                
                $gates[$name] = $gate;
            }
            
            $config['gates'] = $gates;
            
            // format routes
            $routes = \Phun::$config['_routes'];
            foreach($routes as $gate => $rous){
                if(!isset($gates[$gate]))
                    throw new \Exception('Gate named `' . $gate . '` is not registered');
                
                $gate_path = $gates[$gate]['path'];
                
                foreach($rous as $name => $route){
                    if(isset($route['module'])){
                        if(!module_exists($route['module'])){
                            unset($routes[$gate][$name]);
                            continue;
                        }
                    }
                    $route['name'] = $name;
                    if($name != '404' && $name != '500'){
                        $config['_name_gate'][$name] = $gate;
                        
                        $route['rule'] = '/' . trim($gate_path . '/' . trim($route['rule'], '/'), '/');
                        if(strstr($route['rule'], ':'))
                            $route['rule_arr']= explode('/', $route['rule']);
                    }
                    
                    $handler = explode('::', $route['handler']);
                    
                    $route['controller'] = $handler[0] . 'Controller';
                    $route['action'] = isset($handler[1]) ? $handler[1] : 'index';
                    $route['action'].= 'Action';
                    
                    if(!isset($route['method']))
                        $route['method'] = 'ANY';
                    $route['method'] = strtoupper($route['method']);
                    
                    $routes[$gate][$name] = $route;
                }
            }
            
            // sort by priority
            foreach($routes as $name => $route){
                uasort($route, function($a, $b){
                    $ap = $a['priority'] ?? 1000000;
                    $bp = $b['priority'] ?? 1000000;
                    return $bp - $ap;
                });
                $routes[$name] = $route;
            }
            
            $config['routes'] = $routes;
            
            $tx = '<?php' . PHP_EOL;
            $tx.= 'return ' . var_export($config, true) . ';';
            
            touch($config_cache_file);
            
            $ctn = file_get_contents($config_cache_file);
            while($ctn != $tx){
                $f = fopen($config_cache_file, 'w');
                fwrite($f, $tx);
                fclose($f);
                
                $ctn = file_get_contents($config_cache_file);
            }
        }
        
        self::$routes = $config['routes'];
        self::$gates  = $config['gates'];
        self::$config = $config;
        
        self::matchGates();
        self::matchRoutes();
        
        self::$params = array_merge(self::$params_path, self::$params_host);
    }
}