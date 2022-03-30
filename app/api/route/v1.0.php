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
    })->prefix('api/access.Login/');
});
