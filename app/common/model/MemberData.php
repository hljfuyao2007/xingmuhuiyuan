<?php

namespace app\common\model;

class MemberData extends BasicModel
{
    protected $pk = 'data_id';

    /**
     * 用户称昵称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getNicknameAttr($value, $data)
    {
        return Member::where('member_id', $data['member_id'])->value('nickname', '');
    }
}