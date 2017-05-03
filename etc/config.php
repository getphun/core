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
    
    '_gates' => [
        /*
        'micro' => [        
            'host' => 'micro.HOST',
            'path' => '/'
        ],
        'forum' => [
            'host' => ':forum.HOST',
            'path' => '/forum'
        ],
        'canal' => [
            'host' => ':canal.HOST',
            'path' => '/'
        ],
        'admin' => [
            'path' => '/admin'
        ],
        */
        'site' => [
            'path' => '/'
        ]
    ],
    
    '_routes' => [
        /*
        'admin' => [
            '404' => [
                'handler' => 'AdminHome::notFound'
            ],
            'adminHome' => [
                'rule' => '/',
                'handler' => 'AdminHome::index'
            ],
            'adminUser' => [
                'rule' => '/user',
                'handler' => 'AdminUser::index'
            ],
            'adminUserSetting' => [
                'rule' => '/user/:id/:setting',
                'handler' => 'AdminUserSetting::index'
            ]
        ],
        */
        'site' => [
            '404' => [
                'handler' => 'Core\\Controller\\Home::notFound'
            ],
            'siteHome' => [
                'rule' => '/',
                'handler' => 'Core\\Controller\\Home::index'
            ],
            /*
            'sitePageSingle' => [
                'rule' => '/page/:slug',
                'handler' => 'Page::single'
            ]
            */
        ],
        /*
        'micro' => [
            '404' => [
                'handler' => 'MicroHome::notFound'
            ],
            'microHome' => [
                'rule' => '/',
                'handler' => 'MicroHome::index'
            ],
            'microCategories' => [
                'rule' => '/post/category',
                'handler' => 'MicroPostCategory::index'
            ],
            'microCategory' => [
                'rule' => '/post/category/:slug',
                'handler' => 'MicroPostCategory::single'
            ]
        ],
        'canal' => [
            '404' => [
                'handler' => 'CanalHome::notFound'
            ],
            'canalHome' => [
                'rule' => '/',
                'handler' => 'CanalHome::index'
            ],
            'canalPost' => [
                'rule' => '/post/read/:slug',
                'handler' => 'CanalPost::single'
            ]
        ]
        */
    ]
];