<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-13
 * Time: 11:14
 * Description:
 */

return [
    'cent'    => false,  // 一分钱支付
    'JSAPI'   => [
        'app_id'        => 'wxb189139c8708aa0d',
        'secret'        => '3829b11426ba4915ed894214d76cbf63',
        'response_type' => 'array',
    ],
    'APP'     => [
        'app_id'        => '',
        'secret'        => '',
        'response_type' => 'array',
    ],
    'MWEB'      => [
        'app_id'        => 'wxb189139c8708aa0d',
        'secret'        => '',
        'response_type' => 'array',
    ],
    'payment' => [
        'mch_id'    => '1612885534',
        'key'       => 'Ka124AFc030A4888Ka124AFc030A4888',
        'cert_path' => root_path() . 'cert/wechat/cert.pem',
        'key_path'  => root_path() . 'cert/wechat/key.pem',
    ]
];