<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-21
 * Time: 16:38
 * Description:
 */

return [
    'middleware' => [
        // 限流中间件
        \think\middleware\Throttle::class,
    ]
];