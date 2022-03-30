<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-09
 * Time: 16:41
 * Description:
 */

namespace app\admin\controller\system;


use app\admin\model\Manage;
use app\admin\model\ManageRole;
use app\admin\model\Menu;
use app\common\controller\AdminController;

class Role extends AdminController
{
    /**
     * 列表
     * @param ManageRole $role
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(ManageRole $role)
    {
        if ($this->request->isAjax()) {
            [$limit, $where] = $this->buildTableParam($role);

            $data = $role
                ->where($where)
                ->field('role_id,title,describe,create_time,update_time')
                ->order('role_id', 'desc')
                ->paginate($limit)
                ->toArray();

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 添加
     * @param ManageRole $role
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function add(ManageRole $role)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $role->valid($param, 'add');

            $role::create($param);

            return $this->success([], '添加成功', 1);
        }

        return $this->fetch();
    }

    /**
     * 编辑
     * @param ManageRole $role
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(ManageRole $role)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $role->valid($param, 'edit');

            $role::update($param);

            return $this->success([], '操作成功', 1);
        }

        $data = $role
            ->where('role_id', $this->request->get('role_id'))
            ->field('role_id,title,describe')
            ->find();

        return $this->fetch('add', [
            'item' => $data
        ]);
    }

    /**
     * 删除
     * @param ManageRole $role
     * @param Manage $manage
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function del(ManageRole $role, Manage $manage)
    {
        $id = $this->request->post('role_id');

        if ($manage->where('role_id', $id)->value('manage_id', '')) {
            return $this->error([], '该权限已被使用, 不能删除');
        }

        $role::destroy($id);

        return $this->success([], '删除成功', 1);
    }

    /**
     * 权限路由列表
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function auth_list()
    {
        if ($this->request->isAjax()) {
            $data = Menu::where('status', 1)
                ->withoutField('create_time,update_time,delete_time')
                ->select()
                ->toArray();

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 添加权限路由
     * @param Menu $menu
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function auth_add(Menu $menu)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $menu->valid($param);

            $menu::create($param);

            return $this->success([], '添加成功', 1);
        }

        $parent_select = find_level($menu
            ->where([
                ['status', '=', 1],
                ['deep', '<', 4]
            ])
            ->field('id,pid,title,deep')
            ->select()
            ->each(function ($item) {
                if ($item->deep == 2) {
                    $item->title = '>>' . $item->title;
                } elseif ($item->deep == 3) {
                    $item->title = '>>>>' . $item->title;
                }
            }), 'id');

        return $this->fetch('', [
            'parent_select' => $parent_select
        ]);
    }

    /**
     * 编辑权限路由
     * @param Menu $menu
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function auth_edit(Menu $menu)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $menu->valid($param, 'edit');

            $menu::update($param);

            return $this->success([], '操作成功', 1);
        }

        $parent_select = find_level($menu
            ->where([
                ['status', '=', 1],
                ['deep', '<', 4]
            ])
            ->field('id,pid,title,deep')
            ->select()
            ->each(function ($item) {
                if ($item->deep == 2) {
                    $item->title = '>>' . $item->title;
                } elseif ($item->deep == 3) {
                    $item->title = '>>>>' . $item->title;
                }
            }), 'id');

        $data = $menu
            ->where('id', $this->request->get('id'))
            ->withoutField('create_time,update_time,delete_time')
            ->find()
            ->toArray();

        return $this->fetch('auth_add', [
            'parent_select' => $parent_select,
            'item'          => $data
        ]);
    }

    /**
     * 删除权限路由
     * @param Menu $menu
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function auth_del(Menu $menu)
    {
        $id = $this->request->post('id');

        if ($menu->where('pid', $id)->value('id', '')) {
            return $this->error([], '不能删除, 请先删除下级');
        }

        $menu::destroy($id);

        return $this->success([], '删除成功', 1);
    }
}