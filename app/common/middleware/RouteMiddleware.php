<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-19
 * Time: 13:43
 * Description:
 */

namespace app\common\middleware;

use app\common\service\AuthCore;
use app\common\traits\JumpTrait;
use Closure;
use think\exception\HttpResponseException;
use think\facade\Cache;
use think\Response;

class RouteMiddleware
{
    use JumpTrait;

    public function handle($request, Closure $next)
    {
        switch ($baseUrl = $request->baseUrl()) {
            case stripos($baseUrl, '/api/') === 0:
                $newToken = self::api($request, $baseUrl);
                $newToken && header('token:' . $newToken);
                break;
            case stripos($baseUrl, '/admin') === 0 || stripos($baseUrl, '/admin/') === 0:
                self::admin($request, $baseUrl);
                break;
            case stripos($baseUrl, '/backend/') === 0:
                $newToken = self::backend($request, $baseUrl);
                $newToken && header('token:' . $newToken);
                break;
            case stripos($baseUrl, '/mobile/') === 0:
                break;
            default:
                $html404 = file_get_contents(app_path() . 'common/tpl/404.html');
                Response::create($html404)->send();
        }

        return $next($request);
    }

    /**
     * 后台区
     * @param $request
     * @param $url
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function admin($request, $url)
    {
        $request->adminRootUrl = '/admin';
        // 检测登入
        $allowedUri = [$request->adminRootUrl, '/admin/login/login', '/admin/login/out', '/admin/login/captcha'];
        if (!in_array($url, $allowedUri)) {
            $request->admin_id = session('admin.manage_id') ?? '';
            if (!$request->admin_id) {
                throw new HttpResponseException($this->error([], '登入过期, 请重新登入', -10, $request->adminRootUrl));
            }

            // 判断未保持登录是否过期
            $expireTime = session('admin.expire_time');
            if ($expireTime !== true && time() > $expireTime) {
                session('admin', null);
                throw new HttpResponseException($this->error([], '登入过期, 请重新登入', -10, $request->adminRootUrl));
            }

            // 不验证的权限路由
            $except = ['/admin/index/index', '/admin/index/init', '/admin/index/clear', '/admin/index/welcome',
                '/admin/common/get_file_manage', '/admin/common/get_area'];
            // 验证权限
            if (!in_array($url, $except)) {
                if (!AuthCore::getInstance()->verifyAuth($url)) {
                    throw new HttpResponseException($this->error([], '权限不足', -11, $request->adminRootUrl));
                }
            }
        }
    }

    /**
     * 接口区
     * @param $request
     * @param $url
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function api($request, $url)
    {
        // 要过滤的url
        $except = [];

        $newToken = null;
        if (stripos($url, '/api/v1.0/') === 0 && !in_array($url, $except)) {
            $header = $request->header();
            if (isset($header['token']) && $header['token']) {
                $jwt = app('app\\common\\service\\JWTManager', ['param' => $header['token'], 'type' => 1]);
                $tokenArr = $jwt->parseToken();
                if ($tokenArr['code'] == -1) {
                    // 检测是否在文件token黑名单
                    $list = Cache::store('file')->get('tokenBlackList', []);
                    $str = $tokenArr['data']->jti . '_' . $tokenArr['data']->iat;
                    if (in_array($str, $list)) {
                        if (!$newToken = Cache::store('file')->get('black_token:' . $header['token'])) {
                            $response = Response::create(['code' => -201, 'msg' => '登录过期, 请重新登录'], 'json');
                            $response->send();
                        }
                    } else {
                        // 颁发新token
                        $newToken = app('app\\common\\service\\JWTManager', [
                            'param' => [
                                'mid'      => $tokenArr['data']->mid,
                                'dev_type' => $tokenArr['data']->dev_type,
                            ],
                            'type'  => 1
                        ], true)->issueToken();
                        // 加入文件黑名单
                        $jwt->addBlackList($tokenArr['data']);
                        // 列入余地黑名单,30秒有效期
                        Cache::store('file')->set("black_token:" . $header['token'], $newToken, 30);
                    }
                }
                $request->mid = $tokenArr['data']->mid;
                $request->dev_type = $tokenArr['data']->dev_type;
            }
        }
        return $newToken;
    }

    /**
     * 后台接口区
     * @param $request
     * @param $baseUrl
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function backend($request, $baseUrl)
    {
        $header = $request->header();
        // 要过滤的url
        $except = ['/backend/auth/login'];

        $newToken = null;
        if (isset($header['token']) && $header['token'] && !in_array($baseUrl, $except)) {
            $jwt = app('app\\common\\service\\JWTManager', ['param' => $header['token'], 'type' => 2]);
            $tokenArr = $jwt->parseToken();

            // token过期
            if ($tokenArr['code'] == -1) {
                // 检测是否在文件token黑名单
                $list = Cache::store('file')->get('tokenBlackList', []);
                $str = $tokenArr['data']->jti . '_' . $tokenArr['data']->iat;
                if (in_array($str, $list)) {
                    if (!$newToken = Cache::store('file')->get('black_token:' . $header['token'])) {
                        Response::create(['code' => -200, 'message' => '登录过期,请重新登录'], 'json')->send();
                    } else {
                        // 颁发新token
                        $newToken = app('app\\common\\service\\JWTManager', [
                            'param' => ['manage_id' => $tokenArr['data']->manage_id],
                            'type'  => 2
                        ], true)->issueToken();
                        // 加入文件黑名单
                        $jwt->addBlackList($tokenArr['data']);
                        // 列入余地黑名单,30秒有效期
                        Cache::store('file')->set("black_token:" . $header['token'], $newToken, 30);
                    }
                }
            }
            $request->manage_id = $tokenArr['data']->manage_id;
        }
        return $newToken;
    }

    /**
     * 手机接口区
     * @param $request
     * @param $baseUrl
     * @return void
     */
    public function mobile($request, $baseUrl)
    {
    }
}