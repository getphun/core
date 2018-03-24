<?php 
/**
 * Core file cache
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Cache;

class File
{

	public function __construct(){

	}

	public function get($name){
		$cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        if(!is_file($cache_file))
            return null;
        return include $cache_file;
	}

	public function remove($name){
		$cache_file = BASEPATH . '/etc/cache/' . $name . '.text';
        if(is_file($cache_file))
            unlink($cache_file);
        
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        if(is_file($cache_file))
            return unlink($cache_file);
	}

	public function save($name, $content, $expiration){
		$nl = PHP_EOL;
        $expired = time() + $expiration;
        
        $tx = '<?php' . $nl . $nl;
        $tx.= 'if(time() > ' . $expired . ')' . $nl;
        $tx.= '    return !unlink(__FILE__);' . $nl;
        
        $php = var_export($content, true);
        $php = str_replace('stdClass::__set_state', '(object)', $php); // Hope my mom never find this.
        
        $tx.= 'return ' . $php . ';';
        
        $cache_file = BASEPATH . '/etc/cache/' . $name . '.php';
        $f = fopen($cache_file, 'w');
        fwrite($f, $tx);
        fclose($f);
        
        return true;
	}

	public function total(){
		return count( array_diff( scandir( BASEPATH . '/etc/cache/' ), ['.','..','.gitkeep'] ) );
	}
	
	public function truncate(){
		$files = array_diff( scandir( BASEPATH . '/etc/cache/' ), ['.', '..', '.gitkeep'] );
        foreach($files as $file){
            $file_abs = BASEPATH . '/etc/cache/' . $file;
            if(is_file($file_abs))
                unlink($file_abs);
        }
	}

}