<?php


return [
    'secret'                 => env('jwt.secret'),
    // Asymmetric key
    'public_key'             => env('JWT_PUBLIC_KEY', ''),
    'private_key'            => env('JWT_PRIVATE_KEY', ''),
    'password'               => env('JWT_PASSWORD', ''),
    // JWT time to live
    'ttl'                    => env('jwt.ttl', 60),
    // Refresh time to live
    'refresh_ttl'            => env('jwt.refresh_ttl', 20160),
    // JWT hashing algorithm
    'algo'                   => env('jwt.algo', 'HS256'),
    // token获取方式，数组靠前值优先
    'token_mode'             => ['header', 'param'],
    // 黑名单后有效期
    'blacklist_grace_period' => env('jwt.blacklist_grace_period', 10),
    'blacklist_storage'      => thans\jwt\provider\storage\Tp6::class,
];
