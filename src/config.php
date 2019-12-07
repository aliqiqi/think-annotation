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
        \app\annotation\Param::class => \app\annotation\handler\Param::class, # 单个参数验证器
        \app\annotation\Jwt::class => \app\annotation\handler\Jwt::class, # Jwt请求验证器
        \app\annotation\Doc::class => \app\annotation\handler\Doc::class, # 接口文档生成器
    ]
];
