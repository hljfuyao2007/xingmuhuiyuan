<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-06
 * Time: 16:28
 * Description:
 */

namespace app\common\service\sms;


use think\facade\Cache;

class SMS
{
    /**
     *
     * @var string 短信类别 alisms阿里 qcloud腾讯
     */
    private static $smsType;
    /**
     * 0 缺省场景
     * 1 注册
     * 2 忘记密码
     * 3 找回密码
     * 4 修改密码
     * 5 短信登入
     * 6 修改手机号
     * @var string
     */
    private static $type;
    /**
     * @var string 手机号
     */
    private static $phone;
    /**
     * 短信实例
     * @var ALi
     */
    private static $instance;
    /**
     * 验证码id
     * @var null
     */
    private $code_id = null;

    public function __construct($phone = '', $type = 0, $param = [], $is_need_code = true)
    {
        if ($phone) {
            self::$smsType = sysconfig('sms', 'sms_type');
            self::$type = $type;
            self::$phone = $phone;

            $tempId = self::getTemplateId();

            if (!$tempId) {
                abort(-999, '短信模板异常,请检查模板类型是否选择正确');
            }

            switch (self::$smsType) {
                case 'alisms':
                    self::$instance = new \app\common\service\sms\ALi($phone, $param, $tempId, $is_need_code ? self::generate() : '');
                    break;
                case 'qcloud':
                    self::$instance = new \app\common\service\sms\QCloud(self::generate(), $phone, $param, $tempId);
                    break;
                default:
                    self::$instance = new \app\common\service\sms\ALi($phone, $param, $tempId, $is_need_code ? self::generate() : '');
            }
        }
    }

    /**
     * 获取模板id
     * @return array|mixed|object|\think\App
     */
    private static function getTemplateId()
    {
        return sysconfig(self::$smsType, self::$smsType . '_template_id_' . self::$type) ?:
            sysconfig(self::$smsType, self::$smsType . '_template_id_0');
    }

    /**
     * 生成验证码
     * @return string
     */
    private static function generate(): string
    {
        $code = mt_rand(0, 999999);
        if (strlen($code) < 6) {
            $code = str_pad($code, 6, '0', STR_PAD_LEFT);
        }
        // 验证码缓存
        if (sysconfig('sms', 'sms_is_check') == 'on') {
            $phone = self::$phone;
            $type = self::$type;
            Cache::set("SMS_{$phone}_{$type}", $code, sysconfig('sms', 'sms_expire_time'));
        }
        return $code;
    }

    /**
     * 验证验证码
     * @param string $phone
     * @param string $type
     * @param string $code
     * @return bool
     */
    public function verify(string $phone, string $type, string $code): bool
    {
        if (sysconfig('sms', 'sms_is_check') == 'on') {
            $old = Cache::get("SMS_{$phone}_{$type}", '');
            if ($old != $code) {
                // 记录重试次数, 达到3次删除
                $retry = Cache::get("SMS_{$phone}_{$type}", 0);
                if ($retry == 2) {
                    Cache::delete("SMS_{$phone}_{$type}");
                }
                Cache::set("SMS_retry_{$phone}_{$type}", ++$retry, sysconfig('sms', 'sms_expire_time'));
                abort(-1, '验证码不正确');
            }
            $this->code_id = "SMS_{$phone}_{$type}";
        }
        return true;
    }

    /**
     * 发送短信
     * @return array
     */
    public function send(): array
    {
        $ret = self::$instance->sendSms();
        $sucRes = $errRes = [];
        switch (self::$smsType) {
            case 'alisms':
                if ($ret->body->code == 'OK') {
                    $sucRes = ['code' => 1, 'msg' => '发送成功'];
                }
                // 限流
                if ($ret->body->code == 'isv.BUSINESS_LIMIT_CONTROL') {
                    $ret->body->message = (strstr($ret->body->message, '分钟') ? '60秒' : '1小时') . '内请勿重复获取验证码';
                }
                $errRes = ['code' => -1, 'msg' => $ret->body->message];
                break;
            case 'qcloud':
                if ($ret['result'] === 0) $sucRes = ['code' => 1, 'message' => '发送成功'];
                $errRes = ['code' => -1, 'msg' => $ret['errmsg']];
                break;
            default:
                if ($ret['result'] === 0) $sucRes = ['code' => 1, 'message' => '发送成功'];
                $errRes = ['code' => -1, 'msg' => $ret['errmsg']];
        }
        return $sucRes ?: $errRes;
    }

    public function __destruct()
    {
        if (!is_null($this->code_id)) {
            Cache::delete($this->code_id);
        }
    }
}