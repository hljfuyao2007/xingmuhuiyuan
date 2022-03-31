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
        'phone|手机号'       => 'require|mobile|unique:member',
        'email|邮箱'        => 'email',
        'password|密码'     => 'require|length:6,18|check_password',
        'status|状态'       => 'require',
        'sms_code|验证码'    => 'require',
        'code|code'       => 'require',
        'nickname|昵称'     => 'require',
        'avatar|头像'       => 'require',
        'invite_code|邀请码' => 'max:6'
    ];

    protected $scene = [
        'member'      => ['phone', 'password', 'status'],
        'view'        => ['phone', 'email', 'status'],
        'wx_login'    => ['code', 'nickname', 'avatar'],
        'union_login' => ['phone', 'sms_code', 'password', 'invite_code'],
        'register'    => ['nickname', 'phone', 'sms_code', 'password', 'invite_code']
    ];

    // 验证密码是否为数字字母组合
    public function check_password($value)
    {
        return preg_match('/^(?![^a-zA-Z]+$)(?!\D+$)/', $value) ? true : '必须为数字字母组合';
    }

    /**
     * 短信登录
     * @return Member
     */
    public function sceneSmsLogin(): Member
    {
        return $this->only(['phone', 'sms_code'])
            ->remove('phone', 'unique');
    }

    /**
     * 账号登录
     * @return Member
     */
    public function sceneAccountLogin(): Member
    {
        return $this->only(['phone', 'password'])
            ->remove('phone', 'unique')
            ->remove('password', 'check_password');
    }

    /**
     * 忘记密码
     * @return Member
     */
    public function sceneForgetPwd(): Member
    {
        return $this->only(['phone', 'password', 'sms_code'])
            ->remove('phone', 'unique');
    }
}
