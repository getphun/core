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
 * Check if current env is development
 * @return boolean true on dev~ false otherwise.
 */
function is_dev(){
    return ENVIRONMENT == 'development';
}

/**
 * Short-hand for htmlspecialchars
 * @param string str The string to encode
 * @return string encoded $str
 */
function hs($str){
    return htmlspecialchars($str, ENT_QUOTES);
}