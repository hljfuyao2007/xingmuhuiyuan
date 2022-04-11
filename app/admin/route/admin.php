<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-02
 * Time: 14:11
 * Description:
 */

use think\facade\Route;

/***************************************基础设置************************************************/
Route::group('config', function () {
    // 基础设置
    Route::any('basic', 'basic');
    // 上传管理
    Route::get('fileManageList', 'fileManageList');
    // 删除文件
    Route::post('fileManageDel', 'fileManageDel');
    // 上传管理添加
    Route::any('fileManageAdd', 'fileManageAdd');
})->prefix('admin/system.Config/');
/***************************************管理员管理************************************************/
Route::group('manage', function () {
    // 列表
    Route::get('index', 'index');
    // 添加
    Route::any('add', 'add');
    // 编辑
    Route::any('edit', 'edit');
    // 删除
    Route::post('del', 'del');
    // 属性修改
    Route::post('modify', 'modify');
})->prefix('admin/system.Manage/');
/***************************************权限组************************************************/
Route::group('role', function () {
    // 列表
    Route::get('index', 'index');
    // 添加
    Route::any('add', 'add');
    // 编辑
    Route::any('edit', 'edit');
    // 删除
    Route::post('del', 'del');
    // 权限路由列表
    Route::any('auth_list', 'auth_list');
    // 添加权限路由
    Route::any('auth_add', 'auth_add');
    // 编辑权限路由
    Route::any('auth_edit', 'auth_edit');
    // 删除权限路由
    Route::post('auth_del', 'auth_del');
})->prefix('admin/system.Role/');
/***************************************权限树************************************************/
Route::group('auth_tree', function () {
    // 权限树
    Route::any('tree', 'tree');
    // 保存权限
    Route::post('saveRole', 'saveRole');
})->prefix('admin/system.AuthTree/');
/***************************************会员管理************************************************/
Route::group('member', function () {
    // 列表
    Route::get('index', 'index');
    // 添加
    Route::any('add', 'add');
    // 查看
    Route::any('view', 'view');
    // 属性修改
    Route::post('modify', 'modify');
    // 审核
    Route::any('check', 'check');
})->prefix('admin/member.Member/');
/***************************************操作日志************************************************/
Route::group('log', function () {
    // 列表
    Route::get('index', 'index');
    // 删除
    Route::post('del', 'del');
})->prefix('admin/system.Log/');