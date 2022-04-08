<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-03-30
 * Time: 15:38
 * Description:
 */

namespace app\common\model;


class MemberTree extends BasicModel
{
    /**
     * 子级
     * @return \think\model\relation\HasOne
     */
    public function member(): \think\model\relation\HasOne
    {
        return $this->hasOne(Member::class, 'member_id', 'member_id');
    }

    /**
     * 父级
     * @return \think\model\relation\HasOne
     */
    public function parent(): \think\model\relation\HasOne
    {
        return $this->hasOne(Member::class, 'member_id', 'parent_id');
    }
}