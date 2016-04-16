<?php

class OrderListUser Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlistuser');
    }

    public function refresh($user_id){
        $OrderList  = new OrderList;
        $condition['user_id']   = $user_id;
        $options    = array();
        // $options['db_debug']    = true;
	    $options['DBMaster']	= true;
        $list       = $OrderList->getOrderUserList($condition, $options);
        $info       = $list[0];
        $data['user_id']    = $user_id;
        $data['num']        = $info['num'];
        $data['pnum']       = $info['pnum'];
        $data['sku']        = $info['sku'];
        $data['skc']        = $info['skc'];
        $data['price']      = $info['price'];
        $data['discount_price']      = $info['discount_price'];
        $data['zd_discount_price']   = $info['zd_discount_price'];
        $this->create($data)->insert(true);

        $PlotUser    =   new PlotUser;
        $PlotUser->refresh($data);

        return $data;
    }

    public function getrank($n, $order){
        $rank = $this->getCount("$order>$n");
        return $rank;
    }
    
    public function get_total($where){
        $options    =   array();
        $options['fields']  =   "sum(num) as num,sum(price) as price,sum(discount_price) as discount_price";
        //$options['db_debug']    =true;
        return $this->findone($where,$options);
    }
}




