<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-15
 * Time: 9:22
 * Description:
 */

namespace app\backend\controller\auth;

use app\backend\model\Manage;
use app\common\constants\AdminConstant;
use app\common\controller\BackendController;
use think\model\Relation;

class Administrator extends BackendController
{
    /**
     * 管理员列表
     * @param Manage $manage
     * @return array|\think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function index(Manage $manage)
    {
        $get = $this->request->get();

        $where = [];
        if (isset($get['username']) && $get['username']) {
            $where[] = ['username', 'like', "%{$get['username']}%"];
        }
        if (isset($get['phone']) && $get['phone']) {
            $where[] = ['phone', 'like', "%{$get['phone']}%"];
        }
        if (isset($get['email']) && $get['email']) {
            $where[] = ['email', 'like', "%{$get['email']}%"];
        }
        if (isset($get['role_id']) && $get['role_id']) {
            $where[] = ['manage.role_id', '=', $get['role_id']];
        }
        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }

        $data = $manage
            ->where($where)
            ->withJoin(['manageRole' => function (Relation $q) {
                $q->withField(['title']);
            }])
            ->order('manage_id desc')
            ->paginate($get['limit']);

        return apiShow($data);
    }

    /**
     * 详情
     * @param Manage $manage
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function find(Manage $manage)
    {
        $get = $this->request->get();

        $data = $manage
            ->where('manage_id', $get['manage_id'])
            ->field('manage_id,username,avatar,role_id,phone,email,status')
            ->find()
            ->getData();
        $data['avatar_view'] = filePathJoin($data['avatar']);

        return apiShow($data);
    }

    /**
     * 添加
     * @param Manage $manage
     * @return array|\think\response\Json
     */
    public function add(Manage $manage)
    {
        $post = $this->request->post();

        $manage->valid($post, 'add');

        $manage->is_exist($post['username'], '用户名已存在');
        $manage->is_exist($post['phone'], '手机号已存在');
        $manage->is_exist($post['email'], '邮箱已存在');

        $manage::create($post);

        return apiShow([], '添加成功', 1);
    }

    /**
     * 编辑
     * @param Manage $manage
     * @return array|\think\response\Json
     */
    public function edit(Manage $manage)
    {
        $post = $this->request->post();

        $manage->valid($post, 'edit');

        if (isset($post['password']) && isset($post['confirm_password']) &&
            $post['password'] && $post['confirm_password']) {
            if ($post['password'] != $post['confirm_password']) {
                abort(-1, '两次密码不一致');
            }
        } else {
            unset($post['password']);
            unset($post['confirm_password']);
        }

        $manage::update($post);

        return apiShow([], '更新成功', 1);
    }

    /**
     * 删除
     * @param Manage $manage
     * @return array|\think\response\Json
     */
    public function del(Manage $manage)
    {
        $manage_id = $this->request->post('manage_id');

        if ($manage_id == AdminConstant::SUPER_ADMIN_ID) {
            abort(-1, '不能删除系统管理员');
        }

        $manage::destroy($manage_id);

        return apiShow([], '删除成功', 1);
    }

    /**
     * 属性修改
     * @param Manage $manage
     * @return array|\think\response\Json
     */
    public function modify(Manage $manage)
    {
        $post = $this->request->post();

        $manage::update($post);

        return apiShow([], 'success', 1);
    }
}