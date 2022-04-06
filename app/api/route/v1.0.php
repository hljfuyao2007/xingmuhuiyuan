<?php

/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-01
 * Time: 13:33
 * Description:
 */

use think\facade\Route;

Route::group('v1.0', function () {
    /***************************************登录or注册************************************************/
    Route::group('access', function () {
        // 短信登录
        Route::post('sms_login', 'sms_login');
        // 微信登录
        Route::post('wx_login', 'wx_login');
        // 联合登录
        Route::post('union_login', 'union_login');
        // 账号登录
        Route::post('account_login', 'account_login');
        // 注册
        Route::post('register', 'register');
        // 忘记密码
        Route::post('forgetPwd', 'forgetPwd');
    })->prefix('api/access.Login/');
    /***************************************我的************************************************/
    Route::group('my', function () {
        // 我的
        Route::get('my', 'my');
        // 选择平台
        Route::get('choose_platform', 'choose_platform');
        // 平台介绍
        Route::get('platform_introduce', 'platform_introduce');
        // 代理须知
        Route::get('agency_notice', 'agency_notice');
        // 根据身份证获取年龄
        Route::get('getAge', 'getAge');
        // 个人信息
        Route::get('info', 'info');
        // 认证
        Route::post('authentication', 'authentication');
        // 成为代理
        Route::get('become_agent', 'become_agent');
    })->prefix('api/my.My/');
});
