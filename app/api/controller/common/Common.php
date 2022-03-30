<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-01
 * Time: 13:17
 * Description:
 */

namespace app\api\controller\common;


use app\common\controller\ApiController;
use app\common\model\FileManage;
use app\common\model\Member;
use think\response\Json;

class Common extends ApiController
{
    /**
     * 公共上传接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function upload(): Json
    {
        $param = $this->request->post();
        $file = $this->request->file('file');

        $view = $semipath = '';

        if ($file) {
            // 文件哈希值
            $sha1 = $file->hash('sha1');

            $exist = FileManage::where('sha1', $sha1)->field('file_id,url')->find();

            // 文件后缀名
            $ext = $file->extension();
            // 文件类型信息
            $mime = $file->getOriginalMime();
            // 文件真实路径
            $realPath = $file->getRealPath();
            // 文件大小
            $size = $file->getSize();

            // 禁止上传PHP和HTML文件
            if (in_array($mime, ['text/x-php', 'text/html']) || in_array($ext, ['php', 'html'])) {
                abort(-1, '不能上传该类型文件');
            }
            // 图片类型获取宽高
            if (in_array($mime, ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png',
                    'image/webp']) || in_array($ext, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
                list($image_width, $image_height) = getimagesize($realPath);
            }

            switch (sysconfig('upload', 'upload_type')) {
                case 'alioss':
                    // oss前缀
                    $ossPrefix = sysconfig('upload', 'alioss_domain');
                    // 全路径
                    $view = $ossPrefix . ($exist['url'] ?? '');
                    // 半路径
                    $semipath = $exist['url'] ?? '';
                    // 文件上传OSS
                    if (!$exist) {
                        $semipath = ($param['type'] ?? 'file') . '/' . $file->hashName();
                        $ossManage = app('app\\common\\service\\OSS');
                        $ossManage->fileUpload($semipath, $realPath);
                        $view = $ossPrefix . $semipath;
                    }
                    break;
                case 'qnoss':
                case 'txcos':
                    break;
                default: // local
                    // 域名前缀
                    $domainPrefix = $this->request->domain();
                    // 全路径
                    $view = $domainPrefix . ($exist['url'] ?? '');
                    // 半路径
                    $semipath = $exist['url'] ?? '';
                    // 文件上传本地
                    if (!$exist) {
                        // 文件夹名
                        $dirname = '/media/' . ($param['type'] ?? 'file') . '/' . date('Ymd');
                        // 文件名
                        $filename = md5((string)microtime(true)) . ".{$ext}";
                        $semipath = $dirname . '/' . $filename;
                        $file->move(public_path() . $dirname, $filename);
                        $view = $domainPrefix . $semipath;
                    }
            }
            if (!$exist) {
                // 存入文件管理表
                FileManage::create([
                    'url'         => $semipath,
                    'width'       => $image_width ?? $this->db->raw('null'),
                    'height'      => $image_height ?? $this->db->raw('null'),
                    'type'        => $ext,
                    'size'        => round($size / 1024, 1),
                    'mime'        => $mime,
                    'sha1'        => $sha1,
                    'upload_type' => sysconfig('upload', 'upload_type')
                ]);
            }
        }
        return apiShow(['url' => $semipath, 'view_url' => $view], '上传成功');
    }

    /**
     * 编辑器文件上传
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function uploadEditor(): Json
    {
        $data = [
            'upload_type' => $this->request->post('upload_type'),
            'file'        => $this->request->file('upload')
        ];
        $uploadConfig = sysconfig('upload');
        !$data['upload_type'] && $data['upload_type'] = $uploadConfig['upload_type'];
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        $this->validate($data, $rule);

        $view = $semipath = '';

        // 上传
        if ($data['file']) {
            // 文件哈希值
            $sha1 = $data['file']->hash('sha1');

            $exist = FileManage::where('sha1', $sha1)->field('file_id,url')->find();

            // 文件后缀名
            $ext = $data['file']->extension();
            // 文件类型信息
            $mime = $data['file']->getOriginalMime();
            // 文件真实路径
            $realPath = $data['file']->getRealPath();
            // 文件大小
            $size = $data['file']->getSize();

            // 图片类型获取宽高
            if (in_array($mime, ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png',
                    'image/webp']) || in_array($ext, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
                list($image_width, $image_height) = getimagesize($realPath);
            }

            switch (sysconfig('upload', 'upload_type')) {
                case 'alioss':
                    // oss前缀
                    $ossPrefix = sysconfig('upload', 'alioss_domain');
                    // 全路径
                    $view = $ossPrefix . ($exist['url'] ?? '');
                    // 半路径
                    $semipath = $exist['url'] ?? '';
                    // 文件上传OSS
                    if (!$exist) {
                        $semipath = ($param['type'] ?? 'file') . '/' . $data['file']->hashName();
                        $ossManage = app('app\\common\\service\\OSS');
                        $ossManage->fileUpload($semipath, $realPath);
                        $view = $ossPrefix . $semipath;
                    }
                    break;
                case 'qnoss':
                case 'txcos':
                    break;
                default: // local
                    // 域名前缀
                    $domainPrefix = $this->request->domain();
                    // 全路径
                    $view = $domainPrefix . ($exist['url'] ?? '');
                    // 半路径
                    $semipath = $exist['url'] ?? '';
                    // 文件上传本地
                    if (!$exist) {
                        // 文件夹名
                        $dirname = '/media/' . ($param['type'] ?? 'file') . '/' . date('Ymd');
                        // 文件名
                        $filename = md5((string)microtime(true)) . ".{$ext}";
                        $semipath = $dirname . '/' . $filename;
                        $data['file']->move(public_path() . $dirname, $filename);
                        $view = $domainPrefix . $semipath;
                    }
            }
            if (!$exist) {
                // 存入文件管理表
                FileManage::create([
                    'url'         => $semipath,
                    'width'       => $image_width ?? $this->db->raw('null'),
                    'height'      => $image_height ?? $this->db->raw('null'),
                    'type'        => $ext,
                    'size'        => round($size / 1024, 1),
                    'mime'        => $mime,
                    'sha1'        => $sha1,
                    'upload_type' => sysconfig('upload', 'upload_type')
                ]);
            }
        }

        return json([
            'error'    => [
                'message' => '上传成功',
                'number'  => 201,
            ],
            'fileName' => '',
            'uploaded' => 1,
            'url'      => $view
        ]);
    }

    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        $param = $this->request->post();
        return app('app\\common\\service\\JWTManager', [
            'param' => ['mid' => $param['member_id'], 'dev_type' => $param['dev_type'] ?? 1]
        ])->issueToken();
    }

    /**
     * 发送短信
     * @return array|Json
     */
    public function send_sms()
    {
        $param = $this->request->post();

        if ($param['type'] == 1) {
            (new Member())->is_exist($param['phone'], '手机号已被注册');
        }

        $sms = app('app\\common\\service\\sms\\SMS', [$param['phone'], $param['type']], true);
        $sms->send();

        return apiShow([], '发送成功', 1);
    }
}