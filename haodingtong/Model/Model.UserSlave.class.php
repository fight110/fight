<?php

class UserSlave Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_slave');
    }

    public function create_slave($user_id, $user_slave_id){
        $slave = $this->create(array('user_id'=>$user_id, 'user_slave_id'=>$user_slave_id));
        return $slave->insert(true);
    }

    public function get_slave_user_id($master_user_id){
    	$options['fields']	= "GROUP_CONCAT( user_slave_id ) as slave";
    	//$options['db_debug'] = true;
    	$info 	= $this->findone("user_id={$master_user_id}", $options);	
    	return $info['slave'];
    }

    public function get_slave_user_list($master_user_id){
    	$options['tablename']	= "user_slave as us left join user as u on us.user_slave_id=u.id";
    	$options['fields']		= "u.*";
    	$options['limit']	= 100;
    	$list 	= $this->find("us.user_id={$master_user_id}", $options);
    	return $list;
    }

    public function get_master_uid($user_slave_id) {
        $info   = $this->findone("user_slave_id={$user_slave_id}");
        return $info['user_id'];
    }

    public static function get_user_slave_num($master_user_id){
        $UserSlave = new UserSlave;
        $options['fields'] = "count(user_slave_id) as user_num";
        $where = "user_id={$master_user_id}";
        $result= $UserSlave->findone($where,$options);
        return $result['user_num'];
    }
    
}




