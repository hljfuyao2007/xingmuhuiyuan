<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-03-31
 * Time: 13:22
 * Description:
 */

namespace app\api\controller\home;

use app\api\model\Member;
use app\api\model\MemberTree;
use app\api\model\MemberWithdraw;
use app\common\controller\ApiController;
use think\facade\Db;

class Index extends ApiController
{
    /**
     * 提现信息
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function withdrawInfo()
    {
        $mid = $this->deToken(0)->mid;

        $data = Member
            ::where('member_id', $mid)
            ->field('balance,name,alipay_account')
            ->find();
        $data['rate'] = sysconfig('site', 'withdraw_rate');

        return apiShow($data);
    }

    /**
     * 提现申请
     * @param MemberWithdraw $memberWithdraw
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function withdraw(MemberWithdraw $memberWithdraw, Member $member)
    {
        $post = $this->request->post();
        $post['member_id'] = $post['member_id'];//$this->deToken(0)->mid;
        $post['platform_id'] = $post['platform_id'];
        $post['rate'] = sysconfig('site', 'withdraw_rate');
        $post['income_money'] = $post['money'] * (1 - $post['rate'] / 100);

        $memberWithdraw->valid($post, 'withdraw');

       // $member->where('member_id', $post['member_id'])->value('balance', 0) < $post['money'] &&
        //abort(-1, '余额不足');
        $user=$member->where('member_id', $post['member_id'])->find();
        $tree=Db::name("member_tree")
            ->where("member_id",$post['member_id'])
            ->where("platform_id",$post['platform_id'])
            ->find();
        $money=$tree["money"];
        if($post['money']>$tree["money"]){
            abort(-1, '余额不足');
        }
        //$v=$v->assign("tree",$tree);
        // 减少余额
        //$member->where('member_id', $post['member_id'])->dec('balance', $post['money'])->update();
        Db::name("member_tree")
            ->where("member_id",$post['member_id'])
            ->where("platform_id",$post['platform_id'])
            ->dec("money",$post['money'])
            ->update();
        //$memberWithdraw::create($post);
        $in=[
            "member_id"=>$post['member_id'],
            "name"=>$user["name"],
            "account"=>$user["alipay_account"],
            "money"=>$post['money'],
            "income_money"=>$post['income_money'],
            "create_time"=>time(),
            "update_time"=>time(),
            "rate"=>$post['rate'],
            "platform_id"=>$post['platform_id']
        ];
        Db::name("member_withdraw")->insert($in);
        return apiShow([], '操作成功', 1);
    }

    /**
     * 我的下级
     * @param MemberTree $memberTree
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function subordinate(MemberTree $memberTree)
    {
        $get = $this->request->get();
        $get['member_id'] = $this->deToken(0)->mid;

        $where = [
            ['parent_id', '=', $get['member_id']],
            ['level', '=', 1],
            ['member.is_agency', '=', $get['level'] ?? 1],
        ];
        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['phone|name|nickname|platform_id', 'like', '%' . $get['keyword'] . '%'];
        }
        if (isset($get['register_time']) && $get['register_time']) {
            $where[] = ['member.register_time', 'between', [strtotime($get['register_time'] . ' 0:00:00'), strtotime($get['register_time'] . ' 23:59:59')]];
        }

        $data = $memberTree
            ->where($where)
            ->field('parent_id,level')
            ->withJoin([
                'member' => ['name', 'nickname', 'avatar', 'platform_id', 'phone', 'register_time', 'sex', 'member_id']
            ])
            ->paginate(10)
            ->each(function ($val) use ($memberTree) {
                $val->member->child_num = $memberTree
                    ->where([
                        ['parent_id', '=', $val->member->member_id],
                        ['level', '=', 1],
                    ])->count();
                // 手机号中间四位用*代替
                $val->member->phone = substr_replace($val->member->phone, '****', 3, 4);
                $val->member->show_register_time = datetime($val->member->register_time, 'Y/m/d H:i');
                $val->member->amount = 0.00;
            });

        return apiShow($data);
    }

    /**
     * 本月新增
     * @param MemberTree $memberTree
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function monthly_new(MemberTree $memberTree)
    {
        $get = $this->request->get();
        $get['member_id'] = $this->deToken(0)->mid;

        $where = [
            ['parent_id', '=', $get['member_id']],
            ['level', '=', 1],
            ['member_tree.create_time', 'between', getMonth(date('Y-m-d'))],
        ];
        if (isset($get['is_valid']) && $get['is_valid']) {
            $where[] = ['member.is_agency', '=', 1];
        }

        $data = $memberTree
            ->where($where)
            ->field('parent_id,level')
            ->withJoin([
                'member' => ['name', 'nickname', 'avatar', 'platform_id', 'phone', 'register_time', 'sex', 'member_id']
            ])
            ->order('create_time desc')
            ->paginate(10)
            ->each(function ($val) use ($memberTree) {
                // 手机号中间四位用*代替
                $val->member->phone = substr_replace($val->member->phone, '****', 3, 4);
                $val->member->show_register_time = datetime($val->member->register_time, 'Y/m/d H:i');
                $val->member->amount = 0.00;
            });

        return apiShow($data);
    }


    
    public function  plan_task(){
        
        //$mon=date("Ym",strtotime('-1 month'));
        $mon=date("Ym");
        //获取上个月所有的业绩
        $member_data=Db::name("member_data")
            ->where("mon",$mon)
            ->select();

        //获取所有平台用户
        $tree=Db::name("member_tree")->select();
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
            $p_tree[$value["platform_id"]][$value["member_id"]]=$value;
            
        }

        foreach ($member_data as $key => $value) {//业绩
            
             $p_tree[$value["platform_id"]][$value["member_id"]]["y"]+=$value["enterprise"];
            
        }

        foreach ($tree as $key => $value) { //计算团队下各种人数
            if($value["parent_id"]!=0){
                $p_tree[$value["platform_id"]][$value["parent_id"]]["s_y"]+=$p_tree[$value["platform_id"]][$value["member_id"]]["y"];//为上级的一级团队业绩累加
                if($value["create_mon"] == $mon && $p_tree[$value["platform_id"]][$value["member_id"]]["y"]>=50){//上月新增 业绩大于50(有效新增)
                    $p_tree[$value["platform_id"]][$value["parent_id"]]["s_new"]+=1;//有效新增
                }                
            }
            if($value["parent2_id"]!=0){
                $p_tree[$value["platform_id"]][$value["parent2_id"]]["s2"]+=1;//2级团队人数
                $p_tree[$value["platform_id"]][$value["parent2_id"]]["s2_y"]+=$p_tree[$value["platform_id"]][$value["member_id"]]["y"];//为上上级的2级团队业绩累加
            }
        }

 // echo "<pre>";
 //        print_r($p_tree);
 //        exit;
       foreach ($p_tree as $key => $value) {//$key为平台platform_id
           foreach ($value as $k =>$v) {//$k为用户member_id
            // echo $k;
                $dian=$this->getdian($v["s_y"],$v["s_new"]);
                $dian2=$this->getdian2($v["s2_y"],$v["s2"]);

                if($v["s_y"]>0){$son_yongjin=round($dian*$v["s_y"]/100,2);}else{ $son_yongjin=0;};
                if($v["s2_y"]>0){$son2_yongjin=round($dian*$v["s2_y"]/100,2);}else{ $son2_yongjin=0;};
                // $son2_yongjin=round($dian2*$v["s2_y"]/100,2);

                $in=[
                        "money"=>$son_yongjin+$son2_yongjin,
                        "mon"=>$mon,
                        "member_id"=>$k,
                        "platform_id"=>$key,
                        "json"=>json_encode($v)
                ];
 
                if($son_yongjin+$son2_yongjin > 0){
                    Db::name("member_tree")->where("member_id",$k)->where("platform_id",$key)->inc("money",$son_yongjin+$son2_yongjin)->update();
                    Db::name("commission")->insert($in);//佣金发放日志
                }
                
           }
       }

       return apiShow([]);


    } 

    /**
     * 计算佣金点位
     * @return mixed
     */
    public function getdian($yeji,$new)
    {
           //$yeji=5000;//月销售业绩
           //$num=6;//直推人数
           //$new=3;//新增有效人数
           $dian=0;
           if($yeji>800000){
                $dian=13;
           }elseif ($yeji>=400000) {
                $dian=9;
           }elseif ($yeji>=200000) {
                $dian=7;
           }elseif ($yeji>=100000) {
                $dian=5;
           }elseif ($yeji>=30000) {
                $dian=4;
           }elseif ($yeji>=2000) {
                $dian=3;
           }
           

           if($new>50){
                $dian+=4;
           }elseif ($yeji>=20) {
                $dian+=3;
           }elseif ($yeji>=10) {
                $dian+=2;
           }elseif ($yeji>=5) {
                $dian+=1.5;
           }elseif ($yeji>=2) {
                $dian+=1;
           }
           return $dian;



    }
    /**
     * 计算2级佣金点位
     * @return mixed
     */
    public function getdian2($yeji,$num)
    {
       $dian=0;
       if($yeji>500000 && $num>=100){
            $dian=3;
       }elseif ($yeji>=200000 && $num>=50) {
            $dian=2;
       }elseif ($yeji>=100000 && $num>=20) {
            $dian=1.5;
       }elseif ($num>=10) {
            $dian=1;
       }
       return $dian;



    }

}