<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-05
 * Time: 21:01
 * Description:
 */

namespace app\common\model;


class Districts extends BasicModel
{
    protected $pk = 'id';

    /**
     * 获取省
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProvince(): array
    {
        return self
            ::where([
                ['pid', '=', 0],
                ['deep', '=', 0]
            ])
            ->field('id,pid,deep,name,ext_name')
            ->select()
            ->toArray();
    }

    /**
     * 获取市区
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCityOrArea($id): array
    {
        return self
            ::where([
                ['pid', '=', $id]
            ])
            ->field('id,pid,deep,name,ext_name')
            ->select()
            ->toArray();
    }
}