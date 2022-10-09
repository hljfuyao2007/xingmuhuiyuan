<?php

namespace app\admin\controller;

use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use app\common\service\AuthCore;
use think\response\Json;
use think\facade\Db;

class Index extends AdminController
{
    /**
     * 后台布局
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取个人菜单
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function init(): Json
    {
        return json(AuthCore::getInstance()->getMenu());
    }

    /**
     * @return mixed
     */
    public function welcome()
    {   
        $admin=session("admin");
        if($admin["role_id"]==5){
          $member=Db::name("member")->field("member_id")->where("manage_id",$admin["manage_id"])->select();
        }else{
           $member=Db::name("member")->field("member_id")->select(); 
        }
        //$member=Db::name("member")->field("member_id")->where("manage_id",$admin["manage_id"])->select();
        $m=[];
        foreach ($member as  $vvv) {
            $m[]=$vvv["member_id"];
        }
        $p=Db::name("platform")->select();
        $platform=[];$all=[];
        $param = $this->request->param();
        if(isset($param["start_date"]) && isset($param["end_date"]) ){
            if(strtotime($param["start_date"])<600000000){
                unset($param["start_date"]);
                unset($param["end_date"]);
                $this->assign("date",[0]);
            }else{
                $param["start_date"]=date('Y-m-d',strtotime($param["start_date"]));
                $param["end_date"]=date('Y-m-d',strtotime($param["end_date"]));
                $this->assign("date",[1,$param["start_date"],$param["end_date"]]);
            }

        } else{
            $this->assign("date",[0]);
        }
            

        $yestoday=[strtotime(date("Y-m-d",strtotime("-1 day"))." 00:00:00"),strtotime(date("Y-m-d",strtotime("-1 day"))." 24:00:00")];
        $mon_start=strtotime(date("Y-m",strtotime("-1 month"))."-1 00:00:00");
        $all["yeji"]=0;
        foreach ($p as $key => $value) {

                //平台的总业绩，
                $yeji_zong=Db::name("member_data")
                ->where("platform_id",$value["platform_id"])
                ->where("delete_time","=",null);
                
            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                 $yeji_zong=$yeji_zong
                 ->where("date","between",[$param["start_date"],$param["end_date"]]);
                 //->where("d.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }else{
                 // $yeji_zong=$yeji_zong
                 // ->where("m.create_time",">",$mon_start);
                 //->where("d.create_time",">",$mon_start)
            }
                $yeji_zong=$yeji_zong
                ->where("member_id","in",$m)
                ->sum("enterprise");

                // 昨天新增加的绑定的ABC三个平台的人数
                $yestoday_new_tree=Db::name("member_tree")
                ->where("platform_id",$value["platform_id"]);
                //->where("create_time","between",$yestoday)

                if(isset($param["start_date"]) && isset($param["end_date"]) ){
                     $yestoday_new_tree=$yestoday_new_tree
                     ->where("create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
                }else{
                     $yestoday_new_tree=$yestoday_new_tree
                     ->where("create_time","between",$yestoday);
                         //->where("d.create_time",">",$mon_start)
                }
                $yestoday_new_tree=$yestoday_new_tree
                ->where("member_id","in",$m)
                ->where("delete_time","=",null)
                ->count();
                $all["yeji"]+=$yeji_zong;

                $good_member=Db::name("member_tree")
                ->alias("m")
                ->field("m.*,SUM(d.enterprise) e")
                ->join("member_data d","m.member_id=d.member_id","left");

                if(isset($param["start_date"]) && isset($param["end_date"]) ){
                     $good_member=$good_member
                     ->where("m.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")])
                     ->where("d.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
                }else{
                     $good_member=$good_member
                     ->where("m.create_time",">",$mon_start);
                     //->where("d.create_time",">",$mon_start)
                }
               // ->where("m.create_time",">",$mon_start)

                $good_member=$good_member
                ->where("d.platform_id",$value["platform_id"])
                ->where("m.platform_id",$value["platform_id"])
                ->where("m.member_id","in",$m)
                ->group("m.member_id")
                ->select();

                //print_r($good_member);exit;
                $g=0;
                foreach ($good_member as $k => $v) {
                    if($v["e"]>=50){
                        $g++;
                    }
                }

                $zong_num=Db::name("member_tree")
                ->where("platform_id",$value["platform_id"])
                ->where("delete_time","=",null);

                if(isset($param["start_date"]) && isset($param["end_date"]) ){
                     $zong_num=$zong_num
                     ->where("create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
                }
                $zong_num=$zong_num
                ->where("member_id","in",$m)
                ->count();
                //$yestoday_new_tree=Db::name("member_tree")->where("platform_id",$value["platform_id"])->where("create_time","between",$yestoday)->count();
                $platform[]=[
                        "platform_id"=>$value["platform_id"],
                        "name"=>$value["name"],
                        "yestoday_new_tree"=>$yestoday_new_tree,
                        "yeji_zong"=>$yeji_zong,
                        "good_member"=>$g,
                        "zong_num"=>$zong_num
                ];
        }


        $all["yestoday_member"]=Db::name("member");
        if(isset($param["start_date"]) && isset($param["end_date"]) ){
             $all["yestoday_member"]=$all["yestoday_member"]->where("create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
        }else{
             $all["yestoday_member"]=$all["yestoday_member"]->where("create_time","between",$yestoday);
        }
       
        $all["yestoday_member"]=$all["yestoday_member"]->where("delete_time","=",null)
        ->where("member_id","in",$m)
        ->count();


        

         $all["zong_num"]=Db::name("member")
         ->where("member_id","in",$m);
        if(isset($param["start_date"]) && isset($param["end_date"]) ){
            $all["zong_num"]=$all["zong_num"]->where("create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
        }
         $all["zong_num"]=$all["zong_num"]->where("delete_time","=",null)
         ->count();   
         $good_member_zong=Db::name("member_tree")
                ->alias("m")
                ->field("m.*,SUM(d.enterprise) e")
                ->join("member_data d","m.member_id=d.member_id","left");

            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                 $good_member_zong=$good_member_zong
                 ->where("m.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")])
                 ->where("d.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }else{
                 $good_member_zong=$good_member_zong
                 ->where("m.create_time",">",$mon_start);
                 //->where("d.create_time",">",$mon_start)
            }

               // ->where("m.create_time",">",$mon_start)

                $good_member_zong=$good_member_zong->where("m.member_id","in",$m)
                // ->where("d.platform_id",$value["platform_id"])
                // ->where("m.platform_id",$value["platform_id"])
                ->group("m.member_id")
                ->select();
                $all["good_member"]=0;
                foreach ($good_member_zong as $k => $v) {
                    if($v["e"]>=50){
                        $all["good_member"]++;
                    }
                }

        $this->assign("p",$platform);

        $this->assign("all",$all);

        //halt(getRouteList());
        return $this->fetch();
    }

