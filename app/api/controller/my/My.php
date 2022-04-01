<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-03-31
 * Time: 13:23
 * Description:
 */

namespace app\api\controller\my;

use app\api\model\Member;
use app\api\model\MemberPlatform;
use app\api\model\Platform;
use app\common\controller\ApiController;

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
}