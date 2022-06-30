<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022/4/14
 * Time: 9:49
 * Description:
 */

namespace app\mobile\controller;

use app\common\controller\MobileController;

use think\session;
use think\facade\Db;
use think\facade\View;
use think\Jump;
use think\request;


class Index extends MobileController
{
    public $user;
    public $dian_arr=[];
    public $dian2_arr=[];
    public $dian3_arr=[];
    public function __construct(){

        $action = request()->action();

        
        if($action!="login" && $action!="register"){
            $user=session("user");
            if(request()->isPost()){
                $param=request()->param();
                if($param["type"] == "login"){
                    $user=Db::name("member")->where("member_id",$param["mid"])->find();
                    $user["show_id"]=sprintf("%06d",$user['member_id']);
                    session("user",$user);
                }
            }
            if(!session("user")){
                header("Location:login");
            }
            $user=Db::name("member")->where("member_id",$user["member_id"])->find();
            $user["show_id"]=sprintf("%06d",$user['member_id']);
            session("user",$user);
            $this->user=$user;

        }
         
        
       

     
        
       // // print_r($this->user);exit;
       //  if(!$user || empty($user)){
       //      $this->login();
       //      $user=session("user");
       //  }

       
       //  $this->user=$user;


    }
     /**
     * 测试用的假登陆(选择平台)
     * @return mixed
     */
    public function login()
    {
         return View::fetch("v/login");
    }

    /**
     * 测试用的假登陆(选择平台)
     * @return mixed
     */
    public function wx_bd()
    {

        if(!request()->isPost()){
            header("location:login");
        }
        $param=request()->param();
        $v=View::assign("wx",$param);

         return View::fetch("v/wx_bd");
    }
     /**
     * 退出登录
     * @return mixed
     */
    public function logout()
    {
        session('user','');
        header("Location:login");
        //$this->redirect('login');
        //$user=Db::name("member")->where("member_id",1)->find();
        
       // $user=Db::query("select * from tpl_member where member_id=1")->find();

        //session("user",$user);
       
        //$this->redirect('Index/index');

        //return View::fetch("v/login");
    }

