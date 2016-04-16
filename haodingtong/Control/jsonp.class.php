<?php

class Control_jsonp {
    public static function Action_info ($r) {
        $_callback  = $r->query->callback;
        $User       = new User;
        $userinfo   = $User->getAttribute();
        $result['is_auth']  = $userinfo['id']   ? 1 : 0;
        // $result['userinfo'] = $userinfo;
        echo $_callback . "(" . json_encode($result) . ")";
    }
}