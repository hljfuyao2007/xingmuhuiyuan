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
        // 测试
        Route::get('ceshi', 'ceshi');
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
        Route::any('return_oauth2', 'return_oauth2');
        Route::any('wx_sms_login', 'wx_sms_login');


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
        // 用户须知
        Route::get('user_notice', 'user_notice');
        // 根据身份证获取年龄
        Route::get('getAge', 'getAge');
        // 个人信息
        Route::get('info', 'info');
        // 认证
        Route::post('authentication', 'authentication');
        // 成为代理
        Route::get('become_agent', 'become_agent');
        // 成为代理下单
        Route::post('agent_pay', 'agent_pay');


        Route::any('checkIdcard', 'checkIdcard');
    })->prefix('api/my.My/');
    /***************************************首页************************************************/
    Route::group('index', function () {
        // 提现详情
        Route::get('withdrawInfo', 'withdrawInfo');
        // 提现申请
        Route::post('withdraw', 'withdraw');
        // 我的下级
        Route::get('subordinate', 'subordinate');
        // 本月新增
        Route::get('monthly_new', 'monthly_new');

        //计划任务文件
        Route::any('plan_task', 'plan_task');

    })->prefix('api/home.Index/');
    /*************************************支付回调**********************************************/
    Route::group('pay', function () {
        // 微信回调
        Route::any('wxNotify', 'wxNotify');
        Route::any('add', 'add');

    })->prefix('api/pay.Notify/');
});
