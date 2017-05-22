<?php
/**
 * Server tester
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Library;

class Server 
{
    static function php(){
        $result = [
            'success' => version_compare(PHP_VERSION, '7.0.0', '>='),
            'info'    => PHP_VERSION
        ];
        
        return $result;
    }
    
    static function software(){
        $server = \Phun::$dispatcher->req->getServer('SERVER_SOFTWARE');
        $result = [
            'success' => preg_match('!nginx|Apache!', $server),
            'info'    => $server
        ];
        
        return $result;
    }
}