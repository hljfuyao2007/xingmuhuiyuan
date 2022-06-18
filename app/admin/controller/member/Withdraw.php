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
use app\admin\model\MemberTree as MemberMemberTree;
use app\admin\model\MemberWithdraw;
use app\common\controller\AdminController;
use think\facade\Db;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Withdraw extends AdminController
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
            [$limit, $where] = $this->buildTableParam($memberWithdraw);
             $platform=Db::name("platform")->field("name,platform_id")->where("is_show",1)->select();
            $p_arr=[];
            foreach ($platform as $key => $value) {
                $p_arr[$value["platform_id"]]=$value["name"];
            }
            $data = $memberWithdraw
                ->where($where)
                ->withoutField('update_time,delete_time')
                ->order('withdraw_id', 'desc')
                ->paginate($limit)
                ->each(function ($val) {
                    $val->rate = $val->rate . '%';
                    $val->id = $val->withdraw_id;
                    //$val->platform = $p_arr[$val->platform_id];
                });
            foreach ($data as $key => $value) {
                $value["platform"]=$p_arr[$value["platform_id"]];
            }
            return $this->success($data);
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
                $withdraw=$memberWithdraw::where("withdraw_id",$post['withdraw_id'])->find();
                MemberMemberTree::where('member_id', $post['member_id'])
                ->where("platform_id",$withdraw["platform_id"])
                ->where("delete_time","=",null)
                ->inc('money', $post['money'])
                ->update();
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


    public function edit_all(MemberWithdraw $memberWithdraw)
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();

            $this->db->startTrans();
            $ids=explode(",",$param['id']);
            foreach ($ids as $key => $value) {
                $status=$memberWithdraw::where("withdraw_id",$value)->value("status");
                if ($param['status'] == 2 && $status == 0) {
                    //MemberModel::where('member_id', $post['member_id'])->inc('balance', $post['money'])->update();
                    $withdraw=$memberWithdraw::where("withdraw_id",$value)->find();
                    MemberMemberTree::where('member_id', $withdraw['member_id'])
                    ->where("platform_id",$withdraw["platform_id"])
                    ->where("delete_time","=",null)
                    ->inc('money', $withdraw['money'])
                    ->update();
                }
                $memberWithdraw::where("withdraw_id",$value)
                ->update([
                    "status"=>$param['status'],
                    "cause"=>$param["cause"]
                ]);

            }
            $this->db->commit();
            return $this->success([], '操作成功', 1);
        }

       
        return $this->fetch();
    }



    public function dian()
    {
        
        return $this->fetch('', [
            //'item' => $data
        ]);
    }

    /**
     * @NodeAnotation(title="导出")
     */
    public function export(MemberWithdraw $memberWithdraw)
    {
        
        $param=$this->request->param();
            $columName=['姓名','提现账户','提现金额','平台','实际到账金额','手续费率','提现时间','状态'];

            // [$limit, $where] = $this->buildTableParam($memberWithdraw);
            $platform=Db::name("platform")->field("name,platform_id")->where("is_show",1)->select();

            $p_arr=[];
            foreach ($platform as $key => $value) {
                $p_arr[$value["platform_id"]]=$value["name"];
            }
            
            $data = $memberWithdraw
                ->whereIn("withdraw_id",json_decode($param["id"],true))
                //->withoutField('update_time,delete_time')
                ->order('withdraw_id', 'desc')
                ->select()->toArray();
            
            $list=[];
            foreach ($data as $k => $v) {
                
                $list[]=[
                    $v["name"],
                    $v["account"],
                    $v["money"],
                    $p_arr[$v["platform_id"]],//$v["platform"],
                    $v["income_money"],
                    $v["rate"].'%',
                    $v["create_time"],
                    $v["status"]==1?"已打款":($v["status"]==0?"待审核":"已拒绝"),
                ];
            }
    
        ///dump($param);exit;
        return $this->exportExcel($columName,$list);
    }

    public function exportExcel($columName, $list, $fileName='xingmu_tixian_download',$download=false){

        $count = count($columName);  //计算表头数量
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始，循环设置表头：
            $sheet->setCellValue(strtoupper(chr($i)) . '1', $columName[$i - 65]);
        }
        //循环设置单元格：
        foreach ($list as $key => $item) {
            //$key+2,因为第一行是表头，所以写到表格时   从第二行开始写
            for ($i = 65; $i < $count + 65; $i++) {
                //数字转字母从65开始：
                $sheet->setCellValue(strtoupper(chr($i)) . ($key + 2), $item[$i - 65]);
                //固定列宽
                $spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(20);
            }

        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        if($download){
            $load_Path=$_SERVER['SCRIPT_FILENAME'];
            $load_Path="./";
            $user_path = $load_Path.'excel/';//保存路径
            $writer->save($user_path.$fileName.'.xlsx');//保存excle文件
            return $fileName.'.xlsx';
        }else{

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }
 
    }
}