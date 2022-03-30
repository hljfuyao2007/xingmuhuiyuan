<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-01
 * Time: 13:29
 * Description:
 */

namespace app\admin\model;


class FileManage extends \app\common\model\FileManage
{
    /**
     * 文件链接全路径获取器
     * @param $value
     * @return string
     */
    public function getUrlAttr($value): string
    {
        return filePathJoin($value);
    }
}