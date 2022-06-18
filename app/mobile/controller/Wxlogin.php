<?php
namespace app\api\controller\index;

use app\BaseController;
use \think\facade\Db;
use \think\facade\Request;
use \think\facade\View;
use think\Cache;
//use app\R;

class wxlogin extends BaseController{
    const WX_APPID="";
    const WX_APPSECRET="";
	public function index(){
	    $data=request()->param();
		if(isset($data["code"])){
            $code=$data["code"];
            //$code=$_GET["code"];
            
            $this->wxlogin($code);
            
        }
		return "welcome to stxbm wx_login";
	}
    public function wxlogin($code){
         
         
        	$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".self::WX_APPID."&secret=".self::WX_APPSECRET."&code=".$code."&grant_type=authorization_code";
        	
     		$result=CURL($url);
     		$jsonObj=json_decode($result);
     		//$jsonARR=json_decode($result,true);
     		//error(1,"ccc",$jsonARR);
     		
     		$openid=$jsonObj->openid;
     		
     		//error(1,"aaa".$openid,$jsonARR);
            $res=db("user")->where("wx_id",$openid)->find();
            
     		if(!$res){
     			$h=$this->get_wx_h($jsonObj);
     	        $pic_name="st".time().randpw("6","NUMBER").".jpg";
     	        

     	        $this->saveImage($h["headimgurl"],APP_PATH."/../upload/head/".$pic_name);
     			$array=array(
     		        "wx_id"=>$openid,
     		        "nickname"=>$h["nickname"],
     		        "head"=>"head/".$pic_name,
     		        "create_time"=>time(),
     		    );
    			db("user")->insert($array);
    			
    			$res=db("user")->where("wx_id",$openid)->find();
    			
    			//$_SESSION["user"]=$res;
    			$data=[
    			    "user"=>$res,
    			    "url"=>"register.php"
    			    ];
    			//R::error(1,"新注册",$data);
                R::success("获取成功",$data);

     		}
            //else{
     		//     $data=[
    			//     "user"=>$res,
    			//     "url"=>"register.php"
    			//     ];
     		//     //$_SESSION["user"]=$res;
     		// 	if($res["id_card"] ==""){//未验证
     		// 	    R::error(1,"未验证身份证号",$data);
     		// 	 //   header("location:http://{$_SERVER['HTTP_HOST']}".ROOT_PATH."/register.php"); exit;
     		// 	}
     		// }
     		$data=[
        		    "user"=>$res,
        		    "url"=>"register.php"
    		   ];
    		R::success("获取成功",$data);
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
    public function saveImage($path, $image_name) {
         $img = file_get_contents ($path);
         file_put_contents($image_name,$img);
     }



}
