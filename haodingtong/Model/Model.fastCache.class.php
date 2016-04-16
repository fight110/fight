<?php

require_once DOCUMENT_ROOT . "lib/Cache/phpfastcache/phpfastcache.php";
if(!defined('CACHE_PATH')) {
	define('CACHE_PATH', DOCUMENT_ROOT . 'cache/');
}

class fastCache {
	private static $_cache = null;
	public static function getInstance($type='files'){
		if(!self::$_cache){
			switch ($type) {
				default : 
					$cache = phpFastCache('files');
					$cache->option('path', CACHE_PATH);
			}
			self::$_cache = $cache;
		}
		return self::$_cache;
	}
	public static function cache($cache_id, $cache_func, $options=array('type'=>'files', 'timeout'=>60)){
		$cache 	= self::getInstance($options['type']);
		$data 	= $cache->get($cache_id);
		if($data === null){
			$data 	= $cache_func();
			$cache->set($cache_id, $data, $options['timeout']);
		}
		return $data;
	}
}