<?php
/**
 * Application management
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

class Phun
{
    static $config;
    static $service = [];
    static $dispatcher = null;
    
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
    
    static function run(){
        Phun::_env();
        Phun::_bootstrap();
        Phun::_config();
        Phun::_autoload();
        
        Core\Library\Router::run();
        
        $req_params = Core\Library\Router::$params;
        $req_route  = Core\Library\Router::$route;
        $req_ctrl   = $req_route['controller'];
        $req_action = $req_route['action'];
        
        try{
            self::$dispatcher = new $req_ctrl();
            self::$dispatcher->$req_action();
        }catch(Exception $e){
            echo $e->getMessage();
            echo '<pre>';
            echo $e->getTraceAsString();
            echo '</pre>';
        }
    }
}