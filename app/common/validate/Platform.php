<?php

namespace app\common\validate;

use think\Validate;

class Platform extends Validate
{
    protected $rule = [
        'platform_id|平台ID' => 'require',
        'name|平台名'         => 'require|max:50',
        'sort|排序'          => 'require',
        'is_show|显示'       => 'require|in:0,1',
    ];

    protected $scene = [
        'add'  => ['name', 'sort', 'is_show'],
        'edit' => ['platform_id', 'name', 'sort', 'is_show'],
    ];
}