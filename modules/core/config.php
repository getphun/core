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
    '__git' => 'https://github.com/getphun/core',
    
    '__files' => [
        'modules/core'                  => ['install', 'remove', 'update'],
        'index.php'                     => ['install', 'remove', 'update'],
        'etc'                           => ['install', 'remove'],
        '.htaccess'                     => ['install', 'remove'],
        'theme/site/index.phtml'        => ['install', 'remove'],
        'theme/site/404.phtml'          => ['install', 'remove'],
        'theme/site/static/.gitkeep'    => ['install', 'remove']
    ],
    
    '_server' => [
        'Nginx or Apache'   => 'Core\\Library\\Server::software',
        'PHP >= 7.0.0'      => 'Core\\Library\\Server::php'
    ],
    
    '_services' => [
        'cache'         => 'Core\\Service\\Cache',
        'config'        => 'Core\\Service\\Config',
        'param'         => 'Core\\Service\\Param',
        'req'           => 'Core\\Service\\Request',
        'res'           => 'Core\\Service\\Response',
        'router'        => 'Core\\Service\\Router'
    ],
    
    '_autoload' => [
        'classes'       => [
            'Controller'                        => 'modules/core/library/Controller.php',
            'Core\\Controller\\HomeController'  => 'modules/core/controller/HomeController.php',
            'Core\\Library\\Router'             => 'modules/core/library/Router.php',
            'Core\\Library\\View'               => 'modules/core/library/View.php',
            'Core\\Library\\Server'             => 'modules/core/library/Server.php',
            'Core\\Service\\Cache'              => 'modules/core/service/Cache.php',
            'Core\\Service\\Config'             => 'modules/core/service/Config.php',
            'Core\\Service\\Param'              => 'modules/core/service/Param.php',
            'Core\\Service\\Request'            => 'modules/core/service/Request.php',
            'Core\\Service\\Response'           => 'modules/core/service/Response.php',
            'Core\\Service\\Router'             => 'modules/core/service/Router.php'
        ],
        'files'         => [
            'core/devel' => 'modules/core/helper/devel.php',
            'core/page'  => 'modules/core/helper/page.php'
        ]
    ]
    
];