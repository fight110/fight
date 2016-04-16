<?php

class Runtime Extends BaseClass {
    private static $start, $last;
    private static $log = array();

    public function __construct(){
        $this->setFactory('runtime');
    }

    public function start(){
        STATIC::$start = STATIC::$last = microtime(true);
    }

    public static function log(){
        $now    = microtime(true);
        $time   = $now - STATIC::$last;
        STATIC::$log[]  = $time;
    }

    public function end(){
        $url    = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
        $domain = $_SERVER['HTTP_HOST'];
        $now    = microtime(true);
        $time   = $now - STATIC::$start;
        $more   = $_SERVER['REQUEST_URI'] . "===". implode(",", STATIC::$log);
        $User   = new User;
        $user_id    = $User->username;
        $username   = $User->name;
        $this->create(array("domain"=>$domain, 'user_id'=>$user_id, 'username'=>$username, "url"=>$url, "time"=>$time, "more"=>$more))->insert();
    }

    
}




