<?php

class Control_login {
    public static function Action_index($r){
        $data               = $r->data;
        $uname              = $data->uname;
        $pword              = $data->pword;
        $submit             = $data->submit;
        if($submit){
            $auth   = new Auth($uname, $pword);
            $result = $auth->validate();
            if($result['valid'] == true){
                if($r->query->returl){
                    $returl     = $r->query->returl;
                }elseif($r->data->returl){
                    $returl     = $r->data->returl;
                }else{
                    $returl     = "/";
                }
                Flight::redirect($returl);
                return;
            }
        }
        $Company            = new Company;
        $result['company']  = $Company->getData();
        $result['returl']   = $r->query->returl;
        
        Flight::display('login/index.html', $result);
    }


}