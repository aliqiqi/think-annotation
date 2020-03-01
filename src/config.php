<?php

return [
    'inject' => [
        'enable'     => true,
        'namespaces' => [],
    ],
    'route'  => [
        'enable'      => true,
        'controllers' => [],
    ],
    'ignore' => [],
    'cross_domain' => [ # 跨域管理
        'enable'      => true, # 打开以后默认跨域
        'header' => [ # 甚至跨域访问的HEADER，文档：https://www.kancloud.cn/manual/thinkphp6_0/1037507
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With',
            'Access-Control-Allow-Credentials'=>'true',
        ]
    ], 
    'custom' => [
        # 格式：注解类 => 注解操作类
    ]
];
