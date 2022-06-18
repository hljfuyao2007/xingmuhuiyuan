<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-01
 * Time: 13:17
 * Description:
 */

namespace app\common\controller;


use app\BaseController;
use think\facade\Request;

class ApiController extends BaseController
{
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
        if (!$type && !$request->mid) {
            \think\Response::create(['code' => -201, 'msg' => '登入过期, 请重新登入'], 'json')->send();
        }
        if (isset($request->mid) && $request->mid) {
            // 判断是否被禁用
            $memberInfo = (new \app\common\model\Member())
                ->where([
                    ['member_id', '=', $request->mid]
                ])
                ->field('member_id,phone,username,open_id,union_id,status')
                ->find();
            if (!$memberInfo || $memberInfo['status'] == 0) {
                \think\Response::create(['code' => -201, 'msg' => '该账号已被注销或禁用'], 'json')->send();
            } else {
                $request->mid = $memberInfo['member_id'];
                $request->phone = $memberInfo['phone'];
                $request->open_id = $memberInfo['open_id'];
                $request->union_id = $memberInfo['union_id'];
            }
        }

        return $request;
    }
}