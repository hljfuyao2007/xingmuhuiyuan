<?php

namespace app\admin\controller;

use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use app\common\service\AuthCore;
use think\response\Json;


class Index extends AdminController
{
    /**
     * 后台布局
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取个人菜单
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function init(): Json
    {
        return json(AuthCore::getInstance()->getMenu());
    }

    /**
     * @return mixed
     */
    public function welcome()
    {
        //        halt(getRouteList());
        return $this->fetch();
    }

    /**
     * 清除缓存
     * @return array|false|mixed|Json|\think\response\View
     */
    public function clear()
    {
        TriggerService::updateMenu($this->admin_id);
        TriggerService::updateAuth($this->admin_id);
        return $this->success([], '清除成功', 1);
    }
}
