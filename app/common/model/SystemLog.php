<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-10-22
 * Time: 16:30
 * Description:
 */

namespace app\common\model;

class SystemLog extends BasicModel
{
    protected $pk = 'id';

    /**
     * ip修改器
     * @param $value
     * @return false|int
     */
    public function setIpAttr($value)
    {
        return ip2long($value);
    }

    /**
     * ip获取器
     * @param $value
     * @return false|string
     */
    public function getIpAttr($value)
    {
        return long2ip($value);
    }
}