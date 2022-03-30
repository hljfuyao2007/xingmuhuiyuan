<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-11
 * Time: 10:46
 * Description:
 */

namespace app\backend\controller;

use app\admin\model\ManageRole;
use app\common\controller\BackendController;
use app\common\model\Districts;

class Common extends BackendController
{
    /**
     * 三级联动
     * @param Districts $districts
     * @return array|Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function linkage(Districts $districts)
    {
        $param = $this->request->get();

        $data = [];
        if ($param['pid'] == 0 && $param['pid'] != '') {
            $data = $districts->getProvince();
        } elseif ($param['pid'] > 0) {
            $data = $districts->getCityOrArea($param['pid']);
        }
        return apiShow($data);
    }

    /**
     * 权限组select
     * @param ManageRole $manageRole
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authGroupSelect(ManageRole $manageRole)
    {
        return apiShow($manageRole::field('role_id,title')->select());
    }
}