<?php

namespace app\common\validate;

use think\Validate;

class MemberWithdraw extends Validate
{
    protected $rule = [
        'name|姓名' => 'require|max:5',
        'account|提现账户' => 'require',
        'money|提现金额' => 'require|between:0,999999.99',
        'income_money|实际到账金额' => 'require|between:0,999999.99',
        'rate|手续费率' => 'require',
    ];

    protected $scene = [
        'withdraw' => ['name', 'account', 'money', 'income_money', 'rate'],
    ];
}