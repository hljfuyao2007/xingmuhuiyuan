<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-06
 * Time: 16:36
 * Description:
 */

namespace app\common\service\sms;


use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Darabonba\OpenApi\Models\Config;

class ALi
{
    /**
     * @var string 应用ID
     */
    private static $appId;
    /**
     * @var string 应用Key
     */
    private static $appKey;
    /**
     * @var string 发送短信的号码
     */
    private $phone;
    /**
     * @var string 短信签名
     */
    private static $sign;
    /**
     *
     * @var string 模板ID
     */
    private $tempId;
    /**
     * @var mixed 参数
     */
    private $param;
    /**
     * 短信实例
     * @var
     */
    private static $instance;

    public function __construct($phone = '', $param = [], $tempId = '', $code = '')
    {
        $sysconfig = sysconfig('sms');
        $this->phone = $phone;
        $this->param = array_merge($param, ['code' => $code]);
        self::$appId = $sysconfig['alisms_access_key_id'];
        self::$appKey = $sysconfig['alisms_access_key_secret'];
        self::$sign = $sysconfig['alisms_sign'];
        $this->tempId = $tempId;

        // 初始化阿里云短信
        $config = new Config([
            "accessKeyId"     => self::$appId,
            "accessKeySecret" => self::$appKey
        ]);

        $config->endpoint = 'dysmsapi.aliyuncs.com';
        self::$instance = new Dysmsapi($config);
    }

    /**
     * 发短信
     * @return \AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsResponse
     */
    public function sendSms(): \AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsResponse
    {
        $sendSmsRequest = new SendSmsRequest([
            'phoneNumbers'  => $this->phone,
            'signName'      => self::$sign,
            'templateCode'  => $this->tempId,
            'templateParam' => json_encode($this->param, JSON_UNESCAPED_UNICODE)
        ]);

        return self::$instance->sendSms($sendSmsRequest);
    }
}