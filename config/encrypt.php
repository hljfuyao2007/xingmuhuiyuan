<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-05
 * Time: 22:04
 * Description:
 */

return [
    'debug' => env('debug'),
    'aes'   => [
        /**
         * 加密方式
         * aes-128-cbc
         * aes-128-cfb
         * aes-128-ctr
         * aes-128-ecb
         * aes-128-ofb
         */
        'method' => 'aes-128-cbc',
        'secret' => 'QELAXZy0iLjba7ej',
        'iv'     => 'Fn2loD9bM0bxQUcM'
    ]
];