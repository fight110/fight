<?php

class UserWelcome Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_welcome');
    }

    public function add ($user_id) {
        $this->create(array("user_id" => $user_id))->insert();
        return $user_id;
    }

    public function getLatest (){
        $u = $this->findone("1", array("order"=>"id desc"));
        return $u;
    }

    public function getLatestList($options=array()) {
        $options['fields']      = "u.*";
        $options['tablename']   = "user_welcome as uw left join user as u on uw.user_id=u.id";
        $options['order']       = "uw.id desc";
        return $this->find("1", $options);
    }
}




