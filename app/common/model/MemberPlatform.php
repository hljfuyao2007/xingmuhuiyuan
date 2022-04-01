<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-04-01
 * Time: 10:30
 * Description:
 */

namespace app\common\model;

class MemberPlatform extends BasicModel
{
    protected $pk = 'mp_id';

    /**
     * 会员一对一
     * @return \think\model\relation\HasOne
     */
    public function member(): \think\model\relation\HasOne
    {
        return $this->hasOne(Member::class, 'member_id', 'member_id');
    }

    /**
     * 平台一对一
     * @return \think\model\relation\HasOne
     */
    public function platform(): \think\model\relation\HasOne
    {
        return $this->hasOne(Platform::class, 'platform_id', 'platform_id');
    }
}