<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-04-14
 * Time: 15:30
 * Description:
 */

return [
    'view_path'          => app()->getAppPath() . 'view/',
    // 视图输出字符串内容转换
    'tpl_replace_string' => [
        // 模块资源地址前缀
        '__STATIC__' => '/static',
        '__JS__'     => '/static/mobile/js',
        '__CSS__'    => '/static/mobile/css',
        '__PLUG__'   => '/static/mobile/plugs',
        '__IMG__'    => '/static/mobile/images',
        '__PUB__'    => '/static/common'
    ],
];