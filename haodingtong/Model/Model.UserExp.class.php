<?php

class UserExp Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_exp');
    }
    
    public function get_exp_info ($params=array()) {
    	$user_id 	= $params['user_id'];
    	$brand_id 	= $params['brand_id'];
    	$area1		= $params['area1'];
    	$area2		= $params['area2'];
    	$condition 	= array();
    	if($user_id)	$condition[]	= "ue.user_id={$user_id}";
    	if($brand_id)	$condition[]	= "ue.brand_id={$brand_id}";
    	if($area1)		$condition[]	= "u.area1={$area1}";
    	if($area2)		$condition[]	= "u.area2={$area2}";
        $condition[]    = "u.type=1";
    	$options['fields']	= "SUM(ue.exp_num) as exp_num, SUM(ue.exp_price) as exp_price";
    	$options['tablename']	= "user_exp as ue left join user as u on ue.user_id=u.id";
    	$where 		= implode(" AND ", $condition);
    	return $this->findone($where, $options);
    }

    public function set_brand_exp ($user_id, $brand_id, $name, $value) {
        $data['user_id']    = $user_id;
        $data['brand_id']   = $brand_id;
        $data[$name]        = $value;
        return $this->create($data)->insert(true);
    }
}




