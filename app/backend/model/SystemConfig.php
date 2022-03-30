<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-14
 * Time: 15:50
 * Description:
 */

namespace app\backend\model;

use app\admin\service\TriggerService;

class SystemConfig extends \app\common\model\SystemConfig
{
    public static function onAfterWrite($model)
    {
        TriggerService::updateSystemConfig();
    }
}