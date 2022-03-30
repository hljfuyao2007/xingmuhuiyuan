<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-05
 * Time: 21:50
 * Description:
 */

namespace app\common\service\encrypt;


class AES
{
    /**
     * @var bool 是否开启加密
     */
    private static $debug;
    /**
     * @var string 密钥
     */
    private static $secret;
    /**
     * @var string iv向量
     */
    private static $iv;
    /**
     * @var string 加密方法
     */
    private static $method;

    public function __construct()
    {
        static::$debug = config('encrypt.debug');
        static::$secret = config('encrypt.aes.secret');
        static::$iv = config('encrypt.aes.iv');
        static::$method = config('encrypt.aes.method');
    }

    /**
     * 加密
     * @param $data
     * @return false|string
     */
    public function encrypt($data)
    {
        return openssl_encrypt($data, static::$method, static::$secret, 0, static::$iv);
    }

    /**
     * 解密
     * @param $data
     * @return false|string
     */
    public function decrypt($data)
    {
        return openssl_decrypt($data, static::$method, static::$secret, 0, static::$iv);
    }
}