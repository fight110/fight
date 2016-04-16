<?php

class OrderListArea Extends BaseClass {
    private $CACHE_TIME = 60;
    public function __construct(){
        $this->setFactory('orderlist_area');
    }

    public function refresh_area($area1) {
        $OrderList  = new OrderList;
        $options['limit']   = 10000;
        $options['tablename'] = "orderlist as o left join user as u on o.user_id=u.id";
        $options['fields']  = "u.area1 as area1,product_id,SUM(num) as num,SUM(amount) as amount";
        $options['group']   = "product_id,product_id";
        $condition[]    = "u.area1={$area1}";
        $where  = implode(" AND ", $condition);
        $hash   = $OrderList->find($where, $options);
        foreach($hash as $orderinfo){
            $this->create($orderinfo)->insert(true);
        }
    }

    public function refresh_area_product ($area1, $product_id,$product_color_id) {
        $OrderList  = new OrderList;
        $options['tablename'] = "orderlist as o left join user as u on o.user_id=u.id";
        $options['fields']  = "u.area1 as area1,o.product_id,o.product_color_id,SUM(o.num) as num,SUM(o.amount) as amount";
        $condition[]    = "u.area1={$area1}";
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "product_color_id={$product_color_id}";
        $where  = implode(" AND ", $condition);
        $info   = $OrderList->findone($where, $options);
        if(!$info['num']) {
            $info['area1']        = $area1;
            $info['product_id']     = $product_id;
            $info['product_color_id']     = $product_color_id;
            $info['num']            = 0;
            $info['amount']         = 0;
        }
        $this->create($info)->insert(true);
    }

    public function refresh_user_product ($user_id, $product_id, $product_color_id) {
        $User  = new User;
        $info  = $User->findone("id={$user_id}",array('fields'=>"area1"));
        $area1 = $info['area1'];
        if($area1) {
            $this->refresh_area_product($area1, $product_id, $product_color_id);
        }
    }

    public function get_rank ($area1, $product_id,$product_color_id=0, $params=array()) {
        if(!$product_color_id)
            $info   = $this->findone("area1={$area1} AND product_id={$product_id}",array("fields"=>"sum(num) as num"));
        else{
            $info   = $this->findone("area1={$area1} AND product_id={$product_id} AND product_color_id={$product_color_id}");
        }
        $num    = $info['num'];
        if($num){
            $options['tablename']   = "orderlist_area as oa left join product as p on oa.product_id=p.id";
            $options['limit']       =   10000;
            if($product_color_id){
                $condition[]    = "num>{$num}";
            }else{
                $options['fields']      = "product_id";
                $options['group']       = "product_id";
                $options['having']      = "sum(num) >{$num}";
            }
            //$options['db_debug']    = true;
            $condition[]    = "oa.area1={$area1}";
            if($params['category_id']){
                $condition[]    = "p.category_id={$params['category_id']}";
            }
            $where  = implode(" AND ", $condition);
            $list   = $this->find($where, $options);
            $rank   = count($list)+1;
        }else{
            $rank   = 0;
        }
        return $rank;
    }

    public function get_num ($area1, $product_id, $product_color_id=0) {
        if(!product_color_id){
            $info   = $this->findone("area1={$area1} AND product_id={$product_id}");
        }else{
            $info   = $this->findone("area1={$area1} AND product_id={$product_id} AND product_color_id={$product_color_id}");
        }
        $num    = $info['num'];
        return $num;
    }

}

