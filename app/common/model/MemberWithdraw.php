<?php

namespace app\common\model;

class MemberWithdraw extends BasicModel
{
    protected $pk = 'withdraw_id';

    /**
     * 获取与昵称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getNicknameAttr($value, $data)
    {
        return Member::where('member_id', $data['member_id'])->value('nickname', '');
    }
}