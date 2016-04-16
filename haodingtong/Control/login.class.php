<?php
class Control_login {
    public static function Action_index($r){
        // if(PROJECT_HTTP_HOST != "admin.haodingtong.com"){
        //     $returl     = $r->query->returl ? $r->query->returl : "/";
        //     Flight::redirect($returl);
        //     exit;
        // }
        $Company            = new Company;
        $result['company']  = $Company->getData();
        $Domain = new Domain;
        $domain_list = $Domain->get_list("status=1");
        $result['domain_list']    =$domain_list;
        Flight::display('login/index.html', $result);
    }

    public static function Action_validate($r){
        $auth   = new Auth($r->query->uname, $r->query->pword);
        $result = $auth->validate();
        if($result['valid']) {
            $user           = $auth->user;
            $user_id        = $auth->user['id'];
            $user_session   = UserSession::getInstance();
            $user_session->login($user_id);
            SESSION::set("user", $user);
            if($user['type'] == 0) {
                SESSION::set("editor", $user);
            }
        }
        $callback = $r->query->callback;
        $result['SID']  = session_id();//$_COOKIE['SID'];
        if($callback) {
            echo  $callback. "(".json_encode($result).")";
        }else{
            Flight::json($result);
        }
    }
    
    public static function Action_validate_jsonp($r){
        $auth   = new Auth($r->query->uname, $r->query->pword);
        $result = $auth->validate();
        echo $r->query->callback . "(".json_encode($result).")";
    }
    
    public static function Action_kick_others ($r) {
        $user_session   = UserSession::getInstance();
        $user_session->kick_others();
    }

    // public static function Action_validate_jsonp($r){
    //     $auth   = new Auth($r->query->uname, $r->query->pword);
    //     $result = $auth->validate();
    //     echo $r->query->callback . "(".json_encode($result).")";
    // }

    public static function Action_is_logined($r){
        $User           = new User;
        $u              = $User->findone("username='".addslashes($r->query->uname)."'");
        $result         = array();
        if($u['type'] == 1){
            $Devicelist     = new Devicelist;
            $userlist       = $Devicelist->find("key1='".addslashes($r->query->uname)."'");
            if(count($userlist)){
                //$result['message']  = "帐号已登入，是否踢出？";
            }
        }
        if($r->query->callback) {
            echo $r->query->callback . "(".json_encode($result).")";
        }else{
            Flight::json($result);
        }

        //Flight::json($result);
    }

    public static function Action_kill($r){
        $Devicelist     = new Devicelist;
        $Devicelist->update(array("key1"=>"帐号冲突"), "key1='".addslashes($r->query->uname)."'");
    }

    public static function Action_auth_user ($r) {
        $data   = $r->query;
        $uname  = $data->uname;
        $secret = $data->secret;
        $User   = new User;
        $u      = $User->findone("username='".addslashes($uname)."'");
        if($secret == md5($u['password'])){
            $auth   = new Auth($uname, $u['password']);
            $result = $auth->validate();
        }
        Flight::redirect("/");
    }

    
}