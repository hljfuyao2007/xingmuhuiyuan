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
use app\admin\model\MemberData as MemberDataModel;

use app\common\controller\AdminController;
use app\common\model\MemberTree;
use think\facade\Db;

class Member extends AdminController
{
    /**
     * 会员列表
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public $dian_arr=[];
    public $dian2_arr=[];
    public $dian3_arr=[];
    public function index(MemberModel $member)
    {
        if ($this->request->isAjax()) {

            [$limit, $where] = $this->member_buildTableParam($member);
             $GLOBALS['admin'] =session("admin");
            if($GLOBALS['admin']["role_id"]==5){
              //$data->where("manage_id",$admin["manage_id"]);
              $where[]=["manage_id","=",$GLOBALS['admin']["manage_id"]];
            }

            $data = $member
                ->where($where)
                ->withoutField('update_time,delete_time')
                ->order('member_id', 'desc')
                ->paginate($limit)
                ->each(function ($item) {
                    $item->sex = $item->sex == 1 ? '男' : '女';
                    $item->parent_name = Db::name("member")->where("member_id",$item->parent_id)->value("nickname","星木传媒");
                if($GLOBALS['admin']["role_id"]>1){
                    $item->phone = preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $item->phone);
                }

                    


                    $item->manage = Db::name("manage")->where("manage_id",$item->manage_id)->value("username"," -- ");

                    $pl = Db::name("member_tree")->field("platform_id,uid")->where("member_id",$item->member_id)->select();
                    $item_platform_uid=[];
                    $item_platform=[];
                    foreach ($pl as $key => $value) {
                       $item_platform_uid[] = $value["uid"];
                       $item_platform[] = Db::name("platform")->where("platform_id",$value["platform_id"])->value("name");
                       //$item_platform[] = $p[$value["platform_id"]];
                    }
                    $item->platform_uid=implode("/",$item_platform_uid);
                    $item->platform=implode("/",$item_platform);

                });
             // $return=[];
             // foreach ($data as $key => $value) {
             //     # code...
             // }
            return $this->success($data);
        }

        $platform=Db::name("platform")->field("name,platform_id")->select();
        $pp=[];
        foreach ($platform as $key => $value) {
            $pp[$value["platform_id"]]=$value["name"];
        }
            // $p=[];
            // foreach ($platform as $key => $value) {
            //     $p[$value["platform_id"]]=$value["name"];
            // }
        echo "<script>var platform_arr=".json_encode($pp).";</script>";    
        return $this->fetch("",[
            "platform"=>json_encode($pp),
            ]);
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

            $this->db->startTrans();

            
            if(Db::name("member")->where("nickname",$param["nickname"])->value("member_id")){
                return $this->error([], '昵称不唯一', 1);
            }
            if(Db::name("member")->where("phone",$param["phone"])->value("member_id")){
                return $this->error([], '电话不唯一', 1);
            }
            $data = $member::create($param);

            // MemberTree::create([
            //     'member_id' => $data['member_id'],
            //     'parent_id' => 0,
            //     'level'     => 0
            // ]);
            $this->db->commit();

            return $this->success([], '添加成功', 1);
        }

        return $this->fetch();
    }

      /**
     * 添加
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function data(MemberModel $member,MemberDataModel $memberData)
    {


        $param = $this->request->param();
           // [$limit, $where] = $this->buildTableParam($memberData);
             //return $this->success($where);
            $where=[];
            if(isset($param["mon"])){
                $where[]=["mon",'=',$param["mon"]];
            }else{
                $where[]=["mon",'=',date("Ym")];
            
            }
            if(isset($param["member_id"])){
                $where[]=["member_id",'=',$param["member_id"]];
                $member_id=$param["member_id"];
                $this->assign("member_id", $member_id);
            }
            if(isset($param["platform_id"]) && in_array($param["platform_id"],[1,2,3])){
                $where[]=["platform_id",'=',$param["platform_id"]];
                // $member_id=$param["member_id"];
                // $this->assign("member_id", $member_id);
            }

            //print_r($where);exit;
            $platform=Db::name("platform")->field("name,platform_id")->select();
            $p=[];
            foreach ($platform as $key => $value) {
                $p[$value["platform_id"]]=$value["name"];
            }
            $data = $memberData
                ->where($where)
                ->withoutField('update_time,delete_time')
                ->append(['nickname'])
                ->order('data_id desc')
                ->select();
            //$new_data=[];
            $zong=0;
            foreach ($data as $key => $value) {
                $value["platform"]=$p[$value["platform_id"]];
                $zong+=$value["enterprise"];
                //$new_data[]=$value;
            }
            //$data=$new_data;
            // return $this->success($data);
     

        return $this->fetch('', [
            'item' => $data,
            'zong' => $zong,
            //'member_id' => $member_id,
            //'select'=>$select
        ]);
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
        $param = $this->request->param();
         // $admin=session("admin");
         //  print_r($admin);
         //  exit;
         $admin=session("admin");
        if ($this->request->isPost()) {

          
          //print_r($admin);
          if($admin["role_id"]==5){
              return apiShow([], '您无修改权限', -1);
          }
            //print_r($param);exit;
            
            // // $member->valid($param, 'view');
            // print_r($param);
            //  die();
            if (isset($param['password']) && $param['password'] && $param['confirm_passowrd'] ) {
                if ($param['password'] != $param['confirm_passowrd']) {
                    return apiShow([], '两次密码不一致', -1);
                }
            } else {
                unset($param['password']);
            }
            unset($param['confirm_passowrd']);
            // print_r($param);
            // die();


            if($param["parent_name"] == "星木传媒"){
                $param["parent_id"]=0;
            }else{
                $param["parent_id"]=DB::name("member")->where("nickname",$param["parent_name"])->value("member_id",0);
            }

            if(Db::name("member")->where("nickname",$param["nickname"])->where("member_id","<>",$param["member_id"])->value("member_id")){
                return $this->error([], '昵称不唯一', 1);
            }
            // if(Db::name("member")->where("phone",$param["phone"])->value("member_id")){
            //     return $this->error([], '电话不唯一', 1);
            // }
              $data = $member
                ->where('member_id', $param['member_id'])
                ->withoutField('update_time,delete_time')
                ->find();
              
                if($param["parent_id"]!=$data["parent_id"]){
                   $this->set_parent_id($param['member_id'],$param["parent_id"]);
                }
            //     print_r($param);
            // die();
            unset($param["parent_name"]);
            unset($param["parent_id"]);
            unset($param["phone"]);

            $member::update($param);

            // echo "<script>alert('修改成功');window.location.href='view?member_id={$param["member_id"]}'<script>";
            // die();
            //header("location:view?member_id=".$param["member_id"]);exit;
            return $this->success([], '操作成功', 1);
        }
        if($admin["role_id"]==5){
             $select=Db::name("member")->field("member_id,nickname")->where("status",1)->where("manage_id",$admin["manage_id"])->select();
        }else{
             $select=Db::name("member")->field("member_id,nickname")->where("status",1)->select();
        }

        $data = $member
            ->where('member_id', $param['member_id'])
            ->withoutField('update_time,delete_time')
            ->find();

        $GLOBALS['admin'] =session("admin"); 
        if($GLOBALS['admin']["role_id"]>1){
            $data["phone"] = preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $data["phone"]);
        }
            
        $mon=date("Ym");
        $y=Db::name("member_data")->field("SUM(enterprise) s")->where("mon",$mon)->where("member_id",$param['member_id'])->find();
        $data["enterprise"]=$y["s"];
        if($data["parent_id"] ==0){
            $data["parent_name"]="星木传媒";
        }else{
            $data["parent_name"]=Db::name("member")->where("member_id",$data["parent_id"])->value("nickname");
        }
        
        $adminer=Db::name("manage")->where("role_id",5)->select();
        //echo json_encode($select);
        return $this->fetch('', [
            'item' => $data,
            'adminer' => $adminer,
            'select'=>$select,
            'selectjson'=>json_encode($select)
        ]);
    }

    /**
     * 审核
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check(MemberModel $member)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $member::update($post);

            return $this->success([], '操作成功', 1);
        }

        $data = $member
            ->where('member_id', $this->request->get('member_id'))
            ->field('member_id,name,sex,id_card,alipay_account,is_identity')
            ->find();
        $data['age'] = getAgeByIdCard($data['id_card'] ?: 0);

        return $this->fetch('', [
            'item' => $data
        ]);
    }

    /**
     * 开通平台
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function open(MemberModel $member)
    {
        $get=$this->request->get();
        $err="";
        $admin=session("admin");
        if ($this->request->isPost()) {

          if($admin["role_id"]==5){
              return apiShow([], '您无修改权限', -1);
          }

             $post = $this->request->post();
             $platform_id=$post["platform_id"];
             $member_id=$post["member_id"];
             $uid=$post["uid"];
             $get['member_id']=$post["member_id"];
             if($uid == ""){
                if($post["type"] =="del"){

                        $res=Db::name("member_tree")->where("member_id",$member_id)->where("platform_id",$platform_id)->find();

                        //是否有业绩 
                        $data=Db::name("member_data")->where("member_id",$res["member_id"])->where("platform_id",$res["platform_id"])->count();
                        if($data>0){
                            $err='该账号已产生业绩';
                        }else{
                            Db::name("member_tree")->where("id",$res["id"])->delete();
                        }
                    }else{
                        $err='uid为空';
                    }
                    //$platform=Db::name("platform")->select();

                   
                //echo "<script>alert('uid为空');</script>";
             }else{

                    $m = $member
                    ->field("member_id,parent_id,parent2_id")
                    ->where('member_id', $member_id)
                    ->find();
                   $is_uid=Db::name("member_tree")->where("uid",$uid)->value("id");
                   if(!$is_uid){

                        if($post["type"] =="edit"){
                                $array=[
                                    "update_time"=>time(),
                                    "uid"=>$uid
                                ];
                                Db::name("member_tree")
                                 ->where("member_id",$member_id)
                                 ->where("platform_id",$platform_id)
                                 ->update($array);
                             
                        }else{

                                     $array=[
                                            "member_id"=>$member_id,
                                            "parent_id"=>$m["parent_id"],
                                            "level"=>0,
                                            "create_time"=>time(),
                                            "update_time"=>time(),
                                            "parent2_id"=>$m["parent2_id"],
                                            "platform_id"=>$platform_id,
                                            "create_mon"=>date("Ym"),
                                            "money"=>0,
                                            "agent"=>0,
                                            "uid"=>$uid
                                     ];
                                     $vo=Db::name("member_tree")
                                     ->field("id")
                                     ->where("member_id",$member_id)
                                     ->where("platform_id",$platform_id)
                                     ->find();
                                 
                                    if(!$vo){
                                        Db::name("member_tree")
                                         ->where("member_id",$member_id)
                                         ->where("platform_id",$platform_id)
                                         ->insert($array);
                                     }
                            
                           }  

                            
                     }else{
                        $err='uid已存在';
                        //echo "<script>alert('uid已存在');</script>";
                     }

             }
            // $member::update($post);

            // return $this->success([], '操作成功', 1);
        }

        // $data = $member
        //     ->where('member_id', $this->request->get('member_id'))
        //     ->field('member_id,name,sex,id_card,alipay_account,is_identity')
        //     ->find();
        //$data['age'] = getAgeByIdCard($data['id_card'] ?: 0);

        $uid=Db::name("member_tree")->field("id,platform_id,uid,create_time,money")->where("member_id",$get['member_id'])->select();
        $u_arr=[];
        foreach ($uid as $key => $value) {
            $u_arr[$value["platform_id"]]=$value;
        }
        $platform=Db::name("platform")->field("name,platform_id")->where("is_show",1)->select();
        $p_arr=[];
        foreach ($platform as $key => $value) {
            $mon=date("Ym");
            if(isset($u_arr[$value["platform_id"]])){
                $y=Db::name("member_data")
                    ->field("SUM(enterprise) s")
                    ->where("mon",$mon)
                    ->where("member_id",$get['member_id'])
                    ->where("platform_id",$value["platform_id"])
                    ->find();
                $all_y=Db::name("member_data")
                    ->field("SUM(enterprise) s")
                    ->where("member_id",$get['member_id'])
                    ->where("platform_id",$value["platform_id"])
                    ->find();
                //$data["enterprise"]=$y["s"];

                $p_arr[$value["platform_id"]]=[
                    "name"=>$value["name"],
                    "money"=>$u_arr[$value["platform_id"]]["money"],
                    "uid"=>$u_arr[$value["platform_id"]]["uid"],
                    "create_time"=>$u_arr[$value["platform_id"]]["create_time"],
                    "enterprise"=> (float) $y["s"],
                    "all_enterprise"=> (float) $all_y["s"],
                ];
            }else{
                $p_arr[$value["platform_id"]]=[
                    "name"=>$value["name"],
                    "money"=>0,
                    "uid"=>"",
                    "create_time"=>"",
                    "enterprise"=>0,
                    "all_enterprise"=>0,
                ];
            }

        }

        $data=$p_arr;

        return $this->fetch('', [
            'item' => $data,
            "member_id"=>$get['member_id'],
            "err"=>$err
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

        $find = $member
        ->where("member_id",$param['id'])
        ->update([$param['field']=>$param['value']]);
        //return $this->success($param, '保存成功');
       //  $find->$param['field'] = $param['value'];
       // // $find->status = $param['value'];
       //  $find->save();


       //  $find = $member->find($param['id']);

       //  //return $this->success($param, '保存成功');
       //  $find->$param['field'] = $param['value'];
       // // $find->status = $param['value'];
       //  $find->save();

        return $this->success([], '保存成功');
    }




     /**
     * 构建请求参数
     * @param null $model 要查询的模型
     * @param array $excludeFields 忽略构建搜索的字段
     * @return array
     */
    protected function member_buildTableParam($model = null, array $excludeFields = []): array
    {
        $get = $this->request->get('', null, null);
        $page = isset($get['page']) && !empty($get['page']) ? $get['page'] : 1;
        $limit = isset($get['limit']) && !empty($get['limit']) ? $get['limit'] : 15;
        $filters = isset($get['filter']) && !empty($get['filter']) ? $get['filter'] : '{}';
        $ops = isset($get['op']) && !empty($get['op']) ? $get['op'] : '{}';
        // json转数组
        $filters = json_decode($filters, true);
        $ops = json_decode($ops, true);
        $where = [];
        $excludes = [];

        $model = new $model();
        // 判断是否关联查询
        $tableName = humpToLine(lcfirst($model->getName()));

        foreach ($filters as $key => $val) {
            if($key == "nickname"){

                $mem=Db::name("member")->field("member_id")->where("nickname","like","%".$val."%")->select();
                $mid_arr=[];
                foreach ($mem as $k => $v) {
                    $mid_arr[]=$v["member_id"];
                }
                $where[] = ["member_id", "in", $mid_arr];

                continue;
            }

            if($key == "manage"){

                $mem=Db::name("manage")->field("manage_id")->where("username","like","%".$val."%")->select();
                $mid_arr=[];
                foreach ($mem as $k => $v) {
                    $mid_arr[]=$v["manage_id"];
                }
                $where[] = ["manage_id", "in", $mid_arr];
                continue;
            }


            if($key == "platform"){
                $mem=Db::name("member_tree")->field("member_id")->where("platform_id",$val)->select();
                $mid_arr=[];
                foreach ($mem as $k => $v) {
                    $mid_arr[]=$v["member_id"];
                }
                $where[] = ["member_id", "in", $mid_arr];
                continue;
            }
            if($key == "platform_uid"){
                $mem=Db::name("member_tree")->where("uid",$val)->value("member_id");
                if(!$mem){
                    $mem=0;
                }
                // $mem=Db::name("member")->field("member_id")->where("nickname","like","%".$val."%")->select();
                // $mid_arr=[];
                // foreach ($mem as $k => $v) {
                //     $mid_arr[]=$v["member_id"];
                // }
                $where[] = ["member_id", "=",$mem];
                continue;
            }
            if($key == "parent_name"){
                $mem=Db::name("member")->field("member_id")->where("nickname","like","%".$val."%")->select();
                $mid_arr=[];
                foreach ($mem as $k => $v) {
                    $mid_arr[]=$v["member_id"];
                }
                if($val == "星木传媒"){
                   $mid_arr[]=0; 
                }
                $where[] = ["parent_id", "in", $mid_arr];

                continue;
            }
            if (in_array($key, $excludeFields)) {
                $excludes[$key] = $val;
                continue;
            }

            $op = isset($ops[$key]) && !empty($ops[$key]) ? $ops[$key] : '%*%';
            if ($this->relationSearch && count(explode('.', $key)) == 1) {
                $key = "{$tableName}.{$key}";
            }
            switch (strtolower($op)) {
                case '=':
                    $where[] = [$key, '=', $val];
                    break;
                case '%*%':
                    $where[] = [$key, 'LIKE', "%{$val}%"];
                    break;
                case '*%':
                    $where[] = [$key, 'LIKE', "{$val}%"];
                    break;
                case '%*':
                    $where[] = [$key, 'LIKE', "%{$val}"];
                    break;
                case 'range':
                    [$beginTime, $endTime] = explode(' - ', $val);
                    $where[] = [$key, '>=', strtotime($beginTime)];
                    $where[] = [$key, '<=', strtotime($endTime)];
                    break;
                default:
                    $where[] = [$key, $op, "%{$val}"];
            }
        }
        return [$limit, $where, $excludes, $page];
    }



