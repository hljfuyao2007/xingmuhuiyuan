<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-03-31
 * Time: 13:23
 * Description:
 */

namespace app\api\controller\my;

use app\api\model\AgentOrder;
use app\api\model\Member;
use app\api\model\MemberPlatform;
use app\api\model\Platform;
use app\common\controller\ApiController;
use app\common\service\EasyWechat;
use think\facade\Request;

class My extends ApiController
{
    /**
     * 我的
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function my(Member $member)
    {
        $member_id = $this->deToken(0)->mid;

        $data = $member
            ->where('member_id', $member_id)
            ->field('avatar,nickname,phone,platform_id,register_time')
            ->find();

        return apiShow($data);
    }

    /**
     * 选择平台
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function choose_platform()
    {
        $mid = $this->deToken(0)->mid;

        $data = [];
        $data['memberInfo'] = Member
            ::where('member_id', $mid)
            ->field('nickname,avatar,phone,register_time,sex')
            ->find();
        $data['platformInfo'] = MemberPlatform
            ::where('member_platform.member_id', $mid)
            ->field('mp_id,create_time')
            ->withJoin(['platform' => ['platform_id', 'name']])
            ->order('mp_id desc')
            ->select();

        return apiShow($data);
    }

    /**
     * 平台介绍
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function platform_introduce()
    {
        $get = $this->request->get();

        $data = Platform
            ::where('platform_id', $get['platform_id'])
            ->field('platform_id,name,content')
            ->find();

        return apiShow($data);
    }

    /**
     * 代理须知
     * @return array|\think\response\Json
     */
    public function agency_notice()
    {
        return apiShow(['content' => sysconfig('site', 'agency_notice')]);
    }

    /**
     * 根据身份证获取年龄
     * @return array|\think\response\Json|void
     */
    public function getAge()
    {
        $idCard = $this->request->get('idCard');
        $reg = '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/';
        if (!preg_match($reg, $idCard)) {
            abort(-1, '身份证号码不正确');
        }
        return apiShow(['age' => getAgeByIdCard($idCard)]);
    }

    /**
     * 个人信息
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function info()
    {
        $mid = $this->deToken(0)->mid;

        $data = Member
            ::where('member_id', $mid)
            ->field('nickname,name,avatar,phone,alipay_account,id_card,platform_id,register_time,is_identity')
            ->find();
        $data['age'] = getAgeByIdCard($data['id_card']);

        return apiShow($data);
    }

    /**
     * 提现信息认证
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authentication(Member $member)
    {
        $post = $this->request->post();
        $post['member_id'] = $this->deToken(0)->mid;
        $post['is_identity'] = 1;

        $member->valid($post, 'authentication');

        $member::update($post);

        return apiShow([], '操作成功', 1);
    }

    /**
     * 成为代理
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function become_agent()
    {
        $mid = $this->deToken(0)->mid;

        $member = Member::find($mid);
        if ($member['is_agency'] == 1) {
            abort(-1, '您已经是代理了');
        }

        $site = sysconfig('site');

        return apiShow([
            'agency_money'          => $site['agency_money'],
            'agency_service_charge' => $site['agency_service_charge']
        ]);
    }

    /**
     * 成为代理支付
     * @param AgentOrder $agentOrder
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function agent_pay(AgentOrder $agentOrder)
    {
        $post = $this->request->post();
        $post['member_id'] = $this->deToken(0)->mid;

        // 生成订单
        $site = sysconfig('site');

        // 查询订单根据会员id
        $order = $agentOrder
            ->where([
                ['member_id', '=', $post['member_id']],
                ['status', '=', 0]
            ])
            ->withoutField('update_time,delete_time')
            ->find();
        if ($order) {
            $order->order_number = generateOrderNo();
            if ($order['total_fee'] != $site['agency_money'] + $site['agency_service_charge']) {
                $order->agency_money = $site['agency_money'];
                $order->service_charge = $site['agency_service_charge'];
                $order->total_fee = $site['agency_service_charge'] + $site['agency_money'];
            }
            $order->save();
        } else {
            $order = $agentOrder::create([
                'member_id'      => $post['member_id'],
                'agency_money'   => $site['agency_money'],
                'service_charge' => $site['agency_service_charge'],
                'total_fee'      => $site['agency_money'] + $site['agency_service_charge']
            ]);
        }

        $args = [];
        $args['notify_url'] = Request::domain() . '/api/v1.0/pay/wxNotify';
        $args['trade_type'] = 'MWEB';
        $args['total_fee'] = intval($order['total_fee'] * 100);
        $args['out_trade_no'] = $order['order_number'];
        $args['body'] = '成为代理';
        $args['attach'] = "agent|{$post['member_id']}|{$order['order_id']}";

        $config = (new EasyWechat('payment', 'MWEB'))->pre_order($args, $order);

        return apiShow([
            'config'       => $config,
            'order_number' => $order['order_number'],
            'total_fee'    => $order['total_fee']
        ]);
    }
}