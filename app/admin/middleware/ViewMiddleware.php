<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-21
 * Time: 11:20
 * Description:
 */

namespace app\admin\middleware;


use app\common\constants\AdminConstant;
use think\facade\View;

class ViewMiddleware
{
    public function handle($request, \Closure $next)
    {
        list($thisModule, $thisController, $thisAction) = [app('http')->getName(), $request->controller(), $request->action()];
        list($thisControllerArr, $jsPath) = [explode('.', $thisController), null];
        foreach ($thisControllerArr as $vo) {
            empty($jsPath) ? $jsPath = parse_name($vo) : $jsPath .= '/' . parse_name($vo);
        }
        $autoloadJs = file_exists(root_path('public/' . env('admin.static_path')) . "{$thisModule}/js/{$jsPath}.js");
        // 当前JS路径
        $thisControllerJsPath = "{$thisModule}/js/{$jsPath}.js";
        $data = [
            'baseUrl'              => $request->domain() . env('admin.static_path') . '/',
            'adminModuleName'      => $thisModule,
            'thisController'       => parse_name($thisController),
            'thisAction'           => $thisAction,
            'thisRequest'          => parse_name("{$thisModule}/{$thisController}/{$thisAction}"),
            'thisControllerJsPath' => $thisControllerJsPath,
            'autoloadJs'           => $autoloadJs,
            'isSuperAdmin'         => session('admin.manage_id') == AdminConstant::SUPER_ADMIN_ID,
            'version'              => env('app_debug') ? time() : sysconfig('site', 'site_version'),
        ];
        View::assign($data);
        return $next($request);
    }
}