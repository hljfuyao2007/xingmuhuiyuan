<?php

namespace app\api\controller\pay;

use app\api\model\AgentOrder;
use app\api\model\Member;
use app\common\controller\ApiController;

class Notify extends ApiController
{
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
            writeLog(var_export($queryRet, true) . PHP_EOL, 'notify/WxPay');
            if ($queryRet['return_code'] === 'SUCCESS' && $queryRet['result_code'] === 'SUCCESS' &&
                $queryRet['trade_state'] === 'SUCCESS') {
                $res = $app->handlePaidNotify(function ($msg, $fail) use ($bodyArr, $app) {
                    $f = true;
                    try {
                        $this->db->startTrans();
                        switch ($bodyArr[0]) {
                            case 'agent':
                                // 更新会员代理状态
                                Member::update([
                                    'member_id' => $bodyArr[1],
                                    'is_agency' => 1,
                                ]);
                                // 更新订单表
                                AgentOrder::update([
                                    'order_id'   => $bodyArr[2],
                                    'trade_no'   => $msg['transaction_id'],
                                    'pay_status' => 1,
                                ]);
                                break;
                            default:
                                $f = false;
                                break;
                        }
                        $this->db->commit();
                    } catch (\Exception $e) {
                        $this->db->rollback();
                        $f = '错误: ' . $e->getMessage() . PHP_EOL;
                    } finally {
                        if ($f !== true) {
                            writeLog($f, 'notify/WxPay');
                        }
                        return true;
                    }
                });
                $res->send();
            }
        }
        return true;
    }
}