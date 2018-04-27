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
	上传视频接口
 */
class video extends api
{
	public static $page = 10; //分页显示数
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
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'POST':
				//视频上传 前端sdk上传，放回视频地址信息
				$data = [
					'title'      => $data['title'],
					'url'        => $data['url'],
					'user_id'    => $_SESSION['userinfo']['user_id'];
 					'create_at'  => time(),
				];
				$res = pdo_mysql::insert('jia_video',$data);
				if($res){
					return errno::get(20102,0);
				}else{
					errno::get(40002,1);
				}
				break;
			case 'GET':
				//视频查询  分页
				$p = isset(router::$data['p']) ? router::$data['p'] :1;
				$offset = (int)($p-1)*self::$page;
				$sql = "select * from jia_video order by create_at desc limit ".$offset.",".self::$page;
				$info = pdo_mysql::query($sql);
				return ['err'=>0,'code'=>200,'video'=>$info];
				break;
		}		
	}
	
}