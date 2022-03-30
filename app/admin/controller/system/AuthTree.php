<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-12
 * Time: 21:52
 * Description:
 */

namespace app\admin\controller\system;


use app\admin\model\ManageRole;
use app\admin\model\Menu;
use app\admin\service\TriggerService;
use app\common\constants\AdminConstant;
use app\common\controller\AdminController;

class AuthTree extends AdminController
{
    public function initialize()
    {
        $this->layout = false;
        parent::initialize();
    }

    /**
     * 权限树数据
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function tree()
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            if ($param['role_id'] == 1) {
                return $this->error([], '不能修改该权限');
            }

            $checked = explode(',', ManageRole::where('role_id', $param['role_id'])->value('rules', ''));

            $data = Menu
                ::where([
                    ['status', '=', 1],
                    ['pid', '<>', AdminConstant::MENU_HOME_ID]
                ])
                ->field('id,pid,title')
                ->select()
                ->toArray();
            foreach ($data as $key => &$item) {
                if (in_array($item['id'], $checked)) {
                    $item['checked'] = true;
                }
                if ($item['pid'] == 0) {
                    $item['open'] = true;
                }
            }

            return $this->success(find_level($data, 'id'));
        }

        return $this->fetch();
    }

    /**
     * 保存权限
     * @param ManageRole $manageRole
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function saveRole(ManageRole $manageRole)
    {
        $param = $this->request->post();

        $manageRole::update($param);

        // 查询manage_id
        $manage_ids = \app\admin\model\Manage::where('role_id', $param['role_id'])->column('manage_id');
        // 更新权限和菜单
        foreach ($manage_ids as $manage_id) {
            TriggerService::updateMenu($manage_id);
            TriggerService::updateAuth($manage_id);
        }

        return $this->success([], '保存成功', 1);
    }
}