    public function set_parent_id($member_id,$parent_id){

       
        if($parent_id!=0){
            
            $parent2_id=DB::name("member")->where("member_id",$parent_id)->value("parent_id");
        }else{
            $parent2_id=0;
            //DB::name("member")->where("member_id",$member_id)->update(["parent_id"=>0,"parent2_id"=>0]);
        }
        //修改
        DB::name("member")->where("member_id",$member_id)->update(["parent_id"=>$parent_id,"parent2_id"=>$parent2_id]);
        DB::name("member_tree")->where("member_id",$member_id)->update(["parent_id"=>$parent_id,"parent2_id"=>$parent2_id]);
        //修改下级
        DB::name("member")->where("parent_id",$member_id)->update(["parent_id"=>$member_id,"parent2_id"=>$parent_id]);
        DB::name("member_tree")->where("parent_id",$member_id)->update(["parent_id"=>$member_id,"parent2_id"=>$parent_id]);

    }






     /**
     * 开通平台
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function team(MemberModel $member)
    {
        $get=$this->request->get();
        $back=0;
        if(isset($get["back"]) && $get["back"] ==1){
            $back=1;
        }
        $err="";
        $nickname=$member->where("member_id",$get["member_id"])->value("nickname");
        $mon=date("Ym");
        $son=$member
        ->where("parent_id",$get["member_id"])
        ->select();
        $s=[];
        $admin=session("admin");
     
        foreach ($son as $k => $item) {
            $pl = Db::name("member_tree")->field("platform_id,uid")->where("member_id",$item["member_id"])->select();
            $item_platform_uid=[];
            $item_platform=[];
            foreach ($pl as $key => $value) {
                $item_platform_uid[] = $value["uid"];
                $item_platform[] = Db::name("platform")->where("platform_id",$value["platform_id"])->value("name");
            //$item_platform[] = $p[$value["platform_id"]];
            }

            if($admin["role_id"]>1){
              $item["phone"]=preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $item->phone);
            }
            $item["platform_uid"]=implode("<br>",$item_platform_uid);
            $item["platform"]=implode("<br>",$item_platform);

            $yj=Db::name("member_data")->field("SUM(enterprise) s")->where("member_id",$item["member_id"])->where("mon",$mon)->find();
            $item["yj"]=(float)$yj["s"];
            $s[]=$item;
        }



        return $this->fetch("",[
            "son"=>$s,
            "nickname"=>$nickname,
            "back"=>$back

        ]);
    }
    public function statistics()
    {
        $get=$this->request->get();
        $platform=Db::name("platform")->select();
        if(!isset($get["platform_id"])){
            $get["platform_id"]=1;
        }

        $member_id=$get["member_id"];
        $platform_id=$get["platform_id"];
        $mon_arr=[
            date("Ym"),
            date("Ym",strtotime("-1 month")),
            date("Ym",strtotime("-2 month")),
            date("Ym",strtotime("-3 month")),
            date("Ym",strtotime("-4 month")),
            date("Ym",strtotime("-5 month")),
            date("Ym",strtotime("-6 month")),
            date("Ym",strtotime("-7 month")),
            date("Ym",strtotime("-8 month")),
            date("Ym",strtotime("-9 month")),
            date("Ym",strtotime("-10 month")),
            date("Ym",strtotime("-11 month")),
        ];
       
        $r=[];
        foreach ($mon_arr as $key => $value) {
            $r[]=$this->sta_mon($member_id,$platform_id,$value);
        }
    
        return $this->fetch("",[
            "item"=>$r,
            "member_id"=>$member_id,
            "platform_id"=>$platform_id,
            "platform"=>$platform
        ]);

    }
    public function sta_mon($member_id,$platform_id,$mon)
    {
        $ssjs=true;
        $get=[
            "member_id"=>$member_id,
            "mon"=>$mon
        ];
        $member_id=$get["member_id"];
        //$platform_id=$get["platform_id"];

        $user=Db::name("member")->where("member_id",$get["member_id"])->find();
        $yeji=Db::name("member_data")
            ->field("SUM(enterprise) s")
            ->where("member_id",$get["member_id"])
            ->where("platform_id",$platform_id)
            ->where("mon",$mon)
            ->find();
        //$mon=date("Ym");

        $son_man=Db::name("member_tree")
            ->field("member_id,create_mon")
            ->where("parent_id",$get["member_id"])
            ->where("create_mon","<=",$mon)
            ->where("platform_id",$platform_id)
            ->select();
       // print_r($son_man);

        $son_arr=[];$new_arr=[];
        foreach ($son_man as $key => $value) {
           $son_arr[]=$value["member_id"];
           if($value["create_mon"] == $mon){
             $new_arr[]=$value["member_id"];
           }
        }

        $son_yeji=Db::name("member_data")
            ->field("SUM(enterprise) s")
            ->whereIn("member_id",$son_arr)
            ->where("platform_id",$platform_id)
            ->where("mon",$mon)
            ->find();

        $new_youxiao=Db::name("member_data")
            ->field("SUM(enterprise) s,member_id")
            ->whereIn("member_id",$new_arr)
            ->where("platform_id",$platform_id)
            ->group("member_id")
            ->select();
        // $new_youxiao=Db::name("member_data")
        //     ->field("SUM(enterprise) s,member_id")
        //     ->whereIn("member_id",$new_arr)
        //     ->where("platform_id",$platform_id)
        //     ->group("member_id")
        //     ->select();
         $new_num=0;
        // $new_yx=[];
        // foreach ($new_youxiao as $key => $value) {
        //     if($value["s"]>=50){
        //         $new_num++;//有效新增人数
        //         $new_yx[]=$value["member_id"];
        //     }
        // }
        // 
        // 
        foreach ($new_arr as $key => $value) {
            $r=Db::name("member_data")
            ->field("SUM(enterprise) s")
            ->where("member_id",$value)
            ->where("platform_id",$platform_id)
            ->where("mon",$mon)
            ->find();
            if($r["s"]>=50){
                $new_num++;//有效新增人数
                $new_yx[]=$value;
            }
        }
        //echo 1;exit;
        // print_r($new_yx);
        // exit;
        if( $ssjs || date("Ym")== $mon){
            $dian=$this->getdian($son_yeji["s"],$new_num);
            $son_yongjin=round($dian*$son_yeji["s"]/100,2);
        }

        $son2_man=Db::name("member_tree")
        ->field("member_id")
        ->where("parent2_id",$get["member_id"])
        ->where("create_mon","<=",$mon)
        ->where("platform_id",$platform_id)
        ->select();
        $son2_arr=[];$s2num=0; $s2_all_num1=0;
        foreach ($son2_man as $key => $value) {

                $son_parent_id=Db::name("member")->where("member_id",$value["member_id"])->value("parent_id");
                $is_agency=Db::name("member")->where("member_id",$son_parent_id)->value("is_agency");
                if($is_agency == 1  && in_array($son_parent_id,$son_arr) ){ //上级是代理，才计入人数
                    $s2_all_num1++;
                    //本月必须有业绩才算入人数
                    $s2_man_is_y=Db::name("member_data")
                    ->field("member_id")
                    ->where("member_id",$value["member_id"])
                    ->where("enterprise",">",0)
                    ->where("platform_id",$platform_id)
                    ->where("mon",$mon)
                    ->limit(1)
                    ->find();

                    if($s2_man_is_y){
                        $son2_arr[]=$value["member_id"];
                        $s2num++;
                    }
                }
           // $s2_man_is_y=Db::name("member_data")
           //      ->field("member_id")
           //      ->where("member_id",$value["member_id"])
           //      ->where("enterprise",">",0)
           //      ->where("platform_id",$platform_id)
           //      ->limit(1)
           //      ->find();
           // if($s2_man_is_y["s"]){
           //      $s2num++;
           // }




        }
        $son2_yeji=Db::name("member_data")
        ->field("SUM(enterprise) s")
        ->whereIn("member_id",$son2_arr)
        ->where("mon",$mon)
        ->where("platform_id",$platform_id)
        ->find()??0;
        //var_dump($son2_yeji);
        // $dian2=$this->getdian2($son2_yeji["s"],count($son2_man));
        // $son2_yongjin=round($dian2*$son2_yeji["s"]/100,2);
        
        if($ssjs || date("Ym")==$mon){
             $dian2=$this->getdian2($son2_yeji["s"],$s2num);
             //$dian2=$this->getdian2($son2_yeji["s"],count($son2_man));
             $son2_yongjin=round($dian2*$son2_yeji["s"]/100,2);
             $yongjin=$son2_yongjin+$son_yongjin;
        }else{
            $yongjin=Db::name("commission")
            ->where("member_id",$member_id)
            ->where("platform_id",$platform_id)
            ->where("mon",$mon)
            ->value("money");
            //->value("money");
        }
        $yongjin_true=Db::name("commission")
            ->where("member_id",$member_id)
            ->where("platform_id",$platform_id)
            ->where("mon",$mon)
            ->value("money")??0;
        if(!$yongjin){
            $yongjin=0;
        }
        // echo ' (float) $new_num["s"]';
        // print_r( (int) $new_num["s"]);exit;
        $return=[
            "mon"=>$mon,
            "yongjin"=>$yongjin,
            "yeji"=>(float) $yeji["s"],
            //"son_yeji"=>(float) $dian,
            "son_yeji"=>(float) $son_yeji["s"],
            "son2_yeji"=>(float) $son2_yeji["s"],
            "son_num"=>(int) count($son_man),
            "son2_num"=>$s2_all_num1,
            //"son2_num"=>(int) count($son2_man),
            "new_all_num"=>(int) count($new_arr),
            "new_num"=>(int) $new_num,
            "all_num"=> (int) count($son_man)+(int) count($son2_man),
            "yongjin_true"=>$yongjin_true
        ];



        // $v->assign("son_num", (int) count($son_man));
        // $v->assign("son2_num", (int) count($son2_man));
        // $v->assign("new_all_num", (int) count($new_arr));
        // $v->assign("new_num", (float) $new_num);
        // $v->assign("all_num", (int) count($son_man)+(int) count($son2_man));



        return $return;
    }



  /**
     * 计算佣金点位
     * @return mixed
     */
    public function getdian($yeji,$new)
    {

        if(empty($this->dian_arr)){
            $res=Db::name("dian")->where("type",1)->order("dian desc")->select();
            $this->dian_arr=$res;
        }
           $dian=0;

           foreach ($this->dian_arr as $key => $value) {
              if($yeji>$value["money"]){
                    $dian=$value["dian"];
                    break;
               }
           }

        if(empty($this->dian2_arr)){
            $res=Db::name("dian")->where("type",2)->order("dian desc")->select();
            $this->dian2_arr=$res;
        }

        foreach ($this->dian2_arr as $key => $value) {
           if($new>=$value["num"]){
                $dian+=$value["dian"];
                break;
           }
        }

           return $dian;



    }
        /**
     * 计算2级佣金点位
     * @return mixed
     */
    public function getdian2($yeji,$num)
    {


        if(empty($this->dian3_arr)){
            $res=Db::name("dian")->where("type",3)->order("dian desc")->select();
            $this->dian3_arr=$res;
        }
        $dian=0;
        foreach ($this->dian3_arr as $key => $value) {
           if($yeji>$value["money"] && $num>=$value["num"]){
                $dian=$value["dian"];
                break;
           }
       }
       return $dian;

    }