    /**
     * 测试用的注册页
     * @return mixed
     */
    public function register()
    {

        //$user=Db::name("member")->where("member_id",1)->find();
        
       // $user=Db::query("select * from tpl_member where member_id=1")->find();

        //session("user",$user);
       
        //$this->redirect('Index/index');
        //
        //$user=session("user");
        if(request()->isPost()){
        //var_dump(request()->param());
            //session("user",request()->param());
            $param=request()->param();

            if(isset($param["type"]) && $param["type"] == "wx_register"){
                $v=View::assign("wx",$param);
            }else{
                $v=View::assign("wx",1);
            }


        }else{
            $v=View::assign("wx",1);
        }
        $param=request()->param();
        if(isset($param["invite_code"])){
            View::assign("invite_code",$param["invite_code"]);
        }else{
            View::assign("invite_code",0);
        }
        // if(!session("user")){
        //     header("Location:login");
        // }else{
        //     $this->user=session("user");
        // }

         return View::fetch("v/register");
    }
    /**
     * 首页(选择平台)
     * @return mixed
     */
    public function index()
    {
        //var_dump($this->user);
       // $user=session("user");
        //print_r($this->user);
        
        
        $this->user["show_id"]=sprintf("%06d",$this->user['member_id']);

        $v=View::assign("user",$this->user);
        $invite_code=Db::name("member")->where("member_id",$this->user["member_id"])->value("nickname");
        $url=$_SERVER["SERVER_NAME"]."/mobile/register?invite_code=".$invite_code;
        $v=$v->assign("url",$url);
        $p=Db::name("platform")->select();
        $platform=[];
         $trees=Db::name("member_tree")
            ->where("member_id",$this->user["member_id"])
           // ->where("platform_id",$platform_id)
            ->select();
        foreach ($p as $key => $value) {
            $value["open"]=0;
            //$platform[$value["platform_id"]]=$value;
            foreach ($trees as $k => $v) {
                if($v["platform_id"] == $value["platform_id"]){
                    $value["open"]=1;
                }
                //$platform[$value["platform_id"]]["open"]=1;
            }
            $platform[]=$value;
        }

       
        //$platform=[1=>0,2=>0,3=>0];
      

    // print_r($platform);
        $v=View::assign("p",$platform);


        
        // die;
        // $this->assign(["user"=>$this->user]);
        //   echo 0;
        //var_dump($this->user);
        return $v->fetch("v/platform");
      
    }
    public function txrz()
    {
       // $user=session("user");
        //print_r($this->user);
        $v=View::assign("user",$this->user);
        // $this->assign(["user"=>$this->user]);
        //   echo 0;
        return $v->fetch("v/txrz");
    }
    public function dlxz()
    {
       // $user=session("user");
        //print_r($this->user);
        $user=Db::name("member")->where("member_id",$this->user["member_id"])->find();
        $user["show_id"]=sprintf("%06d",$user['member_id']);
        session("user",$user);
        View::assign("user",$user);
        $text=Db::name("system_config")->where("name","agency_notice")->value("value");
        View::assign("text",$text);
        // $this->assign(["user"=>$this->user]);
        //   echo 0;
        return View::fetch("v/dlxz");
    }


    
    public function personal()
    {
       // $user=session("user");

        //更新会员信息
        $user_new=Db::name("member")->where("member_id",$this->user['member_id'])->find();
        $user=session("user",$user_new);
        //print_r($this->user);
        $user_new["show_id"]=sprintf("%06d",$this->user['member_id']);
        $v=View::assign("user",$user_new);
        // $this->assign(["user"=>$this->user]);
        //   echo 0;
        //   
        if($user_new['is_identity'] == 2){
            $age=getAgeByIdCard($user_new["id_card"]);
            $v=$v->assign("age",$age);
        }
       
        return $v->fetch("v/personal");
    }


