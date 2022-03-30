<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-09
 * Time: 9:03
 * Description:
 */

namespace app\common\model;


use think\facade\Request;
use think\Model;

class Member extends BasicModel
{
    protected $pk = 'member_id';

    public static function onBeforeWrite(Model $model)
    {
        parent::onBeforeWrite($model);

        if (isset($model->password)) {
            if (strlen($model->password) <= 18 && strlen($model->password) >= 6) {
                $model->password = encrypt(trim($model->password));
            } else {
                unset($model->password);
            }
        }
    }

    public static function onBeforeInsert(Model $model)
    {
        parent::onBeforeInsert($model);

        $sole = uniqid(env('admin.salt', 'tpl'));
        if (!isset($model->username) || !$model->username) {
            $model->username = $sole;
        }
        if (!isset($model->nickname) || !$model->nickname) {
            $model->nickname = $sole;
        }
        // 初始用户信息
        $model->register_time = time();
        $model->status = 1;
        $model->register_ip = ip2long(Request::ip());
        $model->login_time = time();
        $model->login_ip = ip2long(Request::ip());
    }

    /**
     * 注册时间获取器
     * @param $value
     * @return string
     */
    public function getRegisterTimeAttr($value): string
    {
        return datetime($value);
    }

    /**
     * 登录时间获取器
     * @param $value
     * @return string
     */
    public function getLoginTimeAttr($value): string
    {
        return datetime($value);
    }

    /**
     * 注册IP获取器
     * @param $value
     * @return string
     */
    public function getRegisterIpAttr($value): string
    {
        return long2ip($value);
    }

    /**
     * 登入IP获取器
     * @param $value
     * @return string
     */
    public function getLoginIpAttr($value): string
    {
        return long2ip($value);
    }

    /**
     * 登入IP修改器
     * @param $value
     * @return false|int
     */
    public function setLoginIpAttr($value)
    {
        return ip2long($value);
    }

    /**
     * 头像获取器
     * @param $value
     * @param $data
     * @return string
     */
    public function getAvatarAttr($value, $data): string
    {
        return $value ? filePathJoin($value) : letter_avatar($data['nickname']);
    }

    /**
     * 用户是否存在
     * @param $keyword
     * @param string $errmsg
     * @return bool
     */
    public function is_exist($keyword, string $errmsg = ''): bool
    {
        $data = $this->where('member_id|phone|email', $keyword)->value('member_id', '');

        if ($data) {
            abort(-1, $errmsg ?: '用户已存在');
        }
        return true;
    }
}