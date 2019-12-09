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
    'management' => true, # 接口管理平台控制参数，true开启 | false关闭
    'custom' => [
        # 格式：注解类 => 注解操作类
    ]
];
