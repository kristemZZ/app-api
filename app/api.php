<?php

namespace app;

use core\ctr\router;
use ext\crypt;
use ext\redis_session;
use ext\redis;
use ext\errno;
use ext\pdo_mysql;
use app\help\sendSms;
class api
{
    public static $tz = [
        'check'=>[],
    ];
    // sign lifetime
    public static $lifetime = 7*24*60*60;
    // db_mysql
    public static $db_mysql;
    /**
     * Initial api
     */
    public static function init() 
    {
        redis_session::start();
        errno::load('app','error');
    }

    /**
     * Run api
     *
     * @param array $arr
     */
    public static function check() :array
    {
        if(isset(router::$data['sign'])) $s = self::check_sign(router::$data['sign']);
        if(isset(router::$data['token'])) $s = self::check_token(router::$data['token']);
        if(empty(router::$data)) $s = errno::get(401,1);
        if(isset($s)) return $s;
    }
    protected static function check_sign(string $sign) :array
    {
        // redis_session::start();
        $string = crypt::verify($sign);
        $info = json_decode($string,true);  //解码后的json数据
        if(!isset($info)) return errno::get(401,1); 
        $key = $_SESSION['sign']; //保存在内存中的用户信息
        if(!$key) return errno::get(403,1); //登陆失效
        if($key !== $sign) {
            return errno::get(401,1); //sign错误
        }else{
            if((time()-$info['iat']) <= 2){  //api 两秒有效
                //刷新sign有效期
                $_SESSION['sign'] = $sign;
                return errno::get(200,0);
            }else{
                return errno::get(401,1); //sign失效
            }
        } 
    }
    protected static function check_token(string $token) :array
    {
        if($token === hash('sha256', crypt('jiayuanapp--','jjmz'))){   //约定游客登陆token令牌
            return errno::get(200,0);
        }else{
            return errno::get(401,1);
        }
    }
    
    protected static function csign(string $userid,string $session_id = '0') :string
    {
        
        $time = time();
        $data = [
            'id'    => $userid,
            'iat'   => $time,
            'exp'   => ((int)$time+(int)(self::$lifetime)),
            'sid'   => $session_id,
        ];
        $sql = "SELECT * FROM jia_users where user_id=".$userid;
        $info = pdo_mysql::query($sql);
        $_SESSION['userinfo'] = $info[0];  //保存登录信息
        $string = json_encode($data);
        return crypt::sign($string);
    }
    /**
     * [verifySmsCode description]
     * @param  string $phone [description] 手机号码
     * @param  string $code   [description] 前端传入的验证码
     * @return [type]         [description]
     */
    public static function verifySmsCode(string $phone, string $code) : bool
    {
        $redis = redis::connect();
        $string = (string)$redis->get('code_'.$phone);
        if ( $string === $code) return true;
        return false;
    }

    /**
     * [sendCode description]
     * @param  string $phoneNumber [description] phone number
     * @return [type]              [description]
     */
    public static function sendCode(string $phoneNumber) :bool
    {
        $code = rand(1000,9999); //生成验证码
        //发送短信
        self::sendSms($phoneNumber,$code);
        //保存redis
        $redis = redis::connect();
        $redis->set('code_'.$phoneNumber,$code,60);
        return true;
    }

    /**
     * [sendSms description] 阿里云短信
     * @param  string $phoneNumber [description] 
     * @param  string $code        [description] 验证码
     * @return [type]              [description] 布尔型
     */
    public static function sendSms(string &$phoneNumber,string &$code) : bool
    {
        
        $content = sendSms::send($phoneNumber,$code);
        if(!$content) {
            self::sendSms($phoneNumber,$code);
        }else{
            return true;
        }
    }

    public static function password(string $string) :string
    {
        return hash('md5',$string);
    }


    /*
        获取访问ip
     */
    public static function getRealIp()
    {
        $ip=false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
                    $ip = $ips[$i];
                        break;
                    }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
}