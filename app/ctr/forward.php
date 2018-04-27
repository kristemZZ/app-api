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
	转发数
 */
class forward extends api
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
		$sql = "UPDATE jia_video set forward_number = forward_number+1 where id=".router::$data['video_id'];
		pdo_mysql::query($sql);
			
	}
	
}