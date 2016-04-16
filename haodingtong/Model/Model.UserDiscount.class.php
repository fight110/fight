<?php

class UserDiscount Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_discount');
    }

    public function get_discount($user_id,$field, $category_id){
        $discount = $this->findone("user_id={$user_id} and field='{$field}' and category_id={$category_id}");
        return $discount['category_discount'];
    }

    public function get_discount_list($user_id,$field, $options=array()){
        $options['limit']   = 1000;
        $list = $this->find("user_id={$user_id} AND field='{$field}'", $options);
        return $list;
    }

    public function set_discount ($user_id,$field, $keyword_id, $value) {
        $data['user_id']            = $user_id;
        $data['field']              = $field;
        $data['category_id']        = $keyword_id;
        $data['category_discount']  = $value;
        $id     = $this->create($data)->insert(true);
        $params['category_id']      = $keyword_id;
        ProductOrder::refresh_user($user_id, $params);
        return $id;
    }
}




