<?php

/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-03-30
 * Time: 09:50
 * Description:
 */

namespace app\api\controller\access;

use app\api\model\Member;
use app\api\model\MemberTree;
use app\common\controller\ApiController;
use app\common\service\EasyWechat;
use app\common\service\sms\SMS;

class Login extends ApiController
{

     public function ceshi(){
        //http://app15.com/api/v1.0/access/ceshi
        echo 0;exit;
     }
    /**
     * 短信登录
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sms_login(Member $member)
    {


        $post = $this->request->post();

        $member->valid($post, 'smsLogin');

        (new SMS())->verify($post['phone'], 5, $post['sms_code']);

        $find = $member
            ->where('phone', $post['phone'])
            ->field('member_id,phone,avatar,nickname,status,open_id,union_id,login_ip,login_time,invite_code')
            ->find();
        if (!$find) {
            abort(-1, '用户不存在');
        }
        if ($find['status'] == 0) {
            abort(-1, '账号已被禁用或注销');
        }

        $find->login_time = time();
        $find->login_ip = getRealIp();
        $find->save();

        $token = app('app\\common\\service\\JWTManager', [
            'param' => [
                'mid' => $find['member_id']
            ]
        ])->issueToken();
        header('token:' . $token);

        return apiShow([
            'mid'         => $find['member_id'],
            'phone'       => $find['phone'],
            'nickname'    => $find['nickname'],
            'avatar'      => $find['avatar'],
            'invite_code' => $find['invite_code']
        ], '登录成功');
    }


    /**
     * 短信登录
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wx_sms_login(Member $member)
    {


        $post = $this->request->post();

        $member->valid($post, 'smsLogin');

        (new SMS())->verify($post['phone'], 5, $post['sms_code']);

        $find = $member
            ->where('phone', $post['phone'])
            ->field('member_id,phone,avatar,nickname,status,open_id,union_id,login_ip,login_time,invite_code')
            ->find();

        if (!$find) {
            abort(-1, '用户不存在');
        }
        if ($find['status'] == 0) {
            abort(-1, '账号已被禁用或注销');
        }

        $find->login_time = time();
        $find->login_ip = getRealIp();
        $find->save();

        $token = app('app\\common\\service\\JWTManager', [
            'param' => [
                'mid' => $find['member_id']
            ]
        ])->issueToken();

        if($find["open_id"]==""){
            $member->where('member_id', $find['member_id'])->update(["open_id"=>$post["open_id"]]);
        }else{
            abort(-1, '账号已绑定微信,请先解绑');
        }
        
        header('token:' . $token);

        return apiShow([
            'mid'         => $find['member_id'],
            'phone'       => $find['phone'],
            'nickname'    => $find['nickname'],
            'avatar'      => $find['avatar'],
            'invite_code' => $find['invite_code']
        ], '登录成功');
    }

    /**
     * 微信登录
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wx_login(Member $member)
    {
        $post = $this->request->post();
        $code=$post["code"];
        //$member->valid($post, 'wx_login');
        $config = config('wechat');
        $JSAPI=$config["JSAPI"];
        //$find = (new EasyWechat('JSAPI'))->applet_info($post['code']);
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$JSAPI["app_id"]."&secret=".$JSAPI["secret"]."&code=".$code."&grant_type=authorization_code";
            
        $result=$this->curl($url);
        $jsonObj=json_decode($result);

            //$jsonARR=json_decode($result,true);
            //error(1,"ccc",$jsonARR);
            
        $openid=$jsonObj->openid;
//return apiShow($openid);
        $memberInfo = $member
            ->where('open_id', $openid)
            //->where('open_id', $find['open_id'])
            ->field('member_id,phone,avatar,nickname,status,open_id,union_id,login_ip,login_time,invite_code')
            ->find();
        if ($memberInfo && $memberInfo['status'] == 0) {
            abort(-1, '账号已被禁用或注销');
        }
        if ($memberInfo && !$memberInfo['open_id']) {
            $memberInfo->open_id = $find['open_id'];
            $memberInfo->save();
        }

        // 用户不存在执行注册
        if (!$memberInfo) {
            $h=$this->get_wx_h($jsonObj);
            return apiShow([
                // 'mid'         => $find['member_id'],
                // 'phone'       => $memberInfo['phone'],
                'nickname'    => $h['nickname'],
                'avatar'      => $h['headimgurl'],
                'open_id'     => $openid,
                // 'invite_code' => $memberInfo['invite_code'],
                // 'session_key' => $find['session_key']
            ], '用户不存在');

            // $memberInfo = $member::create([
            //     'nickname' => $post['nickname'],
            //     'avatar'   => $this->avatar_upload($post['avatar']),
            //     'open_id'  => $find['open_id'],
            //     'phone'    => ''
            // ]);

        } else {
            $memberInfo->login_time = time();
            $memberInfo->login_ip = getRealIp();
            $memberInfo->save();
        }

        $token = app('app\\common\\service\\JWTManager', [
            'param' => [
                'mid' => $memberInfo['member_id']
            ]
        ])->issueToken();
        header('token:' . $token);

        return apiShow([
            'mid'         => $memberInfo['member_id'],
            'phone'       => $memberInfo['phone'],
            'nickname'    => $memberInfo['nickname'],
            'avatar'      => $memberInfo['avatar'],
            'open_id'     => $memberInfo['open_id'],
            'invite_code' => $memberInfo['invite_code'],
            //'session_key' => $find['session_key']
        ], '登录成功');
    }

    /**
     * 联合登录
     * @param Member $member
     * @param SMS $sms
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function union_login(Member $member, SMS $sms)
    {
        $post = $this->request->post();
        $post['member_id'] = $this->deToken(0)->mid;

        $member->valid($post, 'union_login');

        $sms->verify($post['phone'], 1, $post['sms_code']);

        $find = $member
            ->where('member_id', $post['member_id'])
            ->field('member_id,phone,open_id,union_id,password,invite_code,avatar,nickname')
            ->find();
        if (!$find) {
            abort(-1, '用户已注销或被禁用');
        }

        $this->db->startTrans();

        $find->phone = $post['phone'];
        $find->password = $post['password'];
        $find->save();

        // 绑定分销关系
        $parent_id = $member->where('invite_code', $post['invite_code'] ?? '')->value('member_id', 0);
        $this->bind_relation($find['member_id'], $parent_id);

        $this->db->commit();

        return apiShow([
            'mid'         => $find['member_id'],
            'phone'       => $find['phone'],
            'nickname'    => $find['nickname'],
            'avatar'      => $find['avatar'],
            'open_id'     => $find['open_id'],
            'invite_code' => $find['invite_code'],
        ], '登录成功');
    }

    /**
     * 账号登录
     * @param Member $member
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function account_login(Member $member)
    {
        $post = $this->request->post();

        $member->valid($post, 'accountLogin');

        $find = $member
            ->where([
                ['phone', '=', $post['phone']],
                ['password', '=', encrypt($post['password'])]
            ])
            ->field('member_id,phone,open_id,union_id,password,invite_code,avatar,status,nickname')
            ->find();
        if (!$find) {
            abort(-1, '账号号密码不正确');
        }
        if (!$find['status']) {
            abort(-1, '账号已被禁用或注销');
        }

        $find->login_time = time();
        $find->login_ip = getRealIp();
        $find->save();

        $token = app('app\\common\\service\\JWTManager', [
            'param' => [
                'mid' => $find['member_id']
            ]
        ])->issueToken();
        header('token:' . $token);

        return apiShow([
            'mid'         => $find['member_id'],
            'phone'       => $find['phone'],
            'nickname'    => $find['nickname'],
            'avatar'      => $find['avatar'],
            'invite_code' => $find['invite_code']
        ], '登录成功');
    }


public function check_str($str){
    $res = preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9 _:：,，.。…\/、~`＠＃￥％＆×＋｜｛｝＝－＊＾＄～｀!@#$%^&*()\+-—=（）！￥{}【】\[\]\|\"\'’‘“”；;《》<>\?\？\·]+$/u', $str);
    return $res ? TRUE : FALSE;
}

    /**
     * 注册
     * @param Member $member
     * @param SMS $sms
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register(Member $member, SMS $sms)
    {
        $post = $this->request->post();
        $invite_code="";

        if(!$this->check_str( $post['nickname'])){
            return apiShow([], '昵称含有不合法字符', -1);
        }
        //正则匹配验证码
        $pattern = "/^[A-Za-z0-9]{6}-[1-3]$/";
        //return apiShow([], $post['nickname'], -1);
        if( $post['invite_code'] !=""){
            // list($invite_code,$platform_id)=explode("-", $post['invite_code']);
            // if(!preg_match($pattern, $invite_code)){
            //     return apiShow([], '邀请码格式不正确', -1);
            // }
            
            // $post['invite_code']=$invite_code;
            $invite_code=$post['invite_code'];
        }
        

        $member->valid($post, 'register');


        $sms->verify($post['phone'], 1, $post['sms_code']);

        $this->db->startTrans();

        //nickname不重复
        //return apiShow([], '测试', -1);
        $nick=$member
            ->where('nickname', $post['nickname'])
            ->find();

        if($nick){
            return apiShow([], '昵称已存在', -1);
        }


        // 绑定分销关系
        if($invite_code!=""){
            //$parent_id = $member->where('invite_code', $post['invite_code'] ?? '')->value('member_id', 0);
            $parent = $member
            ->field("member_id,parent_id,parent2_id")
            ->where('nickname', $post['invite_code'])
            ->find();
            //->value('member_id', 0);
            // if($parent_id>0){
            //     $parent2_id = $member->where('member_id', $parent_id)->value('member_id', 0);
            // }
            // 
            //不自动添加平台了
            //$parent_id=$parent["member_id"];
           // $this->bind_relation($create['member_id'], $parent_id,$platform_id);
           
           if($parent){
                $post["parent_id"]=$parent["member_id"];
                $post["parent2_id"]=$parent["parent_id"];
           }

        }
        
        $create = $member::create($post);
        $this->db->commit();
        return apiShow([], '注册成功', 1);
    }

    /**
     * 忘记密码
     * @param Member $member
     * @param SMS $sms
     * @return array|\think\response\Json
     */
    public function forgetPwd(Member $member, SMS $sms)
    {


        $post = $this->request->post();

        $member->valid($post, 'forgetPwd');

        $sms->verify($post['phone'], 2, $post['sms_code']);

        $member::update(['password' => $post['password']], ['phone' => $post['phone']]);

        return apiShow([], '修改成功', 1);
    }

