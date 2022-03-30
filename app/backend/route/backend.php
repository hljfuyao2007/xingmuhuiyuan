<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-01
 * Time: 14:06
 * Description:
 */

use think\facade\Route;

/***************************************公共方法************************************************/
Route::group('common', function () {
    // 三级联动
    Route::get('linkage', 'linkage');
    // 权限组select
    Route::get('authGroupSelect', 'authGroupSelect');
})->prefix('backend/Common/');
/***************************************登录************************************************/
Route::group('auth', function () {
    // 登录
    Route::post('login', 'login');
    // 获取menu
    Route::get('getMenu', 'getMenu');
    // 获取管理员信息
    Route::get('getInfo', 'getInfo');
})->prefix('backend/auth.Login/');
/***************************************基础设置************************************************/
Route::group('basics', function () {
    // 列表
    Route::get('index', 'index');
    // 设置
    Route::post('edit', 'edit');
    // 上传管理
    Route::get('fileManageList', 'fileManageList');
    // 上传管理删除
    Route::post('fileManageDel', 'fileManageDel');
})->prefix('backend/config.Basics/');
/***************************************管理员管理************************************************/
Route::group('manager', function () {
    // 列表
    Route::get('index', 'index');
    // 详情
    Route::get('find', 'find');
    // 添加
    Route::post('add', 'add');
    // 编辑
    Route::post('edit', 'edit');
    // 删除
    Route::post('del', 'del');
    // 属性修改
    Route::post('modify', 'modify');
})->prefix('backend/auth.Administrator/');
/***************************************权限管理************************************************/
Route::group('role', function () {
    // 列表
    Route::get('index', 'index');
    // 查询一条
    Route::get('find', 'find');
    // 添加
    Route::post('add', 'add');
    // 编辑
    Route::post('edit', 'edit');
    // 删除
    Route::post('del', 'del');
})->prefix('backend/auth.Role/');