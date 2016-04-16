<?php

class OrderListUserProductColor Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlistuserproductcolor');
    }
    
    public function get_product_color_list($where){
        $options    =   array();
        $options['fields']      = "product_id,product_color_id,sum(num) as num,sum(amount) as price,sum(discount_amount) as discount_price";
        $options['group']       = "product_id,product_color_id";
        $options['limit']       = "10000";
        //$options['db_debug']    =true;
        return $this->find($where,$options);
    }
    
    public function get_product_list($where){
        $options    =   array();
        $options['fields']      = "product_id,sum(num) as num,sum(amount) as price,sum(discount_amount) as discount_price";
        $options['group']       = "product_id";
        $options['limit']       = "10000";
        //$options['db_debug']    =true;
        return $this->find($where,$options);
    }
}




