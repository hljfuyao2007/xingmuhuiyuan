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
        \app\admin\middleware\ViewMiddleware::class,
        \app\admin\middleware\LogMiddleware::class
    ]
];