    public function delete_tree()
    {
        $param=$this->request->param();
        $member_id=$param["member_id"];
        $platform_id=$param["platform_id"];
        $admin=session("admin");
      if($admin["role_id"]==5){
          return apiShow([], '您无修改权限', -1);
      }
        $res=Db::name("member_tree")->where("member_id",$member_id)->where("platform_id",$platform_id)->find();

        //是否有业绩 
        $data=Db::name("member_data")->where("member_id",$res["member_id"])->where("platform_id",$res["platform_id"])->count();
        if($data>0){
            $this->error([], '该账号已产生业绩');
        }else{
            Db::name("member_tree")->where("id",$tree_id)->delete();
        }
        //$platform=Db::name("platform")->select();

        $this->success([], '删除成功');

    }


    public function mon(){
        $param=$this->request->param();

        return $this->fetch();


    }
    public function platform_createtime(){

            $param=$this->request->param();


            // $GLOBALS['admin'] =session("admin");
            // if($GLOBALS['admin']["role_id"] != 1){
            //     //验证权限
            //     $role=Db::name("manage_role")->where("id",$GLOBALS['admin']["role_id"])->value("rules");
            //     $this_id=Db::name("menu")->where("href","like","/admin/member/platform_createtime%")->value("role_id");
            //     $role_arr=explode(",",$role);
            //     if(!in_array($this_id,$role_arr)){
            //         $this->error([], '权限不足');
            //     }

            // }
            $u=DB::name("member_tree")
            ->where("uid",$param["uid"])
            ->where("member_id",$param["member_id"])
            ->where("platform_id",$param["platform_id"])
            ->update(["create_time"=>strtotime($param["value"])]);
            if($u){
                $this->success([], '修改成功');
            }else{
                $this->error([], '修改失败');
            }
            
    }



