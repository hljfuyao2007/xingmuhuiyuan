<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-16
 * Time: 15:31
 * Description:
 */

namespace app\backend\controller\auth;

use app\backend\model\Manage;
use app\backend\model\ManageRole;
use app\common\controller\BackendController;

class Role extends BackendController
{
    /**
     * 列表
     * @param ManageRole $manageRole
     * @return array|\think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function index(ManageRole $manageRole)
    {
        $get = $this->request->get();

        $where = [];
        if (isset($get['title']) && $get['title']) {
            $where[] = ['title', 'like', "%{$get['title']}%"];
        }
        if (isset($get['describe']) && $get['describe']) {
            $where[] = ['describe', 'like', "%{$get['describe']}%"];
        }

        $data = $manageRole
            ->where($where)
            ->field('role_id,title,describe,create_time,update_time')
            ->order('role_id', 'desc')
            ->paginate($get['limit']);

        return apiShow($data);
    }

    /**
     * 查询一条
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function find()
    {
        $get = $this->request->get();

        $data = ManageRole
            ::where('role_id', $get['role_id'])
            ->withoutField('update_time,delete_time')
            ->find();

        return apiShow($data);
    }

    /**
     * 添加
     * @param ManageRole $manageRole
     * @return array|\think\response\Json
     */
    public function add(ManageRole $manageRole)
    {
        $post = $this->request->post();

        $manageRole->valid($post, 'add');

        $manageRole::create($post);

        return apiShow([], '添加成功', 1);
    }

    /**
     * 编辑
     * @param ManageRole $manageRole
     * @return array|\think\response\Json
     */
    public function edit(ManageRole $manageRole)
    {
        $post = $this->request->post();

        $manageRole->valid($post, 'edit');

        $manageRole::update($post);

        return apiShow([], '编辑成功', 1);
    }

    /**
     * 删除
     * @param ManageRole $manageRole
     * @param Manage $manage
     * @return array|\think\response\Json
     */
    public function del(ManageRole $manageRole, Manage $manage)
    {
        $post = $this->request->post();

        $manageRole->valid($post, 'del');

        if ($manage->where('role_id', $post['role_id'])->value('manage_id', '')) {
            abort(-1, '该权限已被使用, 不能删除');
        }

        $manageRole::destroy($post['role_id']);

        return apiShow([], '删除成功', 1);
    }
}