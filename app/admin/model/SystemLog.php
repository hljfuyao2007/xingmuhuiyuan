<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-10-22
 * Time: 16:31
 * Description:
 */

namespace app\admin\model;

class SystemLog extends \app\common\model\SystemLog
{
    /**
     * 管理员
     * @return \think\model\relation\HasOne
     */
    public function manage(): \think\model\relation\HasOne
    {
        return $this->hasOne(Manage::class, 'manage_id', 'manage_id');
    }
}