<?php

class UserSlaveTree Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_slave_tree');
    }

    public function create_slave_tree($parent_user_id, $user_slave_id){
        $slave = $this->create(array('parent_user_id'=>$parent_user_id, 'user_slave_id'=>$user_slave_id));
        return $slave->insert(true);
    }


}




