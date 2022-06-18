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
use think\facade\Db;
use \QRcode;
require_once "../extend/phpqrcode/phpqrcode.php";
// require_once "../phpqrcode/WxPay/WxPay.Config.php";
// require_once "../phpqrcode/WxPay/WxPay.JsApiPay.php";
// require_once "../phpqrcode/WxPay/log.php";

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

    /**
     * 发送短信(登录)
     * @return array|Json
     */
    public function send_sms_login()
    {
        $param = $this->request->post();

        if ($param['type'] != 1) {
            (new Member())->is_exist($param['phone'], '手机号未注册');
        }

        $sms = app('app\\common\\service\\sms\\SMS', [$param['phone'], $param['type']], true);
        $sms->send();

        return apiShow([], '发送成功', 1);
    }




    /**
     * 上传头像
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function upload_avatar(): Json
    {
        $param = $this->request->param();
        //var_dump($param);
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

        Db::name("member")->where("member_id",$param['mid'])->update(["avatar"=>$semipath]);
        return apiShow(['url' => $semipath, 'view_url' => $view], '上传成功');
    }




    public function qr()
    {
        header("Content-type:image/png");
        $param = $this->request->param();
        $data = $param["qr"];

        // 二维码容错率
        $level = 'L';

        // 二维码大小
        $size = 4;
        // 参数传递
        $QR = \QRcode::png($data, false, $level, $size);
        $imagestring = base64_encode(ob_get_contents());
        ob_end_clean();
        // 返回
        file_put_contents("abc".date("YmdHis")."png", 'data:image/png;base64,' .$imagestring);
        return json('data:image/png;base64,' . $imagestring);
    }


    public function fugai($QR,$code){
        
        $code_path = runtime_path('qrcode').$code.time().'.png';
        $dst_path = root_path('public')."share.png";//背景图片路径
        $src_path = $QR;//覆盖图
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dst_path));
        $src = imagecreatefromstring(file_get_contents($src_path));
        //获取覆盖图图片的宽高
        list($src_w, $src_h) = getimagesize($src_path);
        
        //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
        imagecopymerge($dst, $src, 230, 640, 0, 0, $src_w, $src_h, 100);    //位置可以自己调试
 
        imagepng($dst,$code_path);//根据需要生成相应的图片
        imagedestroy($dst);
        imagedestroy($src);
 
        $image = imagecreatefrompng($code_path);
 
        $font = root_path('public').'MSYH.TTF'; // 字体文件
        $color = imagecolorallocate($image,0,0,0); // 文字颜色
        imagettftext($image, 40, 0, 250, 600, $color, $font, $code); // 创建文字
        imagepng($image,$code_path);
        
        return $code_path;
 
    }


    public function haibao(){
        header("Content-type:image/png");
        $param = $this->request->param();
        $data = $param["qr"];
        // 二维码容错率
        $level = 'L';
        // 二维码大小
        $size = 6;
        $src_w=260;$src_h=260;
        // 参数传递
        $QR = \QRcode::png($data, false, $level, $size);
        $imagestring = base64_encode(ob_get_contents());
        ob_end_clean();

        //判断是不是代理 用不同的背景图 
        $dl=Db::name("member")->where("member_id",$param["member_id"])->value("is_agency");
        if($dl == 1){
            $dst_path = root_path('public')."/static/mobile/img/background.jpg";//背景图片路径
        }else{
            $dst_path = root_path('public')."/static/mobile/img/background2.jpg";//背景图片路径
        }
        $dst = imagecreatefromstring(file_get_contents($dst_path));
        $src = imagecreatefromstring(base64_decode($imagestring));
        //list($src_w, $src_h) = getimagesize($imagestring);
        //获取覆盖图图片的宽高
        //list($src_w, $src_h) = getimagesize($src_path);
        
        //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
        imagecopymerge($dst, $src, 50, 1000, 0, 0, $src_w, $src_h, 80);    //位置可以自己调试


        $code_path="code_".date("YmdHis")."png";
        imagepng($dst,$code_path);//根据需要生成相应的图片
        imagedestroy($dst);
        imagedestroy($src);
 
        $image = imagecreatefrompng($code_path);
        

        $r=$this->base64EncodeImage($code_path);
        imagedestroy($image);
        unlink($code_path);
        // $font = root_path('public').'MSYH.TTF'; // 字体文件
        // $color = imagecolorallocate($image,0,0,0); // 文字颜色
        // imagettftext($image, 40, 0, 250, 600, $color, '', $code); // 创建文字
        //imagepng($image,$code_path);
        return $r;
 
    }

    public function base64EncodeImage ($image_file) {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }

}