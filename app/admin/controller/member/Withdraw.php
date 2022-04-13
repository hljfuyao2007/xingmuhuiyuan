<?php
/**
 * Created by Automatic scripts.
 * User: Kassy
 * Date: 2022-04-12
 * Time: 09:39:47
 * Description:
 */

namespace app\admin\controller\member;


use app\admin\model\Member as MemberModel;
use app\admin\model\MemberWithdraw;
use app\common\controller\AdminController;

class Withdraw extends AdminController
{
    /**
     * 列表
     * @param MemberWithdraw $memberWithdraw
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(MemberWithdraw $memberWithdraw)
    {
        if ($this->request->isAjax()) {
            [$limit, $where] = $this->buildTableParam($memberWithdraw);

            $data = $memberWithdraw
                ->where($where)
                ->withoutField('update_time,delete_time')
                ->order('withdraw_id', 'desc')
                ->paginate($limit)
                ->each(function ($val) {
                    $val->rate = $val->rate . '%';
                });

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 审核
     * @param MemberWithdraw $memberWithdraw
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(MemberWithdraw $memberWithdraw)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $this->db->startTrans();

            if ($post['status'] == 2) {
                MemberModel::where('member_id', $post['member_id'])->inc('balance', $post['money'])->update();
            }

            $memberWithdraw::update($post);

            $this->db->commit();

            return $this->success([], '操作成功', 1);
        }

        $data = $memberWithdraw
            ->where('withdraw_id', $this->request->get('withdraw_id'))
            ->withoutField('update_time,delete_time')
            ->append(['nickname'])
            ->find();

        return $this->fetch('', [
            'item' => $data
        ]);
    }
}