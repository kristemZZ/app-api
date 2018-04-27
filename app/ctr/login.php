<?php
namespace app\ctr;

use app\api;
use core\ctr\router;
use ext\errno;
use ext\pdo_mysql;
use ext\redis_session;
use ext\redis;
use ext\crypt;
class login extends api
{
	public static $tz = [
        'index'=>[],
    ];
    /**
     * Initial api
     */
    public static function init() 
    {
        parent::init();
        $res = parent::check();
        if($res['err'] !== 0)
        {
        	self::$tz = [];
        	return $res;
        }
    }
	public static function index() 
	{
		redis_session::start();
		$sid = session_id();
		$data = router::$data;
		if(isset($data['openid']) && isset($data['unionid'])){
			//第三方登陆
			$sql = "select * from jia_users where openid = ? and unionid = ?"; //预处理防止sql注入
			$option = [trim($data['openid']),trim($data['unionid'])];
			$info = pdo_mysql::query($sql,$option);
			if($info){
				$sign = parent::csign($info[0]['user_id'],$sid);
			}else{
				//用户不存在 直接注册
				//第三方注册
	            $info = [
	                'openid'     => $data['openid'],
	                'unionid'    => $data['unionid'],
	                'nickname'   => $data['nickname'],
	                'type'       => $data['type'],
	                'create_at'  => time(),
	                'ip'         => self::getRealIp(),
	            ];
	            pdo_mysql::begin();
	            $lastId = '';
	            $res = pdo_mysql::insert('jia_users',$info,$lastId);
	            $info_d = [
	                'user_id'   => $lastId,
	                'header'    => $data['header'],
	            ];
	            $res_d = pdo_mysql::insert('jia_userinfo',$info_d);
	            if($res && $res_d) {
	            	//注册成功
	                pdo_mysql::commit();
	                $sign = parent::csign($lastId,$sid);
	                return errno::get('20101','0');
	            }else{
	            	//注册失败
	                pdo_mysql::rollback();
	                return errno::get('40001','1');
	            }
			}
		}
		if(isset($data['phone'])){
			//手机登陆
			$sql = "select * from jia_users where phone = ?";
			$option = [
				$data['phone'],
			];
			$info = pdo_mysql::query($sql,$option);
			// var_dump($info);
			if(!empty($info)){
				if($info[0]['password'] === self::password($data['password'])) {
					$sign = parent::csign($info[0]['user_id'],$sid);
				}else{
					return errno::get('40102','1'); //密码错误
				}
				
			}else{
				return errno::get('40401','1'); //用户不存在 跳转到注册
			}
		}
		//将sign写入redis
		if(isset($sign)){
			$_SESSION['sign'] = $sign;
			return ['err'=>0 ,'code'=>200 ,'sign'=>$sign];
		}
	}
	
}