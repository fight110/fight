<?php

class UserRegister Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_register');
    }

    public function create_register($name,$phone){
        return $this->create(array("name"=>$name,"phone"=>$phone,"status"=>0))->insert(true);
    }

    public function vaild($phone){
        $info = $this->findone("phone='{$phone}'",array("fields"=>"status"));
        $message = "";
        if(!$info){
            $message = "";
        }elseif(!$info['status']){
            $message = "您已提交过申请,正在审核,请不要重复注册";
        }else{
            $message = "您的申请已通过审核,请不要重复注册";
        }
        return $message;
    }
}
