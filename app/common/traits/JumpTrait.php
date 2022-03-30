<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-19
 * Time: 11:05
 * Description:
 */

namespace app\common\traits;


use think\exception\HttpResponseException;
use think\Response;

trait JumpTrait
{
    /**
     * 操作成功跳转的快捷方法
     * @param mixed $data 返回的数据
     * @param string $msg 提示信息
     * @param int $code 状态码
     * @param string|null $url 跳转的 URL 地址
     * @param int $wait 跳转等待时间
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    protected function success($data = [], string $msg = 'success', int $code = 0, string $url = null, int $wait = 3)
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url)->__toString();
        }

        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html') {
            $response = view(app('config')->get('app.dispatch_success_tmpl'), $result);
        } else {
            $response = json($result);
        }
        return $response;
    }

    /**
     * 操作错误跳转的快捷方法
     * @param mixed $data 返回的数据
     * @param string $msg 提示信息
     * @param int $code 返回的数据
     * @param string|null $url 跳转的 URL 地址
     * @param int $wait 跳转等待时间
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    protected function error($data = [], string $msg = 'error', int $code = -1, string $url = null, int $wait = 3)
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url)->__toString();
        }

        $type = $this->getResponseType();
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        if ($type == 'html') {
            $response = view(app('config')->get('app.dispatch_error_tmpl'), $result);
        } else {
            $response = json($result);
        }
        return $response;
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param array $data 要返回的数据
     * @param int $code 返回的 code
     * @param string $msg 提示信息
     * @param string $type 返回数据格式
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    protected function result(array $data = [], int $code = 0, string $msg = '', string $type = '')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data,
        ];
        $type = $type ?: $this->getResponseType();

        if ($type == 'html') {
            $response = view(app('config')->get('app.dispatch_success_tmpl'), $result);
        } else {
            $response = json($result);
        }
        return $response;
    }

    /**
     * 获取当前的 response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType(): string
    {
        return (request()->isJson() || request()->isAjax() || request()->isPost()) ? 'json' : 'html';
    }
}