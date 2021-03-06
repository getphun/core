<?php

/**
 * Get alternative value if the first value is falsy
 * @param* mixed
 * @return any not falsy value
 */
function alt(){
    $args = func_get_args();
    foreach($args as $arg){
        if($arg)
            return $arg;
    }
}

/**
 * Debug some vars and die.
 * @param* mixed vars The things to debug.
 */
function deb(){
    $args = func_get_args();
    ob_start();
    echo '<pre>';
    foreach($args as $vars){
        if(is_bool($vars) || is_null($vars))
            var_dump($vars);
        else
            echo htmlspecialchars(print_r($vars, true), ENT_QUOTES);
        echo PHP_EOL;
    }
    echo '</pre>';
    
    $ctx = ob_get_contents();
    ob_end_clean();
    
    echo $ctx;
    die;
}

/**
 * Group array by property
 * @param array arr The array
 * @param string prop The property to set as group
 * @return array
 */
function group_by_prop($arr, $prop){
    $result = [];
    foreach($arr as $ar){
        $ar = (object)$ar;
        if(!isset($result[$ar->$prop]))
            $result[$ar->$prop] = [];
        $result[$ar->$prop][] = $ar;
    }
    return $result;
}

/**
 * Group items per columns
 * @param array list List of object to group
 * @param integer column Total column / total item per group
 */
function group_per_column($list, $column=3){
    $result = array();
    
    $group = 0;
    foreach($list as $index => $item){
        if(!array_key_exists($group, $result))
            $result[$group] = array();
        $result[$group][$index] = $item;
        if(count($result[$group]) >= $column)
            $group++;
    }
    
    return $result;
}

/**
 * Short-hand for htmlspecialchars
 * @param string str The string to encode
 * @return string encoded $str
 */
function hs($str){
    return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * Check if current env is development
 * @return boolean true on dev~ false otherwise.
 */
function is_dev(){
    return ENVIRONMENT == 'development';
}

/**
 * Check if arary is indexed 
 * @param array $arr
 * @return boolean true on indexed array
 */
function is_indexed_array($arr){
    return array_keys($arr) === range(0, count($arr)-1);
}

/**
 * Check if module exists
 * @param string name The module name.
 * @return boolean true on exists, false otherwise
 */
function module_exists($name){
    return is_dir( BASEPATH . '/modules/' . $name );
}

/**
 * Check if autoload class exists
 * @param string name The class name.
 * @return boolean true on exists, false otherwise
 */
function autoload_class_exists($name){
    $dis = \Phun::$dispatcher;
    return isset($dis->config->_autoload['classes'][$name]);
}

/**
 * Replace two object
 * @param object $source
 * @param object $new
 * @return replaced props
 */
function object_replace($origin, $new){
    foreach($new as $prop => $value)
        $origin->$prop = $value;
    return $origin;
}

/**
 * Set object/array property as array/object key 
 * @param array $arr The array to proc
 * @param string $prop the property name to set as array key
 */
function prop_as_key($arr, $prop){
    $result = [];
    
    $is_obj = end($arr);
    $is_obj = is_object($is_obj);
    
    foreach($arr as $ar){
        $key = $is_obj ? $ar->$prop : $ar[$prop];
        $result[$key] = $ar;
    }
    
    return $result;
}

/**
 * Convert string to slug
 * @param string $str The string to convert
 * @return string slug version of the $str
 */
function strtoslug($str){
    $str = strtolower(trim($str));
    $str = preg_replace('![^a-z0-9]!', '-', $str);
    $str = preg_replace('!-+!', '-', $str);
    $str = preg_replace('!^-*|-*$!', '', $str);
    return $str;
}