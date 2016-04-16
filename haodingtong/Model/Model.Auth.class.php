<?php

class Auth {
    const   ADMIN   = 0;
    public function __construct($username, $password){
        $user   = new User;
        $where  = "username='".addslashes($username)."' AND password='".addslashes($password)."'";
        $list   = $user->find($where);
        $this->valid = true;
        if(count($list) == 0){
            $this->message  = "帐号或者密码错误";
            $this->valid    = false;
        }else{
            $this->user = $list[0];
            if($this->user['auth'] == 0) {
                $this->message  = "帐号未授权，不能登入";
                $this->valid    = false;
            }elseif($this->user['type'] == 1) {
                $UserSession    = new UserSession;
                $total  = $UserSession->check_has_logined($username);
                if($total){
                    $this->message = "有{$total}个设备登入此帐号，是否要踢出？";
                    $this->confirm = 1;
                }
            }
        }
    }


    public function validate(){
        if($this->message){
            $message    = $this->message;
        }

        if($message){
            $result     = array('valid' => $this->valid, 'message' => $message, 'confirm' => $this->confirm);
        }else{
            $result     = array('valid' => $this->valid);
        }
        return $result;
    }

    public static function is_auth(){
        $Device     = new Device;
        if(!$Device->is_auth()){
            $r      = Flight::request();
            $data       = $r->data;
            $message    = $data->message;
            if($message){
                $Device->setMessage($message);
            }else{
                $message    = $Device->getMessage();
            }
            if($message){
                $message_add = "审核中,请等待";
            }
            echo
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>好订通</title>
</head>
<body>
<form action="" method="POST">请输入授权信息:<input name="message" value="'.$message.'"> <input type="submit" value="提交"></form><p>'
.$message_add.
'</p></body>';
            exit;
        }
    }

    public static function clear_user_login_info(){
        SESSION::destory();
        setcookie(SESSION_LASTUNAME, '', time() + 3600 * 24 * 5, '/');
    }


}