    /**
     * 首页
     * @return mixed
     */
    public function home()
    {   
        $get=request()->param();

        $date=Db::name("system_config")->where("name","user_month")->value("value");
        $mon=date("Ym",strtotime($date."-01 00:00:00"));
        //print_r($get);
        if(isset($get["platform_id"])){
            session("platform_id",$get["platform_id"]);
        }else{
            session("platform_id",1);
        }
        $platform_id=session("platform_id");

        $user=Db::name("member")->where("member_id",$this->user["member_id"])->find();
        $user["show_id"]=sprintf("%06d",$user['member_id']);
        session("user",$user);
              
        $this->user=$user;

        $tree=Db::name("member_tree")
            ->where("member_id",$this->user["member_id"])
            ->where("platform_id",$platform_id)
            ->find();
        if(!$tree){
            header("location:index");
        }
        $yeji=Db::name("member_data")
            ->field("SUM(enterprise) s")
            ->where("member_id",$this->user["member_id"])
            ->where("platform_id",$platform_id)
            ->find();
        

        $son_man=Db::name("member_tree")
            ->field("member_id,create_mon")
            ->where("parent_id",$this->user["member_id"])
            ->where("platform_id",$platform_id)
            ->select();


        //echo count($son_man);

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
        $new_num=0;
        foreach ($new_youxiao as $key => $value) {
            if($value["s"]>=50){
                $new_num++;//有效新增人数
            }
        }



        $dian=$this->getdian($son_yeji["s"],$new_num);
        $son_yongjin=round($dian*$son_yeji["s"]/100,2);


        $son2_man=Db::name("member_tree")
        ->field("member_id")
        ->where("parent2_id",$this->user["member_id"])
        ->where("platform_id",$platform_id)
        ->select();
        $son2_arr=[];
        $s2num=0; $s2_all_num1=0;
        foreach ($son2_man as $key => $value) {
           
                $son_parent_id=Db::name("member")->where("member_id",$value["member_id"])->value("parent_id");
                $is_agency=Db::name("member")->where("member_id",$son_parent_id)->value("is_agency");

                if($is_agency == 1 && in_array($son_parent_id,$son_arr)){ //上级是代理，才计入人数
                    // echo $value["member_id"]."=";
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

        }
        //print_r($son2_arr);
        $son2_yeji=Db::name("member_data")
        ->field("SUM(enterprise) s")
        ->whereIn("member_id",$son2_arr)
        ->where("platform_id",$platform_id)
        ->where("mon",$mon)
        ->find();

        //var_dump($son2_yeji);
        
        if($user["is_2"] == 0){
            $dian2=0;
        }else{
           $dian2=$this->getdian2($son2_yeji["s"],$s2num); 
           //$dian2=$this->getdian2($son2_yeji["s"],count($son2_man)); 
        }
        $son2_yongjin=round($dian2*$son2_yeji["s"]/100,2);

        $v=View::assign("yeji", (float) $yeji["s"]);
        //团队业绩
        $v->assign("son_yeji", (float) $son_yeji["s"]);
        $v->assign("son_yongjin", (float) $son_yongjin);
        $v->assign("son2_yeji", (float) $son2_yeji["s"]);
        $v->assign("son2_yongjin", (float) $son2_yongjin);

        //团队管理
        $v->assign("son_num", (int) count($son_man));
        //$v->assign("son2_num", (int) count($son2_man));
        $v->assign("son2_num", (int) $s2num);

        $v->assign("new_all_num", (int) count($new_arr));
        $v->assign("new_num", (float) $new_num);

        $v->assign("all_num", (int) count($son_man)+ $s2_all_num1);
    //$v->assign("all_num", (int) count($son_man)+(int) count($son2_man));

        // echo $dian."----";
        // echo $dian2;
        // exit;
        $v->assign("money", (float) $tree["money"]);

        $v->assign("user",$this->user);
        $v->assign("tree", $tree);
        //$v->assign("user",$this->user);

        //$v->assign("user",$this->user);
       
        return $v->fetch('home/index');
    }

    /**
     * 我的
     * @return mixed
     */
    public function my()
    {
        $v=View::assign("user",$this->user);
        
        $platform_id=session("platform_id");
        $v=$v->assign("platform_id",$platform_id);

       // $invite_code=Db::name("member")->where("member_id",$this->user["member_id"])->value("invite_code");
        $invite_code=Db::name("member")->where("member_id",$this->user["member_id"])->value("nickname");
        $url=$_SERVER["SERVER_NAME"]."/mobile/register?invite_code=".$invite_code;
        $v=$v->assign("url",$url);

        $v=$v->assign("phone",$this->hidtel($this->user["phone"]));

        return $v->fetch('v/my');
    }

    /**
     * 用户须知
     * @return mixed
     */
    public function user_notice()
    {
        $v=View::assign("user",$this->user);
       
        return $v->fetch('home/user_notice');
    }

    /**
     * 个人信息
     * @return mixed
     */
    public function info()
    {
        $v=View::assign("user",$this->user);
       
        return $v->fetch('home/info');
    }
     /**
     * 业绩明细
     * @return mixed
     */
    public function achievement()
    {
        $get=request()->param();

        if(!isset($get["date"])){
            $get["date"]=date("Y-m");
        }
        $date=date("Ym",strtotime($get["date"]));
        if(isset($get["member_id"])){
            $member_id=$get["member_id"];
        }else{
            $member_id=$this->user["member_id"];
        }
        $v=View::assign("user",$this->user);
        $platform_id=session("platform_id");

        $time_min=strtotime($get["date"]."-1 00:00:00");
        $time_max=strtotime("+1 month",$time_min);
        // echo $time_min."-----".$time_max;
        // exit;
        $yeji=Db::name("member_data")
            //->field("SUM(enterprise) s")
            ->where("member_id",$member_id)
            ->where("platform_id",$platform_id)
            ->where("mon",$date)
            // ->where("create_time",">=",$time_min)
            // ->where("create_time","<",$time_max)
            ->order("create_time desc")
            ->select();
        $enterprise=0;
        foreach ($yeji as $key => $value) {
            $enterprise+=$value["enterprise"];
        }
            // $value["yeji"]=(float)$yeji["s"];
            // $man[]=$value;
        $v=$v->assign("yeji",$yeji);
        $v=$v->assign("member_id",$member_id);
        $v=$v->assign("title",Db::name("member")->where("member_id",$member_id)->value("nickname"));
        $v=$v->assign("date",$get["date"]);
        $v=$v->assign("enterprise",$enterprise);
        return $v->fetch('v/achievement');
    }

    public function team()
    {
        $get=request()->param();
        $v=View::assign("user",$this->user);
        $type=$get["type"];
        if(!isset($get["agent"]) || ($get["agent"]!=0 && $get["agent"] !=1)){
            $agent=-1;
        }else{
            $agent=$get["agent"];
        }

        if(!isset($get["search"])){
            $search="";
        }else{
            $search=$get["search"];
        }
        $date="";
        if(isset($get["date"])){
            $date=$get["date"];
            $date_mon=date("Ym",strtotime($date));
        }
        
        $platform_id=session("platform_id");
        $son_man=Db::name("member_tree")
            ->alias("t")
            // ->field("")
            ->join("member m","t.member_id=m.member_id");

             $p="parent_id";
        if($type==2){
            $type=2;
            //$p="parent2_id";
            $p="parent_id";
            $son_man=$son_man->where("t.parent_id",$this->user["member_id"]);
        }elseif($type==1){
            $son_man=$son_man->where("t.parent_id",$this->user["member_id"]);
            $type=1;
        }elseif($type==3){
            $son_man=$son_man->where("t.parent_id=".$this->user["member_id"]." or t.parent2_id=".$this->user["member_id"]);
            $type=3;
        }elseif($type==99){
            $pid=$get["pid"];
            $title=Db::name("member")->where("member_id",$pid)->value("nickname");
            $v->assign("title",$title);
            $son_man=$son_man->where("t.parent_id",$pid);
            $type=99;
        }

        
         // if($agent != -1){
         //    $son_man=$son_man->where("t.agent",$agent);

         // }
         if($agent != -1){
            $son_man=$son_man->where("m.is_agency",$agent);
         }


         if($search!=""){
            $son_man=$son_man->where("m.member_id='".$search."' or m.phone='".$search."' or m.nickname like '%".$search."%'");
         }

        $son_man=$son_man->where("t.platform_id",$platform_id)
            ->select();
        $man=[];
        foreach ($son_man as $key => $value) {

            $son_id_arr=Db::name("member_tree")->field("member_id")->where("platform_id",$platform_id)->where("parent_id",$value["member_id"])->select();
            $son_id_array=[];
            foreach ($son_id_arr as $kk => $vv) {
                $son_id_array[]=$vv["member_id"];
            }

           $yeji=Db::name("member_data")
            ->field("SUM(enterprise) s");
            //$yeji=$yeji->where("member_id","in",$son_id_array);
            if($type==2){
                $yeji=$yeji->where("member_id","in",$son_id_array);
            }else{
                $yeji=$yeji->where("member_id",$value["member_id"]);
            }
            

            //$son2_mon=date("Ym");
            $date=Db::name("system_config")->where("name","user_month")->value("value");
            $son2_mon=date("Ym",strtotime($date."-01 00:00:00"));
            if(!empty($date_mon)){
                $yeji=$yeji->where("mon",$date_mon);
                $son2_mon=$date_mon;
            }

            $yeji=$yeji->where("platform_id",$platform_id)->find();

            $value["yeji"]=(float)$yeji["s"];
            $value["show_id"]=sprintf("%06d",$value['member_id']);

           // $value["num1"]
            $son_tree=Db::name("member_tree")
            ->where("parent_id",$value["member_id"])
            ->where("platform_id",$platform_id)->field("member_id")->select();
            $s_youxiao_num=0;


            foreach ($son_tree as  $val) {


                       $son_parent_id=Db::name("member")->where("member_id",$val["member_id"])->value("parent_id");
                        $is_agency=Db::name("member")->where("member_id",$son_parent_id)->value("is_agency");
                        if($is_agency == 1){ //上级是代理，才计入人数

                            //本月必须有业绩才算入人数
                            $s2_man_is_y=Db::name("member_data")
                            ->field("member_id")
                            ->where("member_id",$val["member_id"])
                            ->where("enterprise",">",0)
                            ->where("platform_id",$platform_id)
                            ->where("mon",$son2_mon)
                            ->limit(1)
                            ->find();

                            if($s2_man_is_y){
                                $s_youxiao_num++;
                            }
                        }
                // $s2_man_is_y=Db::name("member_data")
                // ->field("member_id")
                // ->where("member_id",$value["member_id"])
                // ->where("enterprise",">",0)
                // ->where("platform_id",$platform_id)
                // ->limit(1)
                // ->find();
                // $s2_man_is_y=Db::name("member_data")
                // ->field("SUM(enterprise) s")
                // ->where("member_id",$val["member_id"])
                // ->where("platform_id",$platform_id)
                // ->find();
               // if($s2_man_is_y["s"]){
               //      $s_youxiao_num++;
               // }
            }
            $value["num1"]=$s_youxiao_num;
            $man[]=$value;
        }

        
        $v->assign("man",$man);
        $v->assign("agent",$agent);
        $v->assign("search",$search);
        $v->assign("type",$type);
        $v->assign("date",$date);
        return $v->fetch('v/team');
    }
    public function first_team()
    {
        $get=request()->param();
        $v=View::assign("user",$this->user);
        $type=$get["type"]??1;
        if(!isset($get["agent"]) || ($get["agent"]!=0 && $get["agent"] !=1)){
            $agent=-1;
        }else{
            $agent=$get["agent"];
        }

        if(!isset($get["search"])){
            $search="";
        }else{
            $search=$get["search"];
        }
        $date="";
        if(isset($get["date"])){
            $date=$get["date"];
        }
        $platform_id=session("platform_id");
        $son_man=Db::name("member_tree")
            ->alias("t")
            // ->field("")
            ->join("member m","t.member_id=m.member_id");

        $p="parent_id";
        $son_man=$son_man->where("t.parent_id",$this->user["member_id"]);
        $type=1;
        // if($type==2){
        //     $type=2;
        //     $p="parent2_id";
        //     $son_man=$son_man->where("t.parent2_id",$this->user["member_id"]);
        // }elseif($type==1){
        //     $son_man=$son_man->where("t.parent_id",$this->user["member_id"]);
        //     $type=1;
        // }elseif($type==3){
        //     $son_man=$son_man->where("t.parent_id=".$this->user["member_id"]." or t.parent2_id=".$this->user["member_id"]);
        //     $type=3;
        // }elseif($type==99){
        //     $pid=$get["pid"];
        //     $title=Db::name("member")->where("member_id",$pid)->value("nickname");
        //     $v->assign("title",$title);
        //     $son_man=$son_man->where("t.parent_id",$pid);
        //     $type=99;
        // }

        
         if($agent != -1){
            $son_man=$son_man->where("m.is_agency",$agent);
         }

         if($search!=""){
            $son_man=$son_man->where("m.member_id='".$search."' or m.phone='".$search."' or m.nickname like '%".$search."%'");
         }

        $son_man=$son_man->where("t.platform_id",$platform_id)
            ->select();
        $man=[];
        foreach ($son_man as $key => $value) {
           $yeji=Db::name("member_data")
            ->field("SUM(enterprise) s")
            ->where("member_id",$value["member_id"]);

            if(!empty($date)){
                $yeji=$yeji->where("date",$date);
            }else{
                $date_mon=Db::name("system_config")->where("name","user_month")->value("value");
                $mon=date("Ym",strtotime($date_mon."-01 00:00:00"));
                $yeji=$yeji->where("mon",$mon);
            }
            $yeji=$yeji->where("platform_id",$platform_id)
            ->find();
            $value["yeji"]=(float)$yeji["s"];
            $value["show_id"]=sprintf("%06d",$value['member_id']);
            $man[]=$value;
        }

        
        $v->assign("man",$man);
        $v->assign("agent",$agent);
        $v->assign("search",$search);
        $v->assign("type",$type);
        $v->assign("date",$date);
        return $v->fetch('v/first_team');
    }


    public function team_copy()
    {
        $get=request()->param();
        $v=View::assign("user",$this->user);
        $type=$get["type"];
        if(!isset($get["agent"]) || ($get["agent"]!=0 && $get["agent"] !=1)){
            $agent=-1;
        }else{
            $agent=$get["agent"];
        }

        if(!isset($get["search"])){
            $search="";
        }else{
            $search=$get["search"];
        }
        
        
        $platform_id=session("platform_id");
        $son_man=Db::name("member_tree")
            ->alias("t")
            // ->field("")
            ->join("member m","t.member_id=m.member_id");

             $p="parent_id";
        if($type==2){
            $type=2;
            $p="parent2_id";
            $son_man=$son_man->where("t.parent2_id",$this->user["member_id"]);
        }elseif($type==1){
            $son_man=$son_man->where("t.parent_id",$this->user["member_id"]);
            $type=1;
        }elseif($type==3){
            $son_man=$son_man->where("t.parent_id=".$this->user["member_id"]." or t.parent2_id=".$this->user["member_id"]);
            $type=3;
        }elseif($type==99){
            $pid=$get["pid"];
            $title=Db::name("member")->where("member_id",$pid)->value("nickname");
            $v->assign("title",$title);
            $son_man=$son_man->where("t.parent_id",$pid);
            $type=99;
        }

        
         if($agent != -1){
            $son_man=$son_man->where("t.agent",$agent);
         }

         if($search!=""){
            $son_man=$son_man->where("m.member_id='".$search."' or m.phone='".$search."' or m.nickname like '%".$search."%'");
         }

        $son_man=$son_man->where("t.platform_id",$platform_id)
            ->select();
        $man=[];
        foreach ($son_man as $key => $value) {
           $yeji=Db::name("member_data")
            ->field("SUM(enterprise) s")
            ->where("member_id",$value["member_id"])
            ->where("platform_id",$platform_id)
            ->find();
            $value["yeji"]=(float)$yeji["s"];
            $value["show_id"]=sprintf("%06d",$value['member_id']);
            $man[]=$value;
        }

        
        $v->assign("man",$man);
        $v->assign("agent",$agent);
        $v->assign("search",$search);
        $v->assign("type",$type);
       
        return $v->fetch('v/team');
    }

    public function newteam()
    {

        $get=request()->param();
        $type=$get["type"];
       
        $platform_id=session("platform_id");
        $son_man=Db::name("member_tree")
            ->alias("t")
            ->join("member m","t.member_id=m.member_id");
        $son_man=$son_man->where("t.parent_id",$this->user["member_id"]);
        $son_man=$son_man->where("t.create_mon",date("Ym"));
      
        $son_man=$son_man->where("t.platform_id",$platform_id)
            ->select();
        $man=[];
        $man_youxiao=[];
        foreach ($son_man as $key => $value) {
           $yeji=Db::name("member_data")
            ->field("SUM(enterprise) s")
            ->where("member_id",$value["member_id"])
            ->where("platform_id",$platform_id)
            ->find();
            $value["yeji"]=(float)$yeji["s"];
            $man[]=$value;
            if((float)$yeji["s"] >=50){
                $man_youxiao[]=$value;
            }
        }

        $v=View::assign("user",$this->user);
        
        if($type==1){
            $v->assign("man",$man_youxiao);
        }else{
            $v->assign("man",$man);
        }
        $v->assign("type",$type);

        $v->assign("num",[count($man),count($man_youxiao)]);

        $v=View::assign("user",$this->user);
       
        return $v->fetch('v/newteam');
    }
    public function withdrawal()
    {
        $v=View::assign("user",$this->user);
        if($this->user["is_identity"] != 2){
            header("location:txrz");
        }
        $platform_id=session("platform_id");
        if(!$platform_id){
            header("location:index");
        }
        $tree=Db::name("member_tree")
            ->where("member_id",$this->user["member_id"])
            ->where("platform_id",$platform_id)
            ->find();
        $v=$v->assign("tree",$tree);
        $min_money=Db::name("system_config")->where("name","min_money")->value("value");
        $v=$v->assign("min_money",$min_money);
        return $v->fetch('v/withdrawal');
    }

    /**
     * 计算佣金点位
     * @return mixed
     */
    // public function getdian($yeji,$new)
    // {
    //        //$yeji=5000;//月销售业绩
    //        //$num=6;//直推人数
    //        //$new=3;//新增有效人数
    //        $dian=0;
    //        if($yeji>800000){
    //             $dian=13;
    //        }elseif ($yeji>=400000) {
    //             $dian=9;
    //        }elseif ($yeji>=200000) {
    //             $dian=7;
    //        }elseif ($yeji>=100000) {
    //             $dian=5;
    //        }elseif ($yeji>=30000) {
    //             $dian=4;
    //        }elseif ($yeji>=2000) {
    //             $dian=3;
    //        }
           

    //        if($new>50){
    //             $dian+=4;
    //        }elseif ($yeji>=20) {
    //             $dian+=3;
    //        }elseif ($yeji>=10) {
    //             $dian+=2;
    //        }elseif ($yeji>=5) {
    //             $dian+=1.5;
    //        }elseif ($yeji>=2) {
    //             $dian+=1;
    //        }
    //        return $dian;



    // }
    // /**
    //  * 计算2级佣金点位
    //  * @return mixed
    //  */
    // public function getdian2($yeji,$num)
    // {
    //    $dian=0;
    //    if($yeji>500000 && $num>=100){
    //         $dian=3;
    //    }elseif ($yeji>=200000 && $num>=50) {
    //         $dian=2;
    //    }elseif ($yeji>=100000 && $num>=20) {
    //         $dian=1.5;
    //    }elseif ($num>=10) {
    //         $dian=1;
    //    }
    //    return $dian;



    // }



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

           // if($yeji>800000){
           //      $dian=13;
           // }elseif ($yeji>=400000) {
           //      $dian=9;
           // }elseif ($yeji>=200000) {
           //      $dian=7;
           // }elseif ($yeji>=100000) {
           //      $dian=5;
           // }elseif ($yeji>=30000) {
           //      $dian=4;
           // }elseif ($yeji>=2000) {
           //      $dian=3;
           // }

           // if($new>50){
           //      $dian+=4;
           // }elseif ($yeji>=20) {
           //      $dian+=3;
           // }elseif ($yeji>=10) {
           //      $dian+=2;
           // }elseif ($yeji>=5) {
           //      $dian+=1.5;
           // }elseif ($yeji>=2) {
           //      $dian+=1;
           // }
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
       
       // if($yeji>500000 && $num>=100){
       //      $dian=3;
       // }elseif ($yeji>=200000 && $num>=50) {
       //      $dian=2;
       // }elseif ($yeji>=100000 && $num>=20) {
       //      $dian=1.5;
       // }elseif ($num>=10) {
       //      $dian=1;
       // }
       return $dian;

    }

