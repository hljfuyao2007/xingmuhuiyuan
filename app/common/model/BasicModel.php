<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-19
 * Time: 15:44
 * Description:
 */

namespace app\common\model;


use think\exception\ValidateException;
use think\Model;
use think\model\concern\SoftDelete;

class BasicModel extends Model
{
    use SoftDelete;

    /**
     * 模型事件新增前
     * @param Model $model
     * @return mixed|void
     */
    public static function onBeforeInsert(Model $model)
    {
        $model->create_time = time();
    }

    /**
     * 模型事件写入前
     * @param Model $model
     * @return mixed|void
     */
    public static function onBeforeWrite(Model $model)
    {
        $model->update_time = time();
    }

    /**
     * 验证数据
     * @param array $data 被验证的数据
     * @param string $scene 验证场景
     * @param string $prefix 返回验证信息前缀
     */
    public function valid(array $data, string $scene = '', string $prefix = '')
    {
        $validInstance = app("app\\common\\validate\\{$this->getName()}");
        $res = $validInstance->scene($scene)->check($data);
        if (!$res) {
            throw new ValidateException($prefix . $validInstance->getError());
        }
    }

    /**
     * 创建时间获取器
     * @param $value
     * @return string
     */
    public function getCreateTimeAttr($value): string
    {
        return datetime($value);
    }

    /**
     * 更新时间获取器
     * @param $value
     * @return string
     */
    public function getUpdateTimeAttr($value): string
    {
        return datetime($value);
    }
}