    /**
     * 余额
     * @param MemberModel $member
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function money(MemberModel $member)
    {
        
        $get=$this->request->get();
        $uid=Db::name("member_tree")->field("id,platform_id,uid,create_time,money")->where("member_id",$get['member_id'])->select();
        $u_arr=[];

        $platform=Db::name("platform")->field("name,platform_id")->select();
        $pp=[];
        foreach ($platform as $key => $value) {
            $pp[$value["platform_id"]]=$value["name"];
        }


        $where=[["member_id","=",$get["member_id"]],["create_time",">",strtotime('-6 month')]];
        if(isset($get["platform_id"]) && $get["platform_id"] !=""){
            $where[]=["platform_id","=",$get["platform_id"]];
            // $member_withdraw=Db::name("member_withdraw")
            // ->field("create_time,money,platform_id,member_id")
            // ->where("member_id",$get["member_id"])
            // ->where("platform_id",$get["platform_id"])
            // ->select();
            // $commission=Db::name("commission")
            // ->field("create_time,money,platform_id,member_id")
            // ->where("member_id",$get["member_id"])
            // ->where("platform_id",$get["platform_id"])
            // ->select();
        }

        $member_withdraw=Db::name("member_withdraw")
            ->field("create_time,money,platform_id,member_id,1 as t,status")
            // ->where("status",1)
            ->where($where)
            //->where("platform_id",$get["platform_id"])
            ->select()->toArray();

        $commission=Db::name("commission")
            ->field("create_time,money,platform_id,member_id,0 as t,1 as status")
            ->where($where)
            //->where("platform_id",$get["platform_id"])
            ->select()->toArray();

        $arr_all=array_merge($member_withdraw,$commission);

        $new_arr=$this->my_array_multisort($arr_all,"create_time");
        return $this->fetch('',[
            "item"=>$new_arr,
            'pp'=>$pp,
            'member_id'=>$get['member_id'],
            'platform_id'=>$get['platform_id']??0,
            ]);

        // $admin=session("admin");
        // if ($this->request->isPost()) {

        //   if($admin["role_id"]==5){
        //       return apiShow([], '您无修改权限', -1);
        //   }

        //      $post = $this->request->post();
             
        // }
        // return $this->fetch();
    }

    public function my_array_multisort($data,$sort_order_field,$sort_order=SORT_DESC){
        $key_arrays=[];
        foreach($data as $val){
           $key_arrays[]=$val[$sort_order_field];
        }
        //dump($data);exit;
        array_multisort($key_arrays,$sort_order,SORT_NUMERIC,$data);
        return $data;
    }



}