<?php

class OrderListDetail Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlistdetail');
    }

    public function create_order_detail($user_id, $product_id, $color_id, $size_id, $num,$group_id,$display_id){
        $data['user_id']        = $user_id;
        $data['product_id']     = $product_id;
        $data['product_color_id']       = $color_id;
        $data['product_size_id']        = $size_id;
        $data['num']            = $num;
        $data['create_ip']      = Flight::IP();
        $data['display_id'] = $display_id;
        $data['group_id'] = $group_id;
        $target                 = $this->create($data);
        $target->insert(true);
        return $target->getData();
    }
    
    public function getUserDisplayOrder($did,$uid,$gid=0){
        $option = array();
        $cond = array();
        $option['tablename'] = 'orderlistdetail o left join product_color pc on o.product_id = pc.product_id AND o.product_color_id = pc.color_id ';
        $option['fields'] = 'sum(o.num) as num,o.group_id';
        //$option['limit'] = 60;
        //$option['db_debug'] = true;
        $option['group'] = 'o.group_id';
        $option['key'] = 'group_id';
        $cond[] = 'pc.status = 1 ';
        $cond[] = 'o.user_id = "'.$uid.'" ';
        $cond[] = 'o.display_id = "'.$did.'" ';
        if($gid){
            $cond[] = 'o.group_id = "'.$gid.'" ';
        }
        $where = implode(" AND ", $cond);
        $res = $this->find($where,$option);
        return $res;
    }
}




