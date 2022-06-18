<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022/4/14
 * Time: 10:15
 * Description:
 */

use think\facade\Route;

Route::any('/', 'index');
Route::any('index', 'index');
Route::any('home', 'home');
Route::any('my', 'my');
Route::any('user_notice', 'user_notice');
Route::any('info', 'info');

Route::any('achievement', 'achievement');
Route::any('team', 'team');
Route::any('newteam', 'newteam');
Route::any('withdrawal', 'withdrawal');
Route::any('personal', 'personal');
Route::any('txrz', 'txrz');
Route::any('dlxz', 'dlxz');
Route::any('tobeagent', 'tobeagent');
Route::any('login', 'login');
Route::any('register', 'register');//注册
Route::any('logout', 'logout');//注册

Route::any('wx_bd', 'wx_bd');//绑定微信

Route::any('forget', 'forget');//忘记密码
Route::any('khxz', 'khxz');//忘记密码
Route::any('tx_log', 'tx_log');//提现记录
Route::any('first_team', 'first_team');
Route::any('team_all', 'team_all');
//Route::any('checkIdcard', 'checkIdcard');


// Route::any('Wxlogin', 'Wxlogin');
// Route::group('Wxlogin', function () {
//     // 后台布局
//     Route::get('index', 'index');

// })->prefix('mobile/Wxlogin/');
// Route::any('user_notice', 'user_notice');
// Route::any('info', 'info');


  