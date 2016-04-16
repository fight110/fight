<?php

class UserProdDiscount Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_product_discount');
    }

    public function get_discount($user_id, $product_id){
        $discount = $this->findone("user_id='{$user_id}' and product_id='{$product_id}'");
        return $discount;
    }

    public function get_discount_list($user_id, $options=array()){
        $list = $this->find("user_id='{$user_id}' and kuanhao_discount>0", $options);
        return $list;
    }

    public function set_discount ($user_id, $product_id, $value,$category_id,$category_discount) {
        $data['user_id']    			= $user_id;
        $data['product_id']             = $product_id;
        $data['kuanhao_discount']	= $value;
        $data['category_id']			= $category_id;
        $data['category_discount']	= $category_discount;
        return $this->create($data)->insert(true);
    }

    public function get_discount_check($user_id, $product_id,$category_id){
        $discount = $this->findone("user_id='{$user_id}' and product_id='{$product_id}' and category_id='{$category_id}'");
        return $discount['id'];
    }
}




