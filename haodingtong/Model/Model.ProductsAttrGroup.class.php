<?php

class ProductsAttrGroup Extends BaseClass {
    public function __construct(){
        $this->setFactory('products_attr_group');
    }

    public function add_attr_group($attr_id, $group_id){
        $id = $this->create(array("attr_id"=>$attr_id, "group_id"=>$group_id))->insert(true);
        return $id;
    }
    
}



