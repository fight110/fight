<?php

class Control_index {
    public static function Action_index($r){
        $ua = new UserAgentInfo();
        if($ua->is_mobile()){
                $platform       = "iphone";
        }else{
                $platform       = "ipad";
        }

        Flight::redirect("http://{$platform}.haodingtong.com");
    }
}