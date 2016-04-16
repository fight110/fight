<?php

class Control_index {
    public static function Action_index($r){
        $ua = new UserAgentInfo();
        if($ua->is_mobile()){
                $platform       = "iphone";
        }else{
                $platform       = "ipad";
        }

        // $ua_save        = new ConfigData("UserAgentAnalysis");
        // $HTTP_USER_AGENT    = $_SERVER['HTTP_USER_AGENT'];
        // $ua_save->set($HTTP_USER_AGENT, $ua_save->get($HTTP_USER_AGENT) + 1);

        Flight::redirect("http://{$platform}.haodingtong.com");
    }

    public static function Action_local_app_haodingtong($r){
    }
}