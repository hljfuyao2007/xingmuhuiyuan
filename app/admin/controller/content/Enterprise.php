<?php
/**
 * Created by Automatic scripts.
 * User: Kassy
 * Date: 2022-04-13
 * Time: 14:21:00
 * Description:
 */

namespace app\admin\controller\content;


use app\admin\model\MemberData;
use app\common\controller\AdminController;
use app\common\service\Excel;
use think\facade\Db;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class Enterprise extends AdminController
{
    /**
     * 列表
     * @param MemberData $memberData
     * @return array|false|mixed|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    
    public $dian_arr=[];
    public $dian2_arr=[];
    public $dian3_arr=[];
    public function index(MemberData $memberData)
    {

        if ($this->request->isAjax()) {
            [$limit, $where] = $this->buildTableParam($memberData);
             //return $this->success($where);


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
                ->paginate($limit);
            //$new_data=[];
            foreach ($data as $key => $value) {
                $value["platform"]=$p[$value["platform_id"]];
                //$new_data[]=$value;
            }
            //$data=$new_data;
            return $this->success($data);
        }

        return $this->fetch();
    }

     /**
     * 构建请求参数
     * @param null $model 要查询的模型
     * @param array $excludeFields 忽略构建搜索的字段
     * @return array
     */
    protected function buildTableParam($model = null, array $excludeFields = []): array
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

    public function inc()
    {


        $now_datetime=date("Y-m-d H:i:s");
        $false_item=[];
        $success_item=[];
        if ($this->request->isPost()) {
            $data = (new Excel())->import();

            $file_name=$data["name"];

            $new_name=$data["newname"];
            $file_id=Db::name("excel")->insertGetid([
                "create_time"=>$now_datetime,
                "update_time"=>$now_datetime,
                "title"=>$file_name,
                "filename"=>$new_name
                ]);
            $data=$data["data"];
           // if(is_numeric(44683)){
              
            
            // TODO::分销逻辑
            if ($data) {

                foreach ($data as $item) {

                    if (!$item[0] || !$item[1] || !$item[2] || (!is_float($item[2]) &&!is_numeric($item[2]) )) {
                        $item[3]="数据错误";
                        $item[0]=$this->excelTime($item[0]);
                        $false_item[]=$item;
                        continue;
                    }
                    
                    $item[0]=$this->excelTime($item[0]);
                   // echo $item[0];exit;
                    $date=$item[0];
                    $uid=$item[1];
                    $money=$item[2];

                    //除十取整  积分换算成钱数
                    $money=floor($money/10);

                    $mt=Db::name("member_tree")->field("member_id,platform_id")->where("uid",$uid)->find();
                    if(!$mt){
                        $item[3]="未查询到用户";
                        $false_item[]=$item;
                    }else{
                        $success_item[]=$item;
                        MemberData::create([
                            'member_id'  => $mt["member_id"],
                            'platform_id'  => $mt["platform_id"],
                            'uid'        => $uid,
                            'date'       => datetime($date, 'Y-m-d'),
                            'mon'       => datetime($date, 'Ym'),
                            'enterprise' => $money,
                            'file_id'=>$file_id
                        ]);
                    }
                }
            }

            Db::name("excel")->where("id",$file_id)->update(["success"=>count($success_item),"false"=>count($false_item)]);
            return $this->success([
                "false_item"=>$false_item,
                "success_item"=>$success_item
                ], '导入成功',1);
            //return $this->success($false_item, '导入成功', 1);
        }

        return $this->fetch("",["false_item"=>$false_item ]);
    }

     public function dec()
    {

        $param = $this->request->param();

        $today=date("Y-m-d");
        $yestoday=date("Y-m-d",strtotime("-1 day"));
        if(date("d")!=1){//月初不回退昨天数据
            
            DB::name("member_data")->where("date",$yestoday)->where("set",0)->delete();
        }
        DB::name("member_data")->where("date",$today)->where("set",0)->delete();
        return $this->success([], '回退成功', 1);
    }



    public function inc_old()
    {
        if ($this->request->isPost()) {
            $data = (new Excel())->import();
            // TODO::分销逻辑
            if ($data) {
                $false_item=[];
                foreach ($data as $item) {
                    if (!$item[0] || !$item[2] || !$item[4] || !is_float($item[4])) {
                        continue;
                    }

                    $mt=Db::name("member_tree")->field("member_id,platform_id")->where("id",(int)$item[2])->find();
                    if(!$mt){
                        $false_item[]=$item;
                    }else{
                        MemberData::create([
                            'member_id'  => $mt["member_id"],
                            'platform_id'  => $mt["platform_id"],
                            'uid'        => $item[2],
                            'date'       => datetime($item[0], 'Y-m-d'),
                            'mon'       => datetime($item[0], 'Ym'),
                            'enterprise' => floor((float)$item[4]*100)/100,
                        ]);
                    }
                }
            }
            return $this->success($false_item, '导入成功', 1);
        }

        return $this->fetch();
    }

    public function set(){
        return $this->fetch();
    }


    public function commission(){

        $param = $this->request->param();

        // echo $this->getdian(500000,5);
        // print_r($this->dian_arr);
        // die;


        $platform=Db::name("platform")->field("name,platform_id")->select();
        $this->assign("platform",$platform);
        $p=[];
        foreach ($platform as $key => $value) {
            $p[$value["platform_id"]]=$value["name"];
        }
        $res=Db::name("commission")->alias("c")
        ->join("member m","m.member_id=c.member_id","left")
        ->join("member_tree t","t.member_id=c.member_id and t.member_id=c.member_id","left")
        ->field("c.*,m.nickname,m.phone,t.uid")
        ->group("c.id")
        ->order("id desc");

        if(isset($param['platform_id']) && $param["platform_id"]!=0){
            $res=$res->where("c.platform_id",$param["platform_id"]);
            $this->assign("platform_id",$param['platform_id']);
        }else{
             $this->assign("platform_id",0);
        }
        if(isset($param['nickname']) && $param["nickname"]!=''){
            $res=$res->where("m.nickname","like","%".$param["nickname"]."%");
            $this->assign("nickname",$param['nickname']);
        }

        if(isset($param['starttime']) && isset($param['endtime']) ){
            $res=$res->where("c.create_time",">",date("Y-m-d H:i:s",$param["starttime"]));
            $res=$res->where("c.create_time","<",date("Y-m-d H:i:s",$param["endtime"]));
            $this->assign("date",date("Y-m-d",$param["starttime"])." - ".date("Y-m-d",$param["endtime"]));
            $res=$res->select();
        }else{
           $res=$res->paginate(15); 
           // 获取分页显示
            $page = $res->render();
            $this->assign("page",$page);
        }
        
     
        
        // $res=$res->toArray();
        //$page=1111;
        $re=[];
        $zong=["y"=>0,"s_y"=>0,"s2"=>0,"s2_y"=>0,"s_new"=>0,"money"=>0];
        foreach ($res as $key => $value) {
            $json=json_decode($value["json"],true);
            $value["y"]=$json["y"];
            $value["s_y"]=$json["s_y"];
            $value["s2"]=$json["s2"];
            $value["s2_y"]=$json["s2_y"];
            $value["s_new"]=$json["s_new"];
            $zong["y"]+=$json["y"];
            $zong["s_y"]+=$json["s_y"];
            $zong["s2"]+=$json["s2"];
            $zong["s2_y"]+=$json["s2_y"];
            $zong["s_new"]+=$json["s_new"];
            $zong["money"]+=$value["money"];

            $value["platform"]=$p[$value["platform_id"]];
            $re[]=$value;
        }


        return $this->fetch("",["item"=>$re,"zong"=>$zong]);
    }

    public function true_set(){
        set_time_limit(0);
        ignore_user_abort();


        $now_datetime=date("Y-m-d H:i:s");
        $mon=date("Ym",strtotime('-1 month'));
        //$mon=date("Ym");
        //获取上个月所有的业绩
        $member_data=Db::name("member_data")
            ->where("mon",$mon)
            ->where("set",0)
            ->where("delete_time","=",null)
            ->select();

        //获取所有平台用户
        $tree=Db::name("member_tree")
        ->alias("t")
        ->join("member m","t.member_id=m.member_id")
        ->field("t.*,m.is_agency")
        ->select();
        $p_tree=[0=>[],1=>[],2=>[],3=>[]];//按照平台划分出用户
        foreach ($tree as $key => $value) {
            if(!isset($p_tree[$value["platform_id"]])){
                $p_tree[$value["platform_id"]]=[];
            }
            $value["y"]=0;
            $value["s_y"]=0;
            $value["s2"]=0;
            $value["s2_y"]=0;
            $value["s_new"]=0;
            //$value["p_agency"]=0;
            $value["s2_arr"]=[];
                //$value["new_arr"]=[];
            $p_tree[$value["platform_id"]][$value["member_id"]]=$value;
            
        }

        foreach ($member_data as $key => $value) {//业绩
            
            if(!isset($p_tree[$value["platform_id"]][$value["member_id"]])){
                continue;
            }
             $p_tree[$value["platform_id"]][$value["member_id"]]["y"]+=$value["enterprise"];
            
            
        }

        foreach ($tree as $key => $value) { //计算团队下各种人数
            
            if($value["parent_id"]!=0){
                if(isset($p_tree[$value["platform_id"]][$value["parent_id"]])){
                    $p_tree[$value["platform_id"]][$value["parent_id"]]["s_y"]+=$p_tree[$value["platform_id"]][$value["member_id"]]["y"];//为上级的一级团队业绩累加
                    //$p_tree[$value["platform_id"]][$value["parent_id"]]["new_arr"][]=[$p_tree[$value["platform_id"]][$value["member_id"]]["y"],$value["member_id"]];
                    if($value["create_mon"] == $mon && $p_tree[$value["platform_id"]][$value["member_id"]]["y"]>=50){//上月新增 业绩大于50(有效新增)
                        $p_tree[$value["platform_id"]][$value["parent_id"]]["s_new"]+=1;//有效新增
                       
                    }  
                }
                              
            }
            if($value["parent2_id"]!=0){
                //上级及上上及都存在在这个平台
                if(isset($p_tree[$value["platform_id"]][$value["parent2_id"]]) && isset($p_tree[$value["platform_id"]][$value["parent_id"]])){
                    $p_tree[$value["platform_id"]][$value["parent2_id"]]["s2"]+=1;//2级团队人数
                    $p_tree[$value["platform_id"]][$value["parent2_id"]]["s2_arr"][]=$value["member_id"];//2级团队人数
                    
                    $p_tree[$value["platform_id"]][$value["parent2_id"]]["s2_y"]+=$p_tree[$value["platform_id"]][$value["member_id"]]["y"];//为上上级的2级团队业绩累加
                }
                
            }
            // if(!isset($p_tree[$value["platform_id"]][$value["member_id"]])){
            //         continue;
            // }
        }



// echo "<pre>";
// print_r($p_tree);
// exit;

       foreach ($p_tree as $key => $value) {//$key为平台platform_id
           foreach ($value as $k =>$v) {//$k为用户member_id
             // echo $k;
                $is_2=Db::name("member")->where("member_id",$v["member_id"])->value("is_2");
                $dian=$this->getdian($v["s_y"],$v["s_new"]);
                if($is_2==0){$dian2=0;}else{
                    $s2num=0;$v["s2_y"]=0;
                    foreach ($v["s2_arr"] as $s2_item) {
                        // $parent_id=Db::name("member")->where("member_id",$s2_item)->value("parent_id");
                        // $is_agency=Db::name("member")->where("member_id",$parent_id)->value("is_agency");

                        //if()
                        $is_agency=$value[$v["parent_id"]]["is_agency"]??0;
                        if($is_agency == 1){ //上级是代理，才计入人数
                            $s2_man_is_y=$value[$s2_item]["y"];
                            //本月必须有业绩才算入人数
                   
                           if($s2_man_is_y){
                                $s2num++;
                                $v["s2_y"]+=$value[$s2_item]["y"];;
                           }
                        }
                        
                    }
                    //$dian2=$this->getdian2($v["s2_y"],$v["s2"]);
                    $dian2=$this->getdian2($v["s2_y"],$s2num);
                }
                if($v["s_y"]>0){$son_yongjin=round($dian*$v["s_y"]/100,2);}else{ $son_yongjin=0;};

                if($v["s2_y"]>0){$son2_yongjin=round($dian2*$v["s2_y"]/100,2);}else{ $son2_yongjin=0;};
                // $son2_yongjin=round($dian2*$v["s2_y"]/100,2);


                $in=[
                        "money"=>(float)$son_yongjin + (float)$son2_yongjin,
                        "mon"=>$mon,
                        "member_id"=>$k,
                        "platform_id"=>$v["platform_id"],
                        "json"=>json_encode($v),
                        // "son_yongjin"=>$son_yongjin,
                        // "son2_yongjin"=>$son2_yongjin,
                        "create_time"=>$now_datetime,
                        "update_time"=>$now_datetime,
                        "uid"=>$v["uid"]
                ];
                
                // if($k ==50){
                //     file_put_contents("commission.log", json_encode($in)."\r\n",FILE_APPEND);
                // }
                
                
                Db::name("commission")->insert($in);//佣金发放日志
                if($son_yongjin+$son2_yongjin > 0){
                    Db::name("member_tree")->where("member_id",$k)->where("platform_id",$key)->inc("money",$son_yongjin+$son2_yongjin)->update();
                }
                
           }
       }
       // exit;

        //Db::name("member_data")->where("mon",$mon)->where("set",0)->update(["set"=>1]);
       return $this->success([], '结算成功', 1);


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




    public function excel_list()
    {


        $param = $this->request->param();
        $excel=Db::name("excel")->where("delete_time","=",null)->order("id desc")->paginate(10);

    
        // 获取分页显示
        $page = $excel->render();

        return $this->fetch("",["item"=>$excel,"page"=>$page]);


    }

    public function excel_del()
    {
        $param = $this->request->param();
        $id=$param["id"];

        DB::name("excel")->where("id",$id)->update(["delete_time"=>date("Y-m-d H:i:s")]);
        DB::name("member_data")->where("file_id",$id)->delete();
        return $this->success([], '删除成功', 1);
        // $param = $this->request->param();
        // $excel=Db::name("excel")->order("id desc")->paginate(10);

    
        // // 获取分页显示
        // $page = $excel->render();

        // return $this->fetch("",["item"=>$excel,"page"=>$page]);


    }


    public function excelTime($days){
        if(is_numeric($days)){
            $jd = GregorianToJD(1, 1, 1970);
            $gregorian = JDToGregorian($jd+intval($days)-25569);
            $myDate = explode('/',$gregorian);
            //return json_encode($myDate);
            $myDateStr = str_pad($myDate[2],4,'0', STR_PAD_LEFT)
                    ."-".str_pad($myDate[0],2,'0', STR_PAD_LEFT)
                    ."-".str_pad($myDate[1],2,'0', STR_PAD_LEFT);
                   // .($time?" 00:00:00":'');
            return $myDateStr;
        }
        return $days;
    }



    public function exportExcel(){

        $param = $this->request->param();
        // print_r($param);exit;
        $columName=$param["columName"];
        $list=$param["list"];
        $fileName=$param["fileName"]??'xingmu_downloadfile';
        $download=$param["download"]??false;
        if(!is_array($columName))$columName=json_decode($columName,true);
        if(!is_array($list))$list=json_decode($list,true);
        
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
         

        

        // if ( empty($columName) || empty($list) ) {
        //     return '列名或者内容不能为空';
        // }
        // if ( count($list[0]) != count($columName) ) {
        //     return '列名跟数据的列不一致';
        // }
        // $EXT=".xlsx";
        // $setTitle='Sheet1';
        // //实例化PHPExcel类
        // $PHPExcel    =    new \PHPExcel();
        // //获得当前sheet对象
        // $PHPSheet    =    $PHPExcel    ->    getActiveSheet();
        // //定义sheet名称
        // $PHPSheet    ->    setTitle($setTitle);
        // //excel的列 这么多够用了吧？不够自个加 AA AB AC ……
        // $letter        =    [
        //     'A','B','C','D','E','F','G','H','I','J','K','L','M',
        //     'N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
        // ];
        // //把列名写入第1行 A1 B1 C1 ...
        // for ($i=0; $i < count($list[0]); $i++) {
        //     //$letter[$i]1 = A1 B1 C1  $letter[$i] = 列1 列2 列3
        //     $PHPSheet->setCellValue("$letter[$i]1","$columName[$i]");
        // }
        // //内容第2行开始
        // foreach ($list as $key => $val) {
        //     //array_values 把一维数组的键转为0 1 2 3 ..
        //     foreach (array_values($val) as $key2 => $val2) {
        //         //$letter[$key2].($key+2) = A2 B2 C2 ……
        //         $PHPSheet->setCellValue($letter[$key2].($key+2),$val2);
        //     }
        // }
        // //生成2007版本的xlsx
        // $PHPExcel_IOFactory=new \PHPExcel_IOFactory;
        // $PHPWriter = $PHPExcel_IOFactory->createWriter($PHPExcel,'Excel2007');
        
        
        
        // if($download){
        //      $load_Path=$_SERVER['SCRIPT_FILENAME'];
        //      $load_Path="./";
        //     $user_path = $load_Path.'excel/';//保存路径
        //     $PHPWriter->save($user_path.$fileName.$EXT);//保存excle文件
        // }else{
        //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //     header('Content-Disposition: attachment;filename='.$fileName.'.xlsx');
        //     header('Cache-Control: max-age=0');
        //     $PHPWriter->save("php://output");
        // }
         
    }

}