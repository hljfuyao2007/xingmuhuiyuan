<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-11
 * Time: 22:18
 * Description:
 */

namespace app\common\validate;


use think\Validate;

class Menu extends Validate
{
    protected $rule = [
        'title|标题' => 'require|max:30',
        'sort|排序'  => 'require',
        'deep|类型'  => 'require'
    ];
}

