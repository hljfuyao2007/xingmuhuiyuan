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
}