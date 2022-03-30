<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-01
 * Time: 15:11
 * Description:
 */

namespace app\backend\controller\auth;

use app\backend\model\Manage;
use app\backend\service\TriggerService;
use app\common\controller\BackendController;
use app\common\service\AuthCore;

class Login extends BackendController
{
    /**
     * 登录
     * @param Manage $manage
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(Manage $manage)
    {
        $post = $this->request->post();

        $manage->valid($post, 'login');

        $data = $manage
            ->where('username|phone|email', $post['username'])
            ->withoutField('update_time,delete_time')
            ->find();

        if (!$data) {
            abort(-1, '用户不存在或已被禁用');
        }
        if (encrypt($post['password']) != $data['password']) {
            abort(-1, '密码或用户名错误');
        }
        if ($data['status'] == 0) {
            abort(-1, '账号已被禁用, 请联系管理员');
        }
        $data->last_login_ip = getRealIp();
        $data->login_time = time();
        $data->save();
        $data = $data->toArray();
        unset($data['password']);

        $token = app('app\\common\\service\\JWTManager', [
            'param' => [
                'manage_id' => $data['manage_id'],
            ],
            'type'  => 2
        ])->issueToken();
        header('token:' . $token);

        TriggerService::updateMenu($data['manage_id']);

        return apiShow([
            'info' => [
                'username' => $data['username'],
                'avatar'   => $data['avatar'],
                'role_id'  => $data['role_id']
            ],
            'menu' => AuthCore::getInstance()->getBackendMenu($data['manage_id'])
        ], '登录成功');
    }

    /**
     * 获取menu
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenu()
    {
        $manage_id = $this->deToken(0)->manage_id;

        TriggerService::updateMenu($manage_id);

        return apiShow(AuthCore::getInstance()->getBackendMenu($manage_id));
    }

    /**
     * 获取管理员信息
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo()
    {
        $manage_id = $this->deToken(0)->manage_id;

        $data = Manage::where('manage_id', $manage_id)
            ->field('username,avatar,role_id')
            ->find();

        return apiShow($data);
    }
}