<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-05
 * Time: 8:58
 * Description:
 */

namespace app\admin\controller;


use app\admin\model\Districts;
use app\common\model\FileManage;
use app\common\controller\AdminController;
use think\response\Json;

class Common extends AdminController
{
    /**
     * 上传文件列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_file_manage(): Json
    {
        $param = $this->request->get();
        $page = isset($param['page']) && !empty($param['page']) ? $param['page'] : 1;
        $limit = isset($param['limit']) && !empty($param['limit']) ? $param['limit'] : 10;
        $title = isset($param['title']) && $param['title'] ? $param['title'] : null;

        $count = FileManage
            ::where([
                ['type', 'like', "%{$title}%"],
                ['type', 'in', ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp']]
            ])->count();
        $list = FileManage
            ::where([
                ['type', 'like', "%{$title}%"],
                ['type', 'in', ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp']]
            ])
            ->withoutField('delete_time,update_time')
            ->page($page, $limit)
            ->order('file_id', 'desc')
            ->select()
            ->each(function ($item) {
                $item->view_url = filePathJoin($item->url);
            });
        $data = [
            'code'  => 0,
            'msg'   => '',
            'count' => $count,
            'data'  => $list,
        ];

        return json($data);
    }

    /**
     * 地区三级联动
     * @param Districts $districts
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_area(Districts $districts)
    {
        $param = $this->request->get();

        $data = [];
        if ($param['pid'] == 0 && $param['pid'] != '') {
            $data = $districts->getProvince();
        } elseif ($param['pid'] > 0) {
            $data = $districts->getCityOrArea($param['pid']);
        }

        return $this->success($data);
    }

    /**
     * 获取搜索select数据
     * @return array|false|mixed|Json|\think\response\View
     */
    public function getSelect()
    {
        $param = $this->request->post();

        $where = [
            ['delete_time', '=', null]
        ];
        if (isset($param['where']) && $param['where']) {
            $where[] = $param['where'];
        }

        $data = $this->db
            ->name($param['table'])
            ->where($where)
            ->column(...explode(',', $param['field']));

        return $this->success($data);
    }
}