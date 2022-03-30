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
        'app_id'        => 'wxb4d7c93dd76954d4',
        'secret'        => '3f314689501083bf9c100caa8d4f0883',
        'response_type' => 'array',
    ],
    'APP'     => [
        'app_id'        => '',
        'secret'        => '',
        'response_type' => 'array',
    ],
    'payment' => [
        'mch_id'    => '',
        'key'       => '',
        'cert_path' => root_path() . 'cert/wechat/cert.pem',
        'key_path'  => root_path() . 'cert/wechat/key.pem',
    ]
];