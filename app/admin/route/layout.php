<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-23
 * Time: 16:29
 * Description:
 */

use think\facade\Route;

Route::get('/', 'login/login');
/***************************************后台布局************************************************/
Route::group('index', function () {
    // 后台布局
    Route::get('index', 'index');
    // 接口初始化
    Route::get('init', 'init');
    // 欢迎页
    Route::get('welcome', 'welcome');
    // 清除缓存
    Route::get('clear', 'clear');
})->prefix('admin/Index/');
/***************************************登入************************************************/
Route::group('login', function () {
    // 登入页
    Route::any('login', 'login');
    // 验证码
    Route::get('captcha', 'captcha');
    // 登出
    Route::post('out', 'out');
})->prefix('admin/Login/');
/***************************************公共方法************************************************/
Route::group('common', function () {
    // 文件管理器列表
    Route::get('get_file_manage', 'get_file_manage');
    // 地区三级联动
    Route::get('get_area', 'get_area');
    // 获取搜索select数据
    Route::post('getSelect', 'getSelect');
})->prefix('admin/Common/');