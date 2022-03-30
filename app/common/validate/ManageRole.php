<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-09
 * Time: 17:13
 * Description:
 */

namespace app\common\validate;


use think\Validate;

class ManageRole extends Validate
{
    protected $rule = [
        'role_id|角色id' => 'require',
        'title|名称'     => 'require|max:15',
        'describe|描述'  => 'max:100'
    ];

    protected $scene = [
        'add'  => ['title', 'describe'],
        'edit' => ['role_id', 'title', 'describe'],
        'del'  => ['role_id']
    ];
}