<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
// 
// 
// 
// 

// echo "<h1>本服务已停止使用</h1>";

// echo "<h1>新的服务地址为 </h1>";
// echo "<h2> http://ry.xmwh.shop/mobile</h2>";
// exit();

namespace think;


//echo 0;exit;
require __DIR__ . '/../vendor/autoload.php';


// 允许跨域
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN'] ?? '*');
header('Access-Control-Allow-Methods:POST,GET,OPTIONS');
header('Access-Control-Allow-Headers:x-requested-with,content-type,token');
header('Access-Control-Expose-Headers:token');


// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
