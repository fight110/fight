<?php

class Control_welcome {
    public static function Action_index($r){
        $Company            = new Company;
        $result['company']  = $Company->getData();
        Flight::display("welcome/index.html", $result);
    }

    public static function Action_in($r){
        $Company            = new Company;
        $result['company']  = $Company->getData();
        Flight::display("welcome/in.html", $result);
    }

    public static function Action_out($r){
        Flight::display("welcome/out.html", $result);
    }

    public static function Action_userinfo($r, $username) {
        $User   = new User;
        $u      = $User->findone("username='".addslashes($username)."'");
        $img    = "tmpl/images/user/{$username}";
        if(is_file(DOCUMENT_ROOT . $img . ".jpg")){
            $u['img']   = $img . ".jpg";
        }elseif(is_file(DOCUMENT_ROOT . $img . ".JPG")){
            $u['img']   = $img . ".JPG";
        }
        if($u['id']){
            $UserWelcome    = new UserWelcome;
            $UserWelcome->add($u['id']);
        }
        Flight::json($u);
    }

    public static function Action_latest($r) {
        $UserWelcome    = new UserWelcome;
        $ulist  = $UserWelcome->getLatestList(array("limit"=>1));
        $u      = $ulist[0];
        Flight::json(array("user_id"=>$u['id'], "name"=>$u['name']));
    }

    
}