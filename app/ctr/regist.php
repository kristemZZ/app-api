<?php
namespace app\ctr;

use app\api;
use core\ctr\router;
use ext\redis;
use ext\pdo_mysql;
use ext\errno;

class regist extends api
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
        	return errno::get(401,1);
        }
    }
    public static function index()
    {
    	
        $data = router::$data;
        $redis = redis::connect();
        if(isset($data['phone'])){
            //验证短信验证码 手机号，密码注册
            if($data['code'] === $redis->get('code_'.$data['phone'])){
                //入库
                $info = [
                    'phone'     => $data['phone'],
                    'password'  => self::password($data['password']),
                    'create_at' => time(),
                    'ip'        => self::getRealIp(),
                    'type'      => 0,
                ];
                $lastId = '';
                $res = pdo_mysql::insert('jia_users',$info,$lastId);
                if($res){
                    return errno::get('20101','0');
                }else{
                    return errno::get('40001','1');
                }
            } 
        }
        // if(isset($data['openid']) && isset($data['unionid'])){
        //     //第三方注册
        //     $info = [
        //         'openid'     => $data['openid'],
        //         'unionid'    => $data['unionid'],
        //         'nickname'   => $data['nickname'],
        //         'type'       => $data['type'],
        //         'create_at'  => time(),
        //         'ip'         => self::getRealIp(),
        //     ];
        //     pdo_mysql::begin();
        //     $lastId = '';
        //     $res = pdo_mysql::insert('jia_users',$info,$lastId);
        //     $info_d = [
        //         'user_id'   => $lastId,
        //         'header'    => $data['header'],
        //     ];
        //     $res_d = pdo_mysql::insert('jia_userinfo',$info_d);
        //     if($res && $res_d) {
        //         pdo_mysql::commit();
        //         return errno::get('20101','0');
        //     }else{
        //         pdo_mysql::rollback();
        //         return errno::get('40001','1');
        //     }
        // }
    }

    
    
    
}
