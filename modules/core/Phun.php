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
    static $req_scheme = 'http';
    
    static private function _autoload(){
        if(!isset(self::$config['_autoload']))
            return;
        
        if(isset(self::$config['_autoload']['files'])){
            foreach(self::$config['_autoload']['files'] as $file)
                include_once BASEPATH . '/' . $file;
        }

        // composer autoload
        $composer_file = BASEPATH . '/vendor/autoload.php';
        if(is_file($composer_file))
            include $composer_file;
        
        spl_autoload_register(function($class){
            if(!isset(Phun::$config['_autoload']['classes'][$class]))
                return trigger_error('Class ' . $class . ' not registered');
                
            $file = Phun::$config['_autoload']['classes'][$class];
            include BASEPATH . '/' . $file;
            
            if(method_exists($class, '__init'))
                $class::__init();
        });
    }
    
    static private function _bootstrap(){
        
    }

    static private function _catchErrors(){
        set_error_handler(function($errno, $errstr, $errfile, $errline){
            if(!is_dev()){
                $f = fopen(BASEPATH . '/etc/log/error.log', 'a');
                $tx = date('Y-m-d H:i:s') . PHP_EOL;
                $tx.= $errstr . PHP_EOL;
                $tx.= $errfile . ' ( ' . $errline . ' )' . PHP_EOL;
                $tx.= PHP_EOL;
                fwrite($f, $tx);
                fclose($f);
            }

            $routes = Router::$routes[Router::$gate];
            if(!isset($routes['500']))
                throw new ErrorException($errstr, $errno, 0, $errfile, $errline);

            $route = $routes['500'];

            $req_ctrl   = $route['controller'];
            $req_action = $route['action'];

            self::$dispatcher = new $req_ctrl();
            self::$dispatcher->$req_action($errstr, $errfile, $errline);
        });
    }
    
    static private function _config(){
        $config_cache_file = BASEPATH . '/etc/cache/config.php';
        
        $module_configs = null;
        if(is_file($config_cache_file))
            $module_configs = include $config_cache_file;
        
        if(!$module_configs || !is_array($module_configs) || ENVIRONMENT === 'development'){
            $module_dir = BASEPATH . '/modules';
            $modules = array_diff(scandir($module_dir), ['.', '..']);
            
            $module_configs = [];
            foreach($modules as $mod){
                $mod_conf_file = $module_dir . '/' . $mod . '/config.php';
                $mod_conf = include $mod_conf_file;
                
                $mod_conf_used = [];
                foreach($mod_conf as $key => $val){
                    if(substr($key,0,2) !== '__')
                        $mod_conf_used[$key] = $val;
                }
                $module_configs = array_replace_recursive($module_configs, $mod_conf_used);
            }
            
            $tx = '<?php' . PHP_EOL;
            $tx.= 'return ' . var_export($module_configs, true) . ';';
            
            touch($config_cache_file);
            
            $ctn = file_get_contents($config_cache_file);
            while($ctn != $tx){
                $f = fopen($config_cache_file, 'w');
                fwrite($f, $tx);
                fclose($f);
                
                $ctn = file_get_contents($config_cache_file);
            }
        }
        
        // get env config
        $env_config     = BASEPATH . '/etc/config.' . ENVIRONMENT . '.php';
        if(!is_file($env_config))
            $env_config = BASEPATH . '/etc/config.php';
        $app_config     = include $env_config;
        
        if(!is_array($app_config))
            $app_config = [];
        
        $config = array_replace_recursive($module_configs, $app_config);
        
        // preset _gates
        if(!isset($config['_gates']['site']))
            $config['_gates']['site'] = ['path' => '/'];
        
        // preset routes
        if(!isset($config['_routes']['site']['404'])){
            $config['_routes']['site']['404'] = [
                'handler' => 'Core\\Controller\\Home::notFound'
            ];
        }
        if(!isset($config['_routes']['site']['siteHome'])){
            $config['_routes']['site']['siteHome'] = [
                'rule'    => '/',
                'handler' => 'Core\\Controller\\Home::index'
            ];
        }
        
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
            
        self::$req_uri    = $path;
        self::$req_scheme = self::$config['secure'] ? 'https' : 'http';
        self::$req_url    = self::$req_scheme . '://' . $_SERVER['SERVER_NAME'] . $path;
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
        Phun::_catchErrors();
        
        self::$services = self::$config['_services'];
        
        Phun::_autoload();
        Router::run();
        
        $req_params = Router::$params;
        $req_route  = Router::$route;
        $req_ctrl   = $req_route['controller'];
        $req_action = $req_route['action'];
        
        self::$req_params = $req_params;
        self::$dispatcher = new $req_ctrl();
        if(!method_exists(self::$dispatcher, $req_action))
            return trigger_error('Class ' . $req_ctrl . ' dont have ' . $req_action . ' method');
        self::$dispatcher->$req_action();
    }
}