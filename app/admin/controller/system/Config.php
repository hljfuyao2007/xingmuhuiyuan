<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-02
 * Time: 14:10
 * Description:
 */

namespace app\admin\controller\system;


use app\admin\model\Districts;
use app\admin\model\FileManage;
use app\admin\model\SystemConfig;
use app\common\controller\AdminController;
use app\common\service\OSS;

class Config extends AdminController
{
    /**
     * 基础设置
     * @param Districts $districts
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function basic(Districts $districts)
    {
        if ($this->request->isPost()) {



            $param = $this->request->post();
            isset($param['sms_type']) && $param['sms_type'] &&
            $param['sms_is_check'] = isset($param['sms_is_check']) ? 'on' : 'off';
            if (isset($param['site_logo']) && $param['site_logo']) {
                // 生成ico图标
               // make_ico($param['site_logo']);
            }


            if (isset($param['agency_money']) && $param['agency_money']) {
                $param['agency_money'] = fmtPrice($param['agency_money']);
            }
            if (isset($param['agency_service_charge']) && $param['agency_service_charge']) {
                $param['agency_service_charge'] = fmtPrice($param['agency_service_charge']);
            }
            foreach ($param as $key => $item) {
                SystemConfig::update(['value' => $item], ['name' => $key]);
            }

            return $this->success([], '操作成功', 1);
        }

        $site = sysconfig('site');
        // 省
        $province = $districts->getProvince();
        // 市
        if ($site['site_location_province']) {
            $city = $districts->getCityOrArea($site['site_location_province']);
        }
        // 区
        if ($site['site_location_city']) {
            $area = $districts->getCityOrArea($site['site_location_city']);
        }
        return $this->fetch('', [
            'site'     => $site,
            'sms'      => sysconfig('sms'),
            'alisms'   => sysconfig('alisms'),
            'province' => $province,
            'city'     => $city ?? [],
            'area'     => $area ?? []
        ]);
    }

    /**
     * 上传管理
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function fileManageList()
    {
        if ($this->request->isAjax()) {
            list($limit, $where) = $this->buildTableParam(FileManage::class);

            $data = FileManage
                ::where($where)
                ->withoutField('update_time,delete_time')
                ->order('create_time desc')
                ->paginate($limit)
                ->toArray();

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 上传管理删除文件
     * @param OSS $oss
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \OSS\Core\OssException
     */
    public function fileManageDel(OSS $oss)
    {
        $id = $this->request->post('file_id', '');

        $url = FileManage::where('file_id', $id)->value('url', '');

        switch (sysconfig('upload', 'upload_type')) {
            case 'alioss':
                // 删除OSS上的文件
                $oss->deleteFile($url);
                break;
            case 'qnoss':
            case 'txcos':
                break;
            default:
                unlink(public_path() . $url);
        }

        FileManage::destroy($id, true);

        return $this->success([], '删除成功', 1);
    }

    /**
     * 上传管理添加
     * @return mixed
     */
    public function fileManageAdd()
    {
        return $this->fetch();
    }
}