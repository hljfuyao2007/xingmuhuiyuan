<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-10-22
 * Time: 16:49
 * Description:
 */

namespace app\admin\middleware;

use app\admin\model\Menu;
use app\admin\model\SystemLog;

class LogMiddleware
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        $currentBaseUrl = $request->baseUrl();

        if ($request->isPost() && ($request->admin_id || session('admin.manage_id'))
            && stripos($currentBaseUrl, '/admin/common') !== 0) {
            $menuModel = new Menu();
            $child_route_zh = $menuModel
                ->where('href', $currentBaseUrl)
                ->field('id,pid,title')
                ->find() ?? [];
            $parent_route_zh = $menuModel
                ->where('id', $child_route_zh['pid'] ?? 0)
                ->value('title', '');
            $saveData = [
                'manage_id' => $request->admin_id ?: session('admin.manage_id'),
                'ip'        => getRealIp(),
                'route'     => $currentBaseUrl,
                'route_zh'  => $parent_route_zh . '/' . ($child_route_zh['title'] ?? ''),
                'content'   => json_encode($request->post())
            ];

            if ($currentBaseUrl == '/admin/login/login') {
                $saveData['route'] = $currentBaseUrl;
                $saveData['route_zh'] = '登录成功';
                if (is_null($saveData['manage_id'])) {
                    $saveData['manage_id'] = null;
                    $saveData['route_zh'] = '登录异常';
                }
            }

            SystemLog::create($saveData);
        }

        return $response;
    }
}