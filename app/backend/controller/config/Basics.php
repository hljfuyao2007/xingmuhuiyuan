<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-11
 * Time: 13:15
 * Description:
 */

namespace app\backend\controller\config;

use app\backend\model\SystemConfig;
use app\common\controller\BackendController;
use app\common\model\FileManage;

class Basics extends BackendController
{
    /**
     * 列表
     * @return array|\think\response\Json
     */
    public function index()
    {
        $site = sysconfig('site');
        $site['site_logo_view'] = filePathJoin($site['site_logo']);
        return apiShow([
            'site'     => $site,
            'selected' => [
                (int)$site['site_location_province'],
                (int)$site['site_location_city'],
                (int)$site['site_location_area']
            ]
        ]);
    }

    /**
     * 编辑
     * @return array|\think\response\Json
     */
    public function edit()
    {
        $post = $this->request->post();

        foreach ($post as $key => $item) {
            SystemConfig::update(['value' => $item], ['name' => $key]);
        }

        return apiShow([], '操作成功', 1);
    }

    /**
     * 上传管理
     * @return array|\think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function fileManageList()
    {
        $get = $this->request->get();

        $where = [];
        if (isset($get['type']) && $get['type']) {
            $where[] = ['type', '=', $get['type']];
        }
        if (isset($get['mime']) && $get['mime']) {
            $where[] = ['mime', '=', $get['mime']];
        }

        $data = FileManage
            ::where($where)
            ->withAttr('url', function ($value) {
                return filePathJoin($value);
            })
            ->withoutField('update_time,delete_time')
            ->order('create_time desc')
            ->paginate($get['limit']);

        return apiShow($data);
    }

    /**
     * 上传管理删除
     * @param FileManage $fileManage
     * @return array|\think\response\Json
     */
    public function fileManageDel(FileManage $fileManage)
    {
        $id = $this->request->post('file_id', '');

        $url = $fileManage->where('file_id', $id)->value('url', '');

        switch (sysconfig('upload', 'upload_type')) {
            case 'alioss':
                // 删除OSS上的文件
                app('app\\common\\service\\OSS')->deleteFile($url);
                break;
            case 'qnoss':
            case 'txcos':
                break;
            default:
                unlink(public_path() . $url);
        }

        $fileManage::destroy($id, true);

        return apiShow([], '删除成功', 1);
    }
}