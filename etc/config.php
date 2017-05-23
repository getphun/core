<?php
/**
 * Application level config
 * @package core
 * @version 0.0.1
 * @upgrade false
 */

return [
    'name' => 'Phun',
    'version' => '0.0.1',
    'host' => 'cms.phu',
    'timezone' => 'Asia/Jakarta',
    
    '_gates' => [
        'site' => [
            'path' => '/'
        ]
    ],
    
    '_routes' => [
        'site' => [
            '404' => [
                'handler' => 'Core\\Controller\\Home::notFound'
            ],
            'siteHome' => [
                'rule' => '/',
                'handler' => 'Core\\Controller\\Home::index'
            ]
        ]
    ],
    
    'query_cache' => [
        'page', 'library'
    ]
];