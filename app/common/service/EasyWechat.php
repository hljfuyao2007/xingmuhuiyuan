<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-15
 * Time: 14:55
 * Description:
 */

namespace app\common\service;


use EasyWeChat\Factory;
use EasyWeChat\Kernel\Http\StreamResponse;
use think\facade\Request;

class EasyWechat
{
    public static $instance;
    protected $config;

    public function __construct(string $type, string $extra = '')
    {
        $this->config = config('wechat');
        $m = ["JSAPI" => "miniProgram", 'APP' => 'officialAccount'];
        $method = $m[$type] ?? $type;
        self::$instance = Factory::$method($extra ? array_merge($this->config[$type], $this->config[$extra]) : $this->config[$type]);
    }

    /**
     * app微信获取资料
     * @return array
     */
    public function app_info(): array
    {
        $user = self::$instance->oauth->user();
        if (!$user) {
            abort(-1, '微信资料获取失败');
        }
        return [
            'union_id' => $user->original['unionid'],
            'open_id'  => $user->id,
            'nickname' => $user->getNickname(),
            'avatar'   => $user->getAvatar(),
        ];
    }

    /**
     * 小程序微信获取资料
     * @param string $code
     * @return array
     */
    public function applet_info(string $code): array
    {
        $s = self::$instance->auth->session($code);
        if (!isset($s['session_key'])) {
            abort(-1, '微信密钥获取失败');
        }
        return [
            'union_id'    => $s['unionid'] ?? '',
            'open_id'     => $s['openid'],
            'session_key' => $s['session_key']
        ];
    }

    /**
     * 小程序获取微信授权手机号
     * @param string $session_key
     * @param string $encryptedData
     * @param string $iv
     * @return mixed|string
     */
    public function applet_get_phone(string $session_key, string $encryptedData, string $iv)
    {
        $user = self::$instance->encryptor->decryptData($session_key, $iv, $encryptedData);

        return $user['purePhoneNumber'] ?? '';
    }

    /**
     * 预下单
     * @param array $param
     * @param $order_info
     * @return mixed|array
     */
    public function pre_order(array $param, $order_info)
    {
        $this->config['cent'] && $param['total_fee'] = '1';
        $res = self::$instance->order->unify($param);
        if ($res['return_code'] === 'SUCCESS' && $res['result_code'] === 'SUCCESS') {
            $order_info->prepayment_info = $res['prepay_id'];
            if ($order_info->trade_type == 'JSAPI') {   // 小程序
                return self::$instance->jssdk->bridgeConfig($order_info->prepayment_info, false);
            } else { // app
                return self::$instance->jssdk->appConfig($order_info->prepayment_info);
            }
        }
        if ($res['return_code'] === 'SUCCESS' && $res['result_code'] === 'FAIL') {
            abort(-1, $res['err_code_des']);
        }
        return [];
    }

    /**
     * 支付回调
     * @param \Closure $c
     */
    public function notify(\Closure $c): void
    {
        self::$instance->handlePaidNotify($c)->send();
    }

    /**
     * 退款
     * @param string $trade_no
     * @param string $refund_order_number
     * @param $total_fee
     * @param $refund_fee
     * @param array $refund_options
     * @param int $abort
     * @return mixed|string
     */
    public function refund(string $trade_no, string $refund_order_number, $total_fee, $refund_fee,
                           array  $refund_options, int $abort = 1)
    {
        $this->config['cent'] && $total_fee = 1 && $refund_fee = 1;
        $refund_res = self::$instance->refund->byTransactionId($trade_no,
            $refund_order_number, $total_fee, $refund_fee, $refund_options);
        if ($refund_res['return_code'] !== 'SUCCESS' || $refund_res['result_code'] !== 'SUCCESS') {
            if ($abort) {
                abort(-1, '微信订单退款失败');
            } else {
                abort(-1, $refund_res['err_code_des'] ?? '微信订单退款失败');
            }
        }
        return '';
    }

    /**
     * 生成小程序太阳码
     * @param string $scene
     * @param array $options
     * @param int $regenerate
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function generateAppletCode(string $scene, array $options, int $regenerate = 0): string
    {
        $filepath = "/static/qrcode/{$options['dir']}/{$options['filename']}.png";

        if (file_exists(public_path() . $filepath)) {
            if ($regenerate) {
                @unlink($filepath);
            } else {
                return Request::domain() . $filepath;
            }
        }

        $res = self::$instance->app_code->getUnlimit($scene, [
            'width' => $options['width'],
            'page'  => $options['page']
        ]);
        is_array($res) && ($res['errcode'] ?? 0) && abort(-1, $res['errmsg']);
        if ($res instanceof StreamResponse) {
            $res->saveAs("./static/qrcode/{$options['dir']}", "{$options['filename']}.png");
        }

        return Request::domain() . $filepath;
    }
}