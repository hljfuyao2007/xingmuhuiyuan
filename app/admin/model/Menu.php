<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-26
 * Time: 23:34
 * Description:
 */

namespace app\admin\model;


use think\Model;

class Menu extends \app\common\model\Menu
{
    /**
     * 图标获取器
     * @param $value
     * @return string
     */
    public function setIconAttr($value): string
    {
        if ($value && stripos($value, 'fa ') !== 0) {
            $value = 'fa ' . $value;
        }
        return $value;
    }
}