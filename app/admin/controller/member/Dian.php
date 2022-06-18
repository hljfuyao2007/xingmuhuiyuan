<?php
/**
 * Created by Automatic scripts.
 * User: Kassy
 * Date: 2022-04-12
 * Time: 09:39:47
 * Description:
 */

namespace app\admin\controller\member;


use app\admin\model\Member as MemberModel;
use app\admin\model\MemberWithdraw;
use app\common\controller\AdminController;
use think\facade\Db;

class Dian extends AdminController
{
    /**
     * 列表
     * @param MemberWithdraw $memberWithdraw
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(MemberWithdraw $memberWithdraw)
    {


        if ($this->request->isAjax()) {
            
            $d=Db::name("dian")->select();
            $type_arr=[

                1=>"基础点位",2=>"增长点位",3=>"二级团队"

            ];
            $dian=[];
            foreach ($d as $key => $value) {
                $value["type_name"]=$type_arr[$value["type"]];
                $dian[]=$value;
            }
            $arr=["total"=>count($dian), 
                "per_page"  =>  100,
                "current_page"=>1   ,
                "last_page"=>0  ,
                "data"=>$dian
             ];
            return $this->success($arr);
        }

        return $this->fetch();
    }
    /**
     * 添加
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();



            Db::name("dian")->insert([
                'type' => $param['type'],
                'num' => $param['num'],
                'money' => $param['money'],
                'dian' => $param['dian'],
                'create_time'=>date("Y-m-d H:i:s"),
                'update_time'=>date("Y-m-d H:i:s"),
            ]);
           

            return $this->success([], '添加成功', 1);
        }

        return $this->fetch();
    }
    /**
     * 审核
     * @param MemberWithdraw $memberWithdraw
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(MemberWithdraw $memberWithdraw)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $this->db->startTrans();

            if ($post['status'] == 2) {
                MemberModel::where('member_id', $post['member_id'])->inc('balance', $post['money'])->update();
            }

            $memberWithdraw::update($post);

            $this->db->commit();

            return $this->success([], '操作成功', 1);
        }

        $data = $memberWithdraw
            ->where('withdraw_id', $this->request->get('withdraw_id'))
            ->withoutField('update_time,delete_time')
            ->append(['nickname'])
            ->find();

        return $this->fetch('', [
            'item' => $data
        ]);
    }



    public function del()
    {   
        $param = $this->request->param();
        $id=$param["id"];
        return $this->fetch('', [
            'id' => $id
        ]);

    }
    public function delete()
    {
        $param = $this->request->param();
        $id=$param["id"];
        Db::name("dian")->where("id",$id)->delete();
        return $this->success([], '删除成功', 1);

    }
}