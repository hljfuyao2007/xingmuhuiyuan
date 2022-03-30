<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-01
 * Time: 14:56
 * Description:
 */

namespace app\backend\model;

use app\admin\model\ManageRole;
use think\Model;

class Manage extends \app\common\model\Manage
{
    public static function onBeforeWrite(Model $model)
    {
        parent::onBeforeInsert($model);

        if (isset($model->password)) {
            if (strlen($model->password) <= 18 && strlen($model->password) >= 6) {
                $model->password = encrypt(trim($model->password));
            } else {
                unset($model->password);
            }
        }
    }

    /**
     * 关联管理员角色[一对一]
     * @return \think\model\relation\HasOne
     */
    public function manageRole(): \think\model\relation\HasOne
    {
        return $this->hasOne(ManageRole::class, 'role_id', 'role_id');
    }

    /**
     * 登入时间获取器
     * @param $value
     * @return string
     */
    public function getLoginTimeAttr($value): string
    {
        return datetime($value);
    }

    /**
     * 登入IP获取器
     * @param $value
     * @return false|string
     */
    public function getLastLoginIpAttr($value): string
    {
        return long2ip($value);
    }

    /**
     * 登入IP修改器
     * @param $value
     * @return false|int
     */
    public function setLastLoginIpAttr($value)
    {
        return ip2long($value);
    }
}