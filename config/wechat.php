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
        'app_id'        => 'wx1ff8eea060120d9b',
        'secret'        => '4582f9e15631b8e5708897e51fb6449e',
        'response_type' => 'array',
    ],
    'APP'     => [
        'app_id'        => '',
        'secret'        => '',
        'response_type' => 'array',
    ],
    'MWEB'      => [
        'app_id'        => 'wx1ff8eea060120d9b',
        'secret'        => '4582f9e15631b8e5708897e51fb6449e',
        'response_type' => 'array',
    ],
    'payment' => [
        'mch_id'    => '1601655030',
        'key'       => 'QGBcyOaGHEoxKywSyJJlQBbjT7imr5ir',
        'cert_path' => root_path() . 'cert/wechat/cert.pem',
        'key_path'  => root_path() . 'cert/wechat/key.pem',
    ]
];