<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-19
 * Time: 15:30
 * Description:
 */

return [
    'view_path'          => app()->getAppPath() . 'view/',
    // 视图输出字符串内容转换
    'tpl_replace_string' => [
        // 模块资源地址前缀
        '__STATIC__' => '/static',
        '__JS__'     => '/static/admin/js',
        '__CSS__'    => '/static/admin/css',
        '__PLUG__'    => '/static/admin/plugs',
        '__IMG__'    => '/static/admin/images',
        '__PUB__'    => '/static/common'
    ],
];