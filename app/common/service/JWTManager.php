<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-02
 * Time: 9:23
 * Description:
 */

namespace app\common\service;

use Firebase\JWT\JWT;
use think\facade\Cache;

class JWTManager
{
    /**
     * @var int token到期余地时间
     */
    private static $leeway;
    /**
     * @var float|int token有效期
     */
    private static $ttl;
    /**
     * @var mixed 后台token有效期
     */
    private static $backend_ttl;
    /**
     * @var string 加密方式
     */
    private static $algo;
    /**
     * @var string 密钥
     */
    private static $secret;
    /**
     * @var int 当前时间戳
     */
    private $now;
    /**
     * @var mixed 参数
     */
    private $param;
    /**
     * @var int|mixed 类型
     */
    private $type;


    public function __construct($param, $type = 1)
    {
        $this->param = $param;
        $this->now = time();
        $this->type = $type;
        self::$leeway = env('jwt.leeway', 300);
        self::$ttl = env('jwt.ttl', 3600 * 4);
        self::$backend_ttl = env('jwt.backend_ttl', 3600 * 24);
        self::$algo = env('jwt.algo', 'HS256');
        self::$secret = env('jwt.secret', '8f7f6085cc180f39866dcaf362986e49');
    }

    /**
     * 发布token
     * @return string
     */
    public function issueToken()
    {
        return self::createToken();
    }

    /**
     * 创建token
     * @return string
     */
    public function createToken(): string
    {
        // 设置余地时间
        JWT::$leeway = self::$leeway;

        return JWT::encode(self::setParam(), self::$secret, self::$algo);
    }

    /**
     * 解析token
     * @return object
     */
    public function parseToken()
    {
        return JWT::decode($this->param, self::$secret, [self::$algo]);
    }

    /**
     * 设置有效荷载
     * @return array
     */
    public function setParam(): array
    {
        $token = [
            'jti' => md5(uniqid(env('admin.salt', 'tpl'), true) . time()),
            'iat' => $this->now // 发布时间
        ];
        if ($this->type == 1) {
            $token['exp'] = $this->now + self::$ttl;
        } else {
            $token['exp'] = $this->now + self::$backend_ttl;
        }

        return array_merge($token, $this->param);
    }

    /**
     * 过期token加入黑名单
     * @param $payload
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function addBlackList($payload)
    {
        $str = $payload->jti . '_' . $payload->iat;
        $list = Cache::store('file')->get('tokenBlackList', []);
        array_push($list, $str);
        Cache::store('file')->set('tokenBlackList', $list);
    }
}