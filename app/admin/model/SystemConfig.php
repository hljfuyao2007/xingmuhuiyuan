<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-02
 * Time: 16:46
 * Description:
 */

namespace app\admin\model;


use app\admin\service\TriggerService;

class SystemConfig extends \app\common\model\SystemConfig
{
    public static function onAfterWrite($model)
    {
        TriggerService::updateSystemConfig();
    }
}