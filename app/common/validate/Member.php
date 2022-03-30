<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-09
 * Time: 10:58
 * Description:
 */

namespace app\common\validate;


use think\Validate;

class Member extends Validate
{
    protected $rule = [
        'phone|手机号'   => 'require|mobile|unique:member',
        'email|邮箱'    => 'email',
        'password|密码' => 'require|length:6,18|check_password',
        'status|状态'   => 'require'
    ];

    protected $scene = [
        'member' => ['phone', 'password', 'email', 'status'],
        'view'   => ['phone', 'email', 'status']
    ];

    // 验证密码是否为数字字母组合
    public function check_password($value)
    {
        return preg_match('/^(?![^a-zA-Z]+$)(?!\D+$)/', $value) ? true : '必须为数字字母组合';
    }
}