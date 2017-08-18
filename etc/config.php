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
    'install' => '2017-05-31 01:05:00',
    
    '_gates' => [],
    '_routes' => [],
    
    'query_cache' => [
        'page', 'library'
    ]
];