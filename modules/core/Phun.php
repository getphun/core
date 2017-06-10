<?php
/**
 * Application management
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

use Core\Library\Router;

class Phun
{
    static $config;
    static $services = [];
    static $dispatcher = null;
    static $req_params = [];
    static $req_uri = '';
    static $req_url = '';
    
    static private function _autoload(){
        if(!isset(self::$config['_autoload']))
            return;
        
        if(isset(self::$config['_autoload']['files'])){
            foreach(self::$config['_autoload']['files'] as $file)
                include_once BASEPATH . '/' . $file;
        }
        
        spl_autoload_register(function($class){
            if(!isset(Phun::$config['_autoload']['classes'][$class]))
                throw new \Exception('Class ' . $class . ' not registered');
            
            $file = Phun::$config['_autoload']['classes'][$class];
            include BASEPATH . '/' . $file;
            
            if(method_exists($class, '__init'))
                $class::__init();
        });
    }
    
    static private function _bootstrap(){
        
    }
    
    static private function _config(){
        $config_cache_file = BASEPATH . '/etc/cache/config.php';
        
        if(!is_file($config_cache_file) || ENVIRONMENT === 'development'){
            $module_dir = BASEPATH . '/modules';
            $modules = array_diff(scandir($module_dir), ['.', '..']);
            
            $mod_config = [];
            foreach($modules as $mod){
                $mod_conf_file = $module_dir . '/' . $mod . '/config.php';
                $mod_conf = include $mod_conf_file;
                
                $mod_conf_used = [];
                foreach($mod_conf as $key => $val){
                    if(substr($key,0,2) !== '__')
                        $mod_conf_used[$key] = $val;
                }
                $mod_config = array_replace_recursive($mod_config, $mod_conf_used);
            }
            
            $tx = '<?php' . PHP_EOL;
            $tx.= 'return ' . var_export($mod_config, true) . ';';
            
            file_put_contents($config_cache_file, $tx);
        }
        
        $module_configs = include $config_cache_file;
        $app_config     = include BASEPATH . '/etc/config.php';
        
        $config = array_replace_recursive($module_configs, $app_config);
        
        self::$config = $config;
    }
    
    static private function _env(){
        $env_file = BASEPATH . '/etc/.env';
        $env = file_get_contents($env_file);
        $env = trim($env);
        
        define('ENVIRONMENT', $env);
        
        if(ENVIRONMENT === 'development'){
            error_reporting(-1);
            ini_set('display_errors', 1);
        }else{
            ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        }
    }
    
    static function _uri(){
        $path = '/' . trim($_SERVER['REQUEST_URI'], '/');
        $path = preg_replace('!\?.*$!', '', $path);
        $path = chop($path, '/');
        if(!$path)
            $path = '/';
            
        self::$req_uri = $path;
        self::$req_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $path;
    }
    
    static private function _resFromCache(){
        if(ENVIRONMENT === 'development')
            return;
        
        $cache_name  = self::$req_url;
        $query_cache = self::$config['query_cache'] ?? [];
        $cache_query = [];
        
        foreach($query_cache as $name){
            if(isset($_GET[$name]))
                $cache_query[$name] = $_GET[$name];
        }
        
        if($cache_query)
            $cache_name.= '?' . http_build_query($cache_query);
        $cache_name = 'req-' . md5($cache_name) . '.php';
        
        $cache_file = BASEPATH . '/etc/cache/' . $cache_name;
        if(!is_file($cache_file))
            return;
        
        include $cache_file;
    }
    
    static function _timezone(){
        date_default_timezone_set(self::$config['timezone']);
    }
    
    static function run(){
        Phun::_env();
        Phun::_bootstrap();
        Phun::_config();
        Phun::_timezone();
        Phun::_uri();
        Phun::_resFromCache();
        
        self::$services = self::$config['_services'];
        
        Phun::_autoload();
        Router::run();
        
        $req_params = Router::$params;
        $req_route  = Router::$route;
        $req_ctrl   = $req_route['controller'];
        $req_action = $req_route['action'];
        
        self::$req_params = $req_params;
        
        try{
            self::$dispatcher = new $req_ctrl();
            self::$dispatcher->$req_action();
        }catch(Exception $e){
            echo $e->getMessage();
            if(ENVIRONMENT == 'development'){
                echo '<pre>';
                echo $e->getTraceAsString();
                echo '</pre>';
            }
        }
    }
}