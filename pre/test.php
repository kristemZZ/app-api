<?php

namespace pre;
use core\ctr\router;
use app\api;
/**
* 
*/
class test
{
	public static $tz = [
		'load' => [],
	];

	public static function run(){
		router::$data = [
			'token'=>'111',
		];
	}
	public static function load(){
		
	}
}