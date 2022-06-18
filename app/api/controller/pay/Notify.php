<?php

namespace app\api\controller\pay;

use app\api\model\AgentOrder;
use app\api\model\Member;
use app\common\controller\ApiController;
use think\facade\Db;
use think\request;
use think\cache;
require_once "../extend/WxPay/WxPay.Api.php";
require_once "../extend/WxPay/WxPay.Config.php";
require_once "../extend/WxPay/WxPay.JsApiPay.php";
require_once "../extend/WxPay/log.php";

class Notify extends ApiController
{
    /**
     * 微信支付回调
     * @return bool
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function wxNotify()
    {
        $argsXml = file_get_contents('php://input');
        if ($argsXml) {
            //禁止引用外部xml实体
            libxml_disable_entity_loader(true);
            writeLog('======================微信回调开始======================' . date('Y-m-d H:i:s') . PHP_EOL, 'notify/WxPay');
            $argsArr = json_decode(json_encode(simplexml_load_string($argsXml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            writeLog(var_export($argsArr, true) . PHP_EOL, 'notify/WxPay');
            $bodyArr = explode('|', $argsArr['attach']);
            $wechatConfig = config('wechat');
            $app = \EasyWeChat\Factory::payment(array_merge($wechatConfig[$argsArr['trade_type']], $wechatConfig['payment']));
            // 查询微信订单合法性
            $queryRet = $app->order->queryByOutTradeNumber($argsArr['out_trade_no']);
             $dd=Db::name("agent_order")->where("order_number",$argsArr['out_trade_no'])->find();
                Db::name("member_tree")->where("member_id",$dd["member_id"])->where("platform_id",$dd["platform_id"])->update(["agent"=>1]);
                Db::name("member")->where("member_id",$dd["member_id"])->update(["is_agency"=>1]);
                Db::name("agent_order")->where("order_number",$argsArr['out_trade_no'])->update(["status"=>1]);
            // writeLog(var_export($queryRet, true) . PHP_EOL, 'notify/WxPay');
            // if ($queryRet['return_code'] === 'SUCCESS' && $queryRet['result_code'] === 'SUCCESS' &&
            //     $queryRet['trade_state'] === 'SUCCESS') {
            //    // $res = $app->handlePaidNotify(function ($msg, $fail) use ($bodyArr, $app) {
            //     //$f = true;
                
               
                    
            //     //});
            //    // $res->send();
            // }
        }
        return true;
    }



    public function add(){
        $post = $this->request->post();
        //$type = Request::post("type");
        $money = $post["money"];
        $user_id = $post["user_id"];
        $order_no =$post["order_no"];
        $platform_id =$post["platform_id"];
        $openid =$post["openid"];
        $tools = new \JsApiPay();
        $input = new \WxPayUnifiedOrder();
        $config = new \WxPayConfig();
        //$data['num'] = Request::post("num");
        //$data['type'] = $type;
        // $data['time'] = time();
        // $data['user_id'] = $user_id;
         
        $a=[
                "member_id"=>$user_id,
                "order_number"=>$post["order_no"],
                "agency_money"=> $money,
                "total_fee"=>$money,
                "status"=>0,
                "create_time"=>time(),
                "update_time"=>time(),
                "platform_id"=>$platform_id,

        ];
        Db::name('agent_order')->insert($a);
        $array = [
            "order_no"=>$order_no,
            "info"=>"",
            "add_time"=>time(),
            "update_time"=>0,
            "user_id"=>$user_id,
            "money"=>$money,
            "status"=>0,
            // "type"=>$type
        ];
        if($openid == ""){
            $openid = Db::name('member')->where("member_id",$user_id)->value("open_id");
        }
        
        
        //$id = Db::name("pay_order")->insertGetId($array);
        $input->SetBody("充值");
        $input->SetAttach(json_encode($array));
        $input->SetOut_trade_no($order_no);
        $input->SetTotal_fee($money);
        //$input->SetTotal_fee(1);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url($_SERVER["SERVER_NAME"]."/api/v1.0/pay/wxNotify");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid);
        $order = \WxPayApi::unifiedOrder($config, $input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        //R::success("订单生成成功",$jsApiParameters);
        return apiShow($jsApiParameters);
 

    }
}