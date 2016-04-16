<?php

class Control_logout {
    public static function Action_index($r){
        $User   = new User;
        $id     = $User->id;

        SESSION::delete('user');
        SESSION::delete('editor');
        $user_session   = UserSession::getInstance();
        $user_session->logout();

        $returl = $r->query->returl;
        if(!$returl){
            $returl     = $r->referrer;
        }

        if(!$returl || $returl =="/logout") $returl = "/";
        SESSION::destory();
        Flight::redirect($returl);
    }

}
