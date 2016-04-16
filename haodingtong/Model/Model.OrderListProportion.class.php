<?php

class OrderListProportion Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlist_proportion');
    }

    public function add ($user_id, $product_id, $product_color_id, $proportion_id, $xnum, $num) {
        $data['user_id']            = $user_id;
        $data['product_id']         = $product_id;
        $data['product_color_id']   = $product_color_id;
        $data['proportion_id']      = $proportion_id;
        $data['xnum']               = $xnum;
        $data['num']                = $num;
        return $this->create($data)->insert(true);
    }

    public function get_product_color_xnum ($product_id, $product_color_id, $params) {
        $options['tablename']   = "orderlist_proportion as o left join user as u on o.user_id=u.id";
        $options['fields']      = "sum(o.xnum) as xnum";
        $condition[]    = "o.product_id={$product_id}";
        $condition[]    = "o.product_color_id={$product_color_id}";
        // $options['db_debug']    = true;
        if($user_id     = $params['user_id']){
            $condition[]    = "o.user_id={$user_id}";
        }
        if($mid         = $params['mid']){
            $condition[]    = "o.zd_user_id={$mid}";
        }
        if($ad_id       = $params['ad_id']){
            $condition[]    = "u.ad_id={$ad_id}";
        }
        $where  = implode(" AND ", $condition);
        return $this->findone($where, $options);
    }

}