        /***
        屏蔽电话中间四位
        示例：

        $phonenum = "13966778888";

        echo hidtel($phonenum);

        最后输出： 139****8888 
        ***/
    public function hidtel($phone){

            $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i',$phone); //固定电话

            if($IsWhat == 1){

                return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i','$1****$2',$phone);

            }else{

                return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);

            }

    }


     public function tobeagent(){

        $v=View::assign("user",$this->user);
        $platform_id=session("platform_id");
        $v=View::assign("platform_id",$platform_id);
        $get=request()->param();
        if(isset($get["code"])){
            
            $code=$get["code"];
            //$member->valid($post, 'wx_login');
            $config = config('wechat');

            $JSAPI=$config["JSAPI"];
            //$find = (new EasyWechat('JSAPI'))->applet_info($post['code']);
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$JSAPI["app_id"]."&secret=".$JSAPI["secret"]."&code=".$code."&grant_type=authorization_code";
               // https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
            $result=$this->curl($url);

            //echo $code;exit;
            $jsonObj=json_decode($result,true);

                //$jsonARR=json_decode($result,true);
                //error(1,"ccc",$jsonARR);
                
            $openid=$jsonObj["openid"];
           // echo $openid;exit;

            $v=View::assign("openid",$openid);
            $v=View::assign("code",$code);
        }else{
            $v=View::assign("code","");
            $v=View::assign("openid","");
        }

        $invite_code=Db::name("member")->where("member_id",$this->user["member_id"])->value("invite_code");
        $url=$_SERVER["SERVER_NAME"]."?invite_code=".$invite_code."_".$platform_id;


        $agency_money=Db::name("system_config")->where("name","agency_money")->value("value");
        $agency_service_charge=Db::name("system_config")->where("name","agency_service_charge")->value("value");
        $v=View::assign("url",$url);
        $v=View::assign("agency_money",$agency_money);
        $v=View::assign("agency_service_charge",$agency_service_charge);

        return $v->fetch('v/tobeagent');

    }




    public function  forget(){
        return View::fetch('v/forget');
    }
    public function  khxz(){

        $text=Db::name("system_config")->where("name","user_notice")->value("value");
        View::assign("text",$text);
        return View::fetch('v/khxz');
    }
    public function  tx_log(){

        $text=Db::name("member_withdraw")
        ->where("member_id",$this->user["member_id"])
        ->where("delete_time","=",null)
        ->select();
        View::assign("text",$text);
        View::assign("date",date("Y-m-d"));
        return View::fetch('v/tx_log');
    }


    public function  team_all(){


        $get=request()->param();

        $date=Db::name("system_config")->where("name","user_month")->value("value");
        $mon=date("Ym",strtotime($date."-01 00:00:00"));
        if(isset($get["platform_id"])){
            session("platform_id",$get["platform_id"]);
        }
        $platform_id=session("platform_id");

        $user=Db::name("member")->where("member_id",$this->user["member_id"])->find();
        session("user",$user);
              
        $this->user=$user;

        $tree=Db::name("member_tree")
            ->where("member_id",$this->user["member_id"])
            ->where("platform_id",$platform_id)
            ->find();
        if(!$tree){
            header("location:index");
        }
        // $yeji=Db::name("member_data")
        //     ->field("SUM(enterprise) s")
        //     ->where("member_id",$this->user["member_id"])
        //     ->where("platform_id",$platform_id)
        //     ->find();
        

        $son_man=Db::name("member_tree")
            ->field("member_id,create_mon")
            ->where("parent_id",$this->user["member_id"])
            ->where("platform_id",$platform_id)
            ->select();
       // print_r($son_man);
// echo $this->user["member_id"]."-";
        $son_arr=[];$new_arr=[];$agency_arr=[];
        foreach ($son_man as $key => $value) {

            if(Db::name("member")->where("member_id",$value["member_id"])->value("is_agency") == 1){

                $agency_arr[]=$value["member_id"];
            }
           $son_arr[]=$value["member_id"];
          
        }
        $son2_man=Db::name("member_tree")
            ->field("member_id,create_mon")
            ->whereIn("parent_id",$agency_arr)
            ->where("platform_id",$platform_id)
            ->select();
        $son2_array=[];
        foreach ($son2_man as $key => $value) {
           // if(Db::name("member_data")->where("member_id",$value["member_id"])->where("platform_id",$platform_id)->value("data_id")){
                $son2_array[]=$value;
           // }          
        } 
        
        $count1=count($son_man);
        $count2=count($son2_array);
        View::assign("count",[$count1,$count2]);
        return View::fetch('v/team_all');
    }


    public function curl($url){
       // $url = "http://git.oschina.net/yunluo/API/raw/master/notice.txt";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $notice = curl_exec($ch);

        return $notice;
    }
    
}