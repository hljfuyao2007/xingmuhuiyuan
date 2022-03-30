<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-09
 * Time: 9:00
 * Description:
 */

namespace app\admin\controller\member;


use app\admin\model\Member as MemberModel;
use app\common\controller\AdminController;

class Member extends AdminController
{
    /**
     * 会员列表
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(MemberModel $member)
    {
        if ($this->request->isAjax()) {
            list($limit, $where) = $this->buildTableParam($member);

            $data = $member
                ->where($where)
                ->withoutField('update_time,delete_time')
                ->order('member_id', 'desc')
                ->paginate($limit)
                ->toArray();

            return $this->success($data);
        }

        return $this->fetch();
    }

    /**
     * 添加
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function add(MemberModel $member)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            $member->valid($param, 'member');

            $member->is_exist($param['phone'], '用户存在, 不能重复创建');

            $member::create($param);

            return $this->success([], '添加成功', 1);
        }

        return $this->fetch();
    }

    /**
     *
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function view(MemberModel $member)
    {
        if ($this->request->isPost()) {
            $param = $this->request->post();

            if ($param['password'] && $param['confirm_passowrd']) {
                if ($param['password'] != $param['confirm_passowrd']) {
                    return apiShow([], '两次密码不一致', -1);
                }
            } else {
                unset($param['password']);
            }
            unset($param['confirm_passowrd']);

            $member->valid($param, 'view');

            $member::update($param);

            return $this->success([], '操作成功', 1);
        }

        $data = $member
            ->where('member_id', $this->request->get('member_id'))
            ->field('member_id,nickname,phone,email,avatar,status')
            ->find();

        return $this->fetch('', [
            'item' => $data
        ]);
    }

    /**
     * 属性修改
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function modify(MemberModel $member)
    {
        $param = $this->request->post();

        $find = $member->find($param['id']);
        $find->status = $param['value'];
        $find->save();

        return $this->success([], '保存成功');
    }
}