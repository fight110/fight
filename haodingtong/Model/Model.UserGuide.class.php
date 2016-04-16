<?php

class UserGuide Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_guide');
    }
    
    public function create_guide($user_id,$product_id,$product_color_id,$num){
        return $this->create(array("user_id"=>$user_id,"product_id"=>$product_id,"product_color_id"=>$product_color_id,"num"=>$num))->insert(true);
    }

    public function get_guide_num($user_id,$product_id,$product_color_id) {
        $info   =   $this->findone("user_id={$user_id} AND product_id={$product_id} AND product_color_id={$product_color_id}",array("fields"=>"num"));
        return $info['num'] ? $info['num'] : '-';
    }
}