    /**
     * 清除缓存
     * @return array|false|mixed|Json|\think\response\View
     */
    public function clear()
    {
        TriggerService::updateMenu($this->admin_id);
        TriggerService::updateAuth($this->admin_id);
        return $this->success([], '清除成功', 1);
    }



    /**
     * @return mixed
     */
    public function show_list()
    {
        $admin=session("admin");
        $param = $this->request->param();
        if(!isset($param["platform_id"])){
            $param["platform_id"]=0;
        }
        if(!isset($param["member_type"])){
            $param["member_type"]="good_member";
        }
        $this->assign("member_type",$param["member_type"]);
        if($admin["role_id"]==5){
            $member=Db::name("member")->field("member_id")->where("manage_id",$admin["manage_id"])->select();
        }else{
            $member=Db::name("member")->field("member_id")->select();
        }
        //$member=Db::name("member")->field("member_id")->where("manage_id",$admin["manage_id"])->select();
        $m=[];
        foreach ($member as  $vvv) {
            $m[]=$vvv["member_id"];
        }
        $p=Db::name("platform")->select();
        $platform=[];$all=[];
        $param = $this->request->param();
        if(isset($param["start_date"]) && isset($param["end_date"]) ){
            if(strtotime($param["start_date"])<600000000){
                unset($param["start_date"]);
                unset($param["end_date"]);
                $this->assign("date",[0]);
            }else{
                $param["start_date"]=date('Y-m-d',strtotime($param["start_date"]));
                $param["end_date"]=date('Y-m-d',strtotime($param["end_date"]));
                $this->assign("date",[1,$param["start_date"],$param["end_date"]]);
            }

        } else{
            $this->assign("date",[0]);
        }


        $yestoday=[strtotime(date("Y-m-d",strtotime("-1 day"))." 00:00:00"),strtotime(date("Y-m-d",strtotime("-1 day"))." 24:00:00")];
        $mon_start=strtotime(date("Y-m",strtotime("-1 month"))."-1 00:00:00");
        $all["yeji"]=0;
        foreach ($p as $key => $value) {
            $platform[]=[
                    "platform_id"=>$value["platform_id"],
                    "name"=>$value["name"],
                ];
            if( $param["platform_id"]!= $value["platform_id"]){
                // $param["platform_id"] !=0 &&
                continue;
            }

            //平台的总业绩，
            $yeji_zong=Db::name("member_data")
                ->where("platform_id",$value["platform_id"])
                ->where("delete_time","=",null);

            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                $yeji_zong=$yeji_zong
                    ->where("date","between",[$param["start_date"],$param["end_date"]]);
                //->where("d.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }else{
                // $yeji_zong=$yeji_zong
                // ->where("m.create_time",">",$mon_start);
                //->where("d.create_time",">",$mon_start)
            }
            $yeji_zong=$yeji_zong
                ->where("member_id","in",$m)
                ->sum("enterprise");

            // 昨天新增加的绑定的ABC三个平台的人数
            $yestoday_new_tree=Db::name("member_tree")->alias("t")
                ->field("t.*,member.nickname,member.phone,member.is_agency")
                ->join("member","member.member_id=t.member_id")
                ->where("t.platform_id",$value["platform_id"]);
            //->where("create_time","between",$yestoday)

            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                $yestoday_new_tree=$yestoday_new_tree
                    ->where("t.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }else{
                $yestoday_new_tree=$yestoday_new_tree
                    ->where("t.create_time","between",$yestoday);
                //->where("d.create_time",">",$mon_start)
            }
            $yestoday_new_tree=$yestoday_new_tree
                ->where("t.member_id","in",$m)
                ->where("t.delete_time","=",null)
                ->select();


            $all["yeji"]+=$yeji_zong;

            $good_member=Db::name("member_tree")
                ->alias("m")
                ->join("member","member.member_id=m.member_id")
                ->field("m.*,SUM(d.enterprise) e,member.nickname,member.phone,member.is_agency")
                ->join("member_data d","m.member_id=d.member_id","left");

            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                $good_member=$good_member
                    ->where("m.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")])
                    ->where("d.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }else{
                $good_member=$good_member
                    ->where("m.create_time",">",$mon_start);
                //->where("d.create_time",">",$mon_start)
            }
            // ->where("m.create_time",">",$mon_start)

            $good_member=$good_member
                ->where("d.platform_id",$value["platform_id"])
                ->where("m.platform_id",$value["platform_id"])
                ->where("m.member_id","in",$m)
                ->group("m.member_id")
                ->select();

            //print_r($good_member);exit;
            $g=0;
            $good_member_list=[];
            foreach ($good_member as $k => $v) {
                if($v["e"]>=50){
                    $g++;
                    $good_member_list[]=$v;
                }
            }

            $zong_num=Db::name("member_tree")->alias("t")
                ->join("member","member.member_id=t.member_id")
                ->field("t.*,member.nickname,member.phone,member.is_agency")
                ->where("t.platform_id",$value["platform_id"])
                ->where("t.delete_time","=",null);

            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                $zong_num=$zong_num
                    ->where("t.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }
            $zong_num=$zong_num
                ->where("t.member_id","in",$m)
                ->select();
            if($param["platform_id"] == $value["platform_id"] && $param["member_type"] == "all_member"){
                $this->assign("good_member_list",$zong_num);
            }
            if($param["platform_id"] == $value["platform_id"] && $param["member_type"] == "good_member"){
                $this->assign("good_member_list",$good_member_list);
            }

            if($param["platform_id"] == $value["platform_id"] && $param["member_type"] == "yestoday_member"){
                $this->assign("good_member_list",$yestoday_new_tree);
            }
            $yestoday_new_tree=count($yestoday_new_tree);
            $zong_num=count($zong_num);
            //$yestoday_new_tree=Db::name("member_tree")->where("platform_id",$value["platform_id"])->where("create_time","between",$yestoday)->count();
            // $platform[]=[
            //     "platform_id"=>$value["platform_id"],
            //     "name"=>$value["name"],
            //     "yestoday_new_tree"=>$yestoday_new_tree,
            //     "yeji_zong"=>$yeji_zong,
            //     "good_member"=>$g,
            //     "zong_num"=>$zong_num,
            //     "good_member_list"=>$good_member_list
            // ];
        }


        if($param["platform_id"] == 0 && $param["member_type"] == "yestoday_member"){

            $all["yestoday_member"]=Db::name("member");
            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                $all["yestoday_member"]=$all["yestoday_member"]->where("create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }else{
                $all["yestoday_member"]=$all["yestoday_member"]->where("create_time","between",$yestoday);
            }

            $all["yestoday_member"]=$all["yestoday_member"]->where("delete_time","=",null)
                ->where("member_id","in",$m)
                ->select();
        
            $this->assign("good_member_list",$all["yestoday_member"]);
            $all["yestoday_member"]->count($all["yestoday_member"]);
        }
        
        if($param["platform_id"] == 0 && $param["member_type"] == "all_member"){
            
                $all["zong_num"]=Db::name("member")
                    ->where("member_id","in",$m);
                if(isset($param["start_date"]) && isset($param["end_date"]) ){
                    $all["zong_num"]=$all["zong_num"]->where("create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
                }

                $all["zong_num"]=$all["zong_num"]->where("delete_time","=",null)
                ->select();
            
                $this->assign("good_member_list",$all["zong_num"]);

                $all["zong_num"]=count($all["zong_num"]);

        }
        
        if($param["platform_id"] == 0 && $param["member_type"] == "good_member"){



            $good_member_zong=Db::name("member_tree")
                ->alias("m")
                ->field("m.*,SUM(d.enterprise) e,member.nickname,member.phone,member.is_agency")
                ->join("member_data d","m.member_id=d.member_id","left")
                ->join("member","member.member_id=m.member_id","left");

            if(isset($param["start_date"]) && isset($param["end_date"]) ){
                $good_member_zong=$good_member_zong
                    ->where("m.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")])
                    ->where("d.create_time","between",[strtotime($param["start_date"]." 00:00:00"),strtotime($param["end_date"]." 00:00:00")]);
            }else{
                $good_member_zong=$good_member_zong
                    ->where("m.create_time",">",$mon_start);
                //->where("d.create_time",">",$mon_start)
            }

            // ->where("m.create_time",">",$mon_start)

            $good_member_zong=$good_member_zong->where("m.member_id","in",$m)
                // ->where("d.platform_id",$value["platform_id"])
                // ->where("m.platform_id",$value["platform_id"])
                ->group("m.member_id")
                ->select();
            $good_member_zong_list=[];
            $all["good_member"]=0;
            foreach ($good_member_zong as $k => $v) {
                if($v["e"]>=50){
                    $all["good_member"]++;
                    $good_member_zong_list[]=$v;
                }
            }

    
        $this->assign("good_member_list",$good_member_zong_list);
    }


    $this->assign("p",$platform);
    // $this->assign("all",$all);

    // dump($good_member_zong_list);
    // exit;
        //halt(getRouteList());
        return $this->fetch();
    }
}
