<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-01
 * Time: 15:00
 * Description:
 */

namespace app\common\controller;

use app\BaseController;

class BackendController extends BaseController
{
    /**
     * @var int 分页条数
     */
    protected $pageSize = 10;

    /**
     * 解析token
     * @param bool $type
     * @return object|\think\App
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function deToken(bool $type = true)
    {
        $request = app('request');
        // 需要检测是否登入
        if (!$type && !$request->manage_id) {
            \think\Response::create(['code' => -201, 'msg' => '登入过期, 请重新登入'], 'json')->send();
        }
        if (isset($request->manage_id) && $request->manage_id) {
            // 判断是否被禁用
            $manageInfo = (new \app\common\model\Manage())
                ->where([
                    ['manage_id', '=', $request->manage_id]
                ])
                ->withoutField('password,create_time,update_time,delete_time')
                ->find();
            if (!$manageInfo || $manageInfo['status'] == 0) {
                \think\Response::create(['code' => -201, 'msg' => '该账号已被注销或禁用'], 'json')->send();
            } else {
                $request->manage_id = $manageInfo['manage_id'];
                $request->phone = $manageInfo['phone'];
                $request->role_id = $manageInfo['role_id'];
            }
        }

        return $request;
    }
}