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
    // 平台
    Route::any('open', 'open');
    // 业绩
    Route::any('data', 'data');
    // 团队
    Route::any('team', 'team');
    Route::any('money', 'money');
     // 团队
    Route::any('statistics', 'statistics');
    Route::any('mon', 'mon');
    Route::any('platform_createtime', 'platform_createtime');
    

})->prefix('admin/member.Member/');
/***************************************操作日志************************************************/
Route::group('log', function () {
    // 列表
    Route::get('index', 'index');
    // 删除
    Route::post('del', 'del');
})->prefix('admin/system.Log/');
/***************************************会员提现************************************************/
Route::group('withdraw', function () {
    // 列表
    Route::get('index', 'index');
    // 审核
    Route::any('edit', 'edit');
    Route::any('export', 'export');
    Route::any('edit_all', 'edit_all');
})->prefix('admin/member.Withdraw/');
/***************************************算法************************************************/
Route::group('dian', function () {
    // 列表
    Route::any('index', 'index');
    // 新增
    Route::any('add', 'add');
    // 新增
    Route::any('del', 'del');
    // shanchu
    Route::any('delete', 'delete');
    // 审核
    Route::any('edit', 'edit');
    
})->prefix('admin/member.Dian/');
/***************************************平台管理************************************************/
Route::group('platform', function () {
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
})->prefix('admin/content.Platform/');
/***************************************业绩管理************************************************/
Route::group('enterprise', function () {
    // 列表
    Route::get('index', 'index');
    // 列表
    Route::get('see', 'see');
    // 导入
    Route::any('inc', 'inc');
    // 回退
    Route::any('dec', 'dec');
    // 结算
    Route::any('set', 'set');
    // 确认结算
    Route::any('true_set', 'true_set');
    // 账单
    Route::any('commission', 'commission');

    // excel
    Route::any('excel_list', 'excel_list');
    
    Route::any('excel_del', 'excel_del');


    Route::any('exportExcel', 'exportExcel');
    Route::any('true_set_list', 'true_set_list');

})->prefix('admin/content.Enterprise/');