    /**
     * 绑定关系
     * @param $member_id
     * @param $parent_id
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function bind_relation($member_id, $parent_id,$platform_id)
    {
        $memberTreeModel = new MemberTree();

        if ($parent_id) { // 存在父级
            $saveData = [];
            // 父级
            $saveData[] = [
                'member_id' => $member_id,
                'parent_id' => $parent_id,
                'platform_id'=> $platform_id,
                'level'     => 1
            ];

            // 查出所有父级的上级
            // $superior = MemberTree::where('member_id', $parent_id)->where('platform_id',$platform_id)->select();
            // if (!$superior->isEmpty()) {
            //     foreach ($superior as $item) {
            //         if ($item['parent_id']) {
            //             $saveData[] = [
            //                 'member_id' => $member_id,
            //                 'parent_id' => $item['parent_id'],
            //                 'level'     => $item['level'] + 1,
            //                 'platform_id'=> $platform_id
            //             ];
            //         }
            //     }
            // }
            // $memberTreeModel->saveAll($saveData);
            $superior = MemberTree::where('member_id', $parent_id)->where('platform_id',$platform_id)->find();
            if (!$superior->isEmpty()) {
                //foreach ($superior as $item) {
                if ($superior['parent_id']) {
                    $saveData[] = [
                        'member_id' => $member_id,
                        'parent_id' => $superior['member_id'],
                        'parent2_id' => $superior['parent_id'],
                        'level'     => $superior['level'] + 1,
                        'platform_id'=> $platform_id
                    ];
                }
                //}
            }
            $memberTreeModel->saveAll($saveData);

        } else { // 不存在父级
            $memberTreeModel::create([
                'member_id' => $member_id,
                'parent_id' => 0,
                'parent2_id' => 0,
                'level'     => 0,
                'platform_id'=> $platform_id
            ]);
        }
    }

    /**
     * 上传头像
     * @param $url
     * @return bool|string
     */
    private function avatar_upload($url)
    {
        if (!$url) {
            return false;
        }
        // 设置运行时间为无限制
        set_time_limit(0);
        $url = trim($url);
        $curl = curl_init();
        // 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_URL, $url);
        // 设置header
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // https请求 不验证证书 其实只用这个就可以了
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //https请求 不验证HOST
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //执行速度慢，强制进行ip4解析
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        // 运行cURL，请求网页
        $file = curl_exec($curl);
        // 关闭URL请求
        curl_close($curl);
        // 将文件写入获得的数据
        $filename = './avatar/' . time() . rand() . ".jpg";
        $dir = iconv("UTF-8", "GBK", './avatar/' . date('Ymd'));
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        $write = @fopen($filename, "w");
        fwrite($write, $file);
        fclose($write);
        $oss = app('app\\common\\service\\OSS');
        // 上传 oss
        $ossCode = $oss->fileUpload('avatar/file/' . date('Ymd') . '/' . substr($filename, 9), root_path() . 'public/' . substr($filename, 2));
        if ($ossCode['code'] == 0) {
            // 删除本地文件
            unlink(root_path() . 'public/' . substr($filename, 2));
            return 'avatar/file/' . date('Ymd') . '/' . substr($filename, 9);
        } else {
            return false;
        }
    }

    public function return_oauth2(){
        //oauth2
        $post = $this->request->param();
        $config = config('wechat');
        $JSAPI=$config["JSAPI"];
       
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$JSAPI["app_id"]."&redirect_uri=".$post['baseUrl']."&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
        //  echo $url;
        // exit;
        return apiShow(["url"=>$url]);

    }

    public function curl($url){
       // $url = "http://git.oschina.net/yunluo/API/raw/master/notice.txt";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $notice = curl_exec($ch);

        return $notice;
    }

    public function get_wx_h($jsonObj){
        $openid = $jsonObj->openid;
        $access_token = $jsonObj->access_token;
        $url ="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid;
        $res = file_get_contents($url);
        $json = json_decode($res,true);//这里是将返回过来的json对象转成数组
        $headimgurl = $json['headimgurl'];
        $nickname = $json['nickname'];
        

        return $json;
    
     }
}
