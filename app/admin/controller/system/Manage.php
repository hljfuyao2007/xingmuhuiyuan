<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-08
 * Time: 14:21
 * Description:
 */

namespace app\admin\controller\system;


use app\admin\model\Manage as ManageModel;
use app\admin\model\ManageRole;
use app\common\constants\AdminConstant;
use app\common\controller\AdminController;
use think\model\Relation;

class Manage extends AdminController
{
    /**
     * 列表
     * @param ManageModel $manage
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(ManageModel $manage)
    {
        if ($this->request->isAjax()) {
            list($limit, $where) = $this->buildTableParam($manage);

            $data = $manage
                ->where($where)
                ->withJoin(['manageRole' => function (Relation $q) {
                    $q->withField(['title']);
                }])
                ->order('manage_id desc')
                ->paginate($limit)
                ->toArray();

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 添加
     * @param ManageModel $manage
     * @param ManageRole $manageRole
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add(ManageModel $manage, ManageRole $manageRole)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $manage->valid($param, 'add');

            $manage->is_exist($param['username'], '用户名已存在');
            $manage->is_exist($param['phone'], '手机号已存在');
            $manage->is_exist($param['email'], '邮箱已存在');

            $manage::create($param);

            return $this->success([], '添加成功', 1);
        }

        $role = $manageRole
            ->field('role_id,title')
            ->select()
            ->toArray();

        return $this->fetch('', [
            'role' => $role
        ]);
    }

    /**
     * 编辑
     * @param ManageModel $manage
     * @param ManageRole $manageRole
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(ManageModel $manage, ManageRole $manageRole)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $manage->valid($param, 'edit');

            if ($param['password'] && $param['confirm_password']) {
                if ($param['password'] != $param['confirm_password']) {
                    return $this->error([], '两次密码不一致', -1);
                }
            } else {
                unset($param['password']);
                unset($param['confirm_password']);
            }

            $manage::update($param);

            return $this->success([], '操作成功', 1);
        }

        $data = $manage
            ->where('manage_id', $this->request->get('manage_id'))
            ->withoutField('password,update_time,delete_time')
            ->find();
        $data['avatar_data'] = $data->getData('avatar');

        $role = $manageRole
            ->field('role_id,title')
            ->select()
            ->toArray();

        return $this->fetch('', [
            'item' => $data,
            'role' => $role
        ]);
    }

    /**
     * 删除
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function del()
    {
        $manage_id = $this->request->post('manage_id');

        if ($manage_id == AdminConstant::SUPER_ADMIN_ID) {
            return $this->error([], '系统管理员, 不能删除', -1);
        }

        ManageModel::destroy($manage_id);

        return $this->success([], '删除成功', 1);
    }

    /**
     * 属性修改
     * @param ManageModel $manage
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function modify(ManageModel $manage)
    {
        $param = $this->request->post();

        $find = $manage->find($param['id']);
        $find->status = $param['value'];
        $find->save();

        return $this->success([], '保存成功');
    }
}