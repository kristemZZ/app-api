<?php
namespace app\ctr;

use app\api;
use core\ctr\router;
use ext\redis;
use ext\pdo_mysql;
use ext\errno;

/**
 *  发送短信
 */
class sendSms extends api
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
    	parent::sendCode(router::$data['phone']);
        
    }

    
    
    
}
