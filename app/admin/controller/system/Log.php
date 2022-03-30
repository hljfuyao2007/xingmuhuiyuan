<?php
/**
 * Created by Automatic scripts.
 * User: Kassy
 * Date: 2021-10-22
 * Time: 17:12:48
 * Description:
 */

namespace app\admin\controller\system;


use app\admin\model\SystemLog;
use app\common\controller\AdminController;

class Log extends AdminController
{
    /**
     * 列表
     * @param SystemLog $log
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(SystemLog $log)
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList(\app\admin\model\Manage::class);
            }

            [$limit, $where] = $this->buildTableParam($log);

            foreach ($where as &$item) {
                if ($item[0] == 'create_time') {
                    $item[0] = 'system_log.create_time';
                }
            }
            $where[] = ['system_log.delete_time', '=', null];

            $data = $log
                ->field('id,manage_id,ip,route,route_zh,content,create_time')
                ->withJoin(['manage' => ['username']])
                ->where($where)
                ->order('id desc')
                ->paginate($limit);

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 删除
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function del()
    {
        SystemLog::destroy($this->request->post('id'));

        return $this->success([], '删除成功', 1);
    }
}