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
	评论接口
 */
class comment extends api
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
		$data = [
			'user_id'   => $_SESSION['userinfo']['user_id'],
			'video_id'  => router::$data['video_id'],
			'content'   => router::$data['content'],
			'nickname'  => $_SESSION['userinfo']['nickname'],
			'create_at' => time(),
		];
		$res = pdo_mysql::insert('jia_comment',$data);
		if($res){
			return errno::get(20001,0);
		}else{
			return errno::get(40003,1);
		}		
	}
	
}