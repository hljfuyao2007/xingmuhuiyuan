<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-01
 * Time: 13:25
 * Description:
 */

use think\facade\Route;

/***************************************公共方法************************************************/
Route::group('common', function () {
    // 上传
    Route::post('upload', 'upload');
    // 编辑器上传
    Route::post('uploadEditor', 'uploadEditor');
    // 获取token
    Route::post('getToken', 'getToken');
    // 发送短信
    Route::post('send_sms', 'send_sms');
})->prefix('api/common.Common/');