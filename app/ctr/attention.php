<?php
namespace app\ctr;

use app\api;
use core\ctr\router;
use ext\errno;
use ext\pdo_mysql;
use ext\redis_session;
use ext\redis;
use ext\crypt;

/*
	加关注接口
 */
class attention extends api
{
	public static $tz = [
        'index'=>[],
    ];
    /**
     * Initial api
     */
    public static function init() 
    {
    	redis_session::start();
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
		switch (router::$data['type']) {
			case '0':
				//关注
				$data = [
					'user_id'   => $_SESSION['userinfo']['user_id'],
					'parent_id'  => router::$data['parent_id'],
				];
				$res = pdo_mysql::insert('jia_attention',$data);
				if($res){
					return errno::get(20103,0);
				}else{
					return errno::get(40004,1);
				}	
				break;
			
			case '1':
				//取消取消关注
				$data = [
					'user_id'   => $_SESSION['userinfo']['user_id'],
					'parent_id'  => router::$data['parent_id'],
				];
				pdo_mysql::delete('jia_attention',$data);
				break;
		}
			
	}
	
}