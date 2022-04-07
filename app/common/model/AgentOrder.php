<?php

namespace app\common\model;

use think\Model;

class AgentOrder extends BasicModel
{
    protected $pk = 'order_id';

    public static function onBeforeInsert(Model $model)
    {
        parent::onBeforeInsert($model);
        $model->order_number = generateOrderNo();
    }
}