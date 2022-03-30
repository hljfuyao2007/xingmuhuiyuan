<?php

namespace app;

use app\common\traits\JumpTrait;
use think\db\exception\PDOException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\db\PDOConnection;
use think\exception\ErrorException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    use JumpTrait;

    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        $result = ['code' => $e->getCode() ?: -999, 'msg' => $e->getMessage()];

        switch ($e) {
            case $e instanceof ValidateException:
                $result['code'] = -1;
                break;
            case $e instanceof PDOException || $e instanceof PDOConnection:
                $result = ['code' => -888, 'msg' => $e->getMessage()];
                break;
            case $e instanceof ErrorException:
                $result['code'] = -100;
                break;
            case $e instanceof HttpException:
                $result['code'] = $e->getStatusCode() ?? -100;
                break;
            default:
                // 其他错误交给系统处理
                return parent::render($request, $e);
        }
        $this->app->db->rollback();
        if (stripos($request->baseUrl(), '/admin/') === 0) {
            return $this->error([], $result['msg'], $result['code']);
        }
        Response::create($result, 'json')->send();
        die();
    }
}
