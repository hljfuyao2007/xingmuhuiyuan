<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-26
 * Time: 23:47
 * Description:
 */

namespace app\common\validate;


use think\Validate;

class Manage extends Validate
{
    protected $rule = [
        'username|用户名'          => 'require|max:20|alphaNum',
        'role_id|权限组'           => 'require',
        'avatar|头像'             => 'require',
        'password|密码'           => 'requireWith:confirm_password|length:6,18|check_password',
        'confirm_password|确认密码' => 'requireWith:password|confirm:password|length:6,18',
        'phone|手机号'             => 'require|mobile',
        'email|邮箱号'             => 'email',
        'keep_login|是否保持登录'     => 'require',
        'captcha|验证码'           => 'require|captcha'
    ];

    protected $scene = [
        'login'        => ['username', 'password'],
        'login_verify' => ['username', 'password', 'captcha'],
        'add'          => ['avatar', 'username', 'phone', 'email', 'password', 'confirm_password', 'role_id'],
        'edit'         => ['avatar', 'username', 'phone', 'email', 'role_id', 'password', 'confirm_password']
    ];

    // 验证密码是否为数字字母组合
    public function check_password($value)
    {
        return preg_match('/^(?![^a-zA-Z]+$)(?!\D+$)/', $value) ? true : '必须为数字字母组合';
    }

    /**
     * 编辑验证场景
     * @return Manage
     */
    public function sceneEdit(): Manage
    {
        return $this->only(['avatar', 'username', 'phone', 'email', 'role_id', 'password', 'confirm_password'])
            ->remove('password', 'require')
            ->remove('confirm_password', 'require');
    }

    /**
     * 登录场景验证
     * @return Manage
     */
    public function sceneLogin(): Manage
    {
        return $this->only(['username', 'password'])
            ->remove('password', 'length')
            ->remove('password', 'check_password');
    }
}