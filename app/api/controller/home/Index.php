<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-03-31
 * Time: 13:22
 * Description:
 */

namespace app\api\controller\home;

use app\api\model\Member;
use app\api\model\MemberTree;
use app\api\model\MemberWithdraw;
use app\common\controller\ApiController;

class Index extends ApiController
{
    /**
     * 提现信息
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function withdrawInfo()
    {
        $mid = $this->deToken(0)->mid;

        $data = Member
            ::where('member_id', $mid)
            ->field('balance,name,alipay_account')
            ->find();
        $data['rate'] = sysconfig('site', 'withdraw_rate');

        return apiShow($data);
    }

    /**
     * 提现申请
     * @param MemberWithdraw $memberWithdraw
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function withdraw(MemberWithdraw $memberWithdraw, Member $member)
    {
        $post = $this->request->post();
        $post['member_id'] = $this->deToken(0)->mid;
        $post['rate'] = sysconfig('site', 'withdraw_rate');
        $post['income_money'] = $post['money'] * (1 - $post['rate'] / 100);

        $memberWithdraw->valid($post, 'withdraw');

        $member->where('member_id', $post['member_id'])->value('balance', 0) < $post['money'] &&
        abort(-1, '余额不足');

        // 减少余额
        $member->where('member_id', $post['member_id'])->dec('balance', $post['money'])->update();

        $memberWithdraw::create($post);

        return apiShow([], '操作成功', 1);
    }

    /**
     * 我的下级
     * @param MemberTree $memberTree
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function subordinate(MemberTree $memberTree)
    {
        $get = $this->request->get();
        $get['member_id'] = $this->deToken(0)->mid;

        $where = [
            ['parent_id', '=', $get['member_id']],
            ['level', '=', 1],
            ['member.is_agency', '=', $get['level'] ?? 1],
        ];
        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['phone|name|nickname|platform_id', 'like', '%' . $get['keyword'] . '%'];
        }
        if (isset($get['register_time']) && $get['register_time']) {
            $where[] = ['member.register_time', 'between', [strtotime($get['register_time'] . ' 0:00:00'), strtotime($get['register_time'] . ' 23:59:59')]];
        }

        $data = $memberTree
            ->where($where)
            ->field('parent_id,level')
            ->withJoin([
                'member' => ['name', 'nickname', 'avatar', 'platform_id', 'phone', 'register_time', 'sex', 'member_id']
            ])
            ->paginate(10)
            ->each(function ($val) use ($memberTree) {
                $val->member->child_num = $memberTree
                    ->where([
                        ['parent_id', '=', $val->member->member_id],
                        ['level', '=', 1],
                    ])->count();
                // 手机号中间四位用*代替
                $val->member->phone = substr_replace($val->member->phone, '****', 3, 4);
                $val->member->show_register_time = datetime($val->member->register_time, 'Y/m/d H:i');
                $val->member->amount = 0.00;
            });

        return apiShow($data);
    }

    /**
     * 本月新增
     * @param MemberTree $memberTree
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function monthly_new(MemberTree $memberTree)
    {
        $get = $this->request->get();
        $get['member_id'] = $this->deToken(0)->mid;

        $where = [
            ['parent_id', '=', $get['member_id']],
            ['level', '=', 1],
            ['member_tree.create_time', 'between', getMonth(date('Y-m-d'))],
        ];
        if (isset($get['is_valid']) && $get['is_valid']) {
            $where[] = ['member.is_agency', '=', 1];
        }

        $data = $memberTree
            ->where($where)
            ->field('parent_id,level')
            ->withJoin([
                'member' => ['name', 'nickname', 'avatar', 'platform_id', 'phone', 'register_time', 'sex', 'member_id']
            ])
            ->order('create_time desc')
            ->paginate(10)
            ->each(function ($val) use ($memberTree) {
                // 手机号中间四位用*代替
                $val->member->phone = substr_replace($val->member->phone, '****', 3, 4);
                $val->member->show_register_time = datetime($val->member->register_time, 'Y/m/d H:i');
                $val->member->amount = 0.00;
            });

        return apiShow($data);
    }
}