<?php
/**
 * Core module config
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'core',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/phun/core',
    
    '__files' => [
        'modules/core'  => ['install', 'remove', 'update'],
        'index.php'     => ['install', 'remove', 'update'],
        'etc'           => ['install', 'remove'],
        '.htaccess'     => ['install', 'remove'],
    ],
    
    '_services' => [],
    '_autoload' => [
        'classes' => [
            'Core\\Library\\Router'     => 'modules/core/library/Router.php',
            
            'Core\\Controller\\HomeController'    => 'modules/core/controller/HomeController.php'
        ],
        'files'   => [
            'core-helper-devel' => 'modules/core/helper/devel.php'
        ]
    ]
    
];