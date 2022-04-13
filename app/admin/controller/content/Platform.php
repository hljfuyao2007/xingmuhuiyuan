<?php
/**
 * Created by Automatic scripts.
 * User: Kassy
 * Date: 2022-04-13
 * Time: 10:05:43
 * Description:
 */

namespace app\admin\controller\content;


use app\admin\model\Platform as PlatformModel;
use app\common\controller\AdminController;
use app\common\model\MemberPlatform;

class Platform extends AdminController
{
    /**
     * 列表
     * @param PlatformModel $platformModel
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(PlatformModel $platformModel)
    {
        if ($this->request->isAjax()) {
            [$limit, $where] = $this->buildTableParam($platformModel);

            $data = $platformModel
                ->where($where)
                ->withoutField('update_time,delete_time')
                ->order('platform_id', 'desc')
                ->paginate($limit);

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 添加
     * @param PlatformModel $platform
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function add(PlatformModel $platform)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $platform->valid($post, 'add');

            $platform::create($post);

            return $this->success([], '添加成功', 1);
        }

        return $this->fetch();
    }

    /**
     * 编辑
     * @param PlatformModel $platform
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(PlatformModel $platform)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $platform->valid($post, 'add');

            $platform::update($post);

            return $this->success([], '编辑成功', 1);
        }

        $data = $platform->find($this->request->get('platform_id'));

        return $this->fetch('add', ['item' => $data]);
    }

    /**
     * 删除
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function del()
    {
        if (MemberPlatform::where('platform_id', $this->request->get('platform_id'))->value('mp_id')) {
            abort(-1, '该平台已被使用，不能删除');
        }

        PlatformModel::destroy($this->request->post('platform_id'));

        return $this->success([], '删除成功', 1);
    }

    /**
     * 属性修改
     * @param PlatformModel $platform
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function modify(PlatformModel $platform)
    {
        $param = $this->request->post();

        $rule = [
            'id|ID'    => 'require',
            'field|字段' => 'require',
            'value|值'  => 'require',
        ];
        $this->validate($param, $rule);

        $platform::update(['platform_id' => $param['id'], $param['field'] => $param['value']]);

        return $this->success([], '保存成功', 1);
    }
}