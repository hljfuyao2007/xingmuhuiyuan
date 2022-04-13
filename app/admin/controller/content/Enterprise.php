<?php
/**
 * Created by Automatic scripts.
 * User: Kassy
 * Date: 2022-04-13
 * Time: 14:21:00
 * Description:
 */

namespace app\admin\controller\content;


use app\admin\model\MemberData;
use app\common\controller\AdminController;
use app\common\service\Excel;

class Enterprise extends AdminController
{
    /**
     * 列表
     * @param MemberData $memberData
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(MemberData $memberData)
    {
        if ($this->request->isAjax()) {
            [$limit, $where] = $this->buildTableParam($memberData);

            $data = $memberData
                ->where($where)
                ->withoutField('update_time,delete_time')
                ->append(['nickname'])
                ->order('data_id desc')
                ->paginate($limit);

            return $this->success($data);
        }

        return $this->fetch();
    }

    public function inc()
    {
        if ($this->request->isPost()) {
            $data = (new Excel())->import();

            // TODO::分销逻辑

            if ($data) {
                foreach ($data as $item) {
                    if (!$item[0] || !$item[2] || !$item[4]) {
                        continue;
                    }

                    MemberData::create([
                        'member_id'  => 1,
                        'uid'        => $item[2],
                        'date'       => datetime($item[0], 'Y-m-d'),
                        'enterprise' => $item[4],
                    ]);
                }
            }
            return $this->success([], '导入成功', 1);
        }

        return $this->fetch();
    }
}