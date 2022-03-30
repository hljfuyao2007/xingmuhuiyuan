<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-24
 * Time: 14:31
 * Description:
 */

namespace app\admin\controller;


use app\admin\model\Manage;
use app\common\controller\AdminController;
use think\captcha\facade\Captcha;

class Login extends AdminController
{
    /**
     * 初始化方法
     */
    public function initialize()
    {
        parent::initialize();
        if (session('admin') && $this->request->action() != 'out') {
            header('Location: /admin/index/index');
        }
    }

    /**
     * 登入
     * @param Manage $manage
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(Manage $manage)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $manage->valid($param, env('admin.captcha') ? 'login_verify' : 'login');

            $admin = $manage->where('username|phone|email', $param['username'])
                ->withoutField('update_time,delete_time')
                ->find();

            if (!$admin) {
                return $this->error([], '用户不存在或已被禁用');
            }
            if (encrypt($param['password']) != $admin['password']) {
                return $this->error([], '密码或用户名错误');
            }
            if ($admin['status'] == 0) {
                return $this->error([], '账号已被禁用, 请联系管理员');
            }
            $admin->login_num += 1;
            $admin->last_login_ip = getRealIp();
            $admin->login_time = time();
            $admin->save();
            $admin = $admin->toArray();
            unset($admin['password']);
            $admin['expire_time'] = $param['keep_login'] == 1 ? true : time() + env('admin.no_keep_expire_time');
            session('admin', $admin);

            return $this->success([], '登入成功', 1);
        }
        return $this->fetch('', [
            'is_captcha' => env('admin.captcha')
        ]);
    }

    /**
     * 验证码
     * @return \think\Response
     */
    public function captcha(): \think\Response
    {
        return Captcha::create();
    }

    /**
     * 退出登入
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function out()
    {
        session('admin', null);
        return $this->success([], '退出成功', 1);
    }
}