<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-26
 * Time: 23:01
 * Description:
 */

namespace app\common\model;


class Manage extends BasicModel
{
    protected $pk = 'manage_id';

    /**
     * 头像获取器
     * @param $value
     * @param $data
     * @return string
     */
    public function getAvatarAttr($value, $data): string
    {
        return $value ? filePathJoin($value) : letter_avatar($data['username']);
    }

    /**
     * 管理员是否存在
     * @param $username
     * @param string $errmsg
     * @return bool
     */
    public function is_exist($username, string $errmsg = ''): bool
    {
        $data = $this->where('username|phone|email', $username)->value('manage_id', '');

        $data && abort(-1, $errmsg ?: '管理员已存在');
        return true;
    }
}