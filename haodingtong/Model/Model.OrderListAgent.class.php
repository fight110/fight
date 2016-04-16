<?php

class OrderListAgent Extends BaseClass {
    private $CACHE_TIME = 60;
    public function __construct(){
        $this->setFactory('orderlist_agent');
    }

    public function agent_product_list ($zd_user_id, $params=array(), $options=array()) {
        $condition[]    = "user_id={$zd_user_id}";
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        return $list;
    }

    public function refresh_agent($zd_user_id) {
        $OrderList  = new OrderList;
        $options['limit']   = 10000;
        $options['fields']  = "zd_user_id as user_id,product_id,product_color_id,SUM(num) as num,SUM(amount) as amount";
        //$options['key']     = "product_id";
        $options['group']   = "product_id,product_color_id";
        $condition[]    = "zd_user_id={$zd_user_id}";
        $where  = implode(" AND ", $condition);
        $hash   = $OrderList->find($where, $options);
        foreach($hash as $orderinfo) {
            $this->create($orderinfo)->insert(true);
        }
    }

    public function refresh_agent_product ($zd_user_id, $product_id, $product_color_id) {
        $OrderList  = new OrderList;
        $options['fields']  = "zd_user_id as user_id,product_id,product_color_id,SUM(num) as num,SUM(amount) as amount";
        $condition[]    = "zd_user_id={$zd_user_id}";
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "product_color_id={$product_color_id}";
        $where  = implode(" AND ", $condition);
        $info   = $OrderList->findone($where, $options);
        if(!$info['user_id']) {
            $info['user_id']        = $zd_user_id;
            $info['product_id']     = $product_id;
            $info['product_color_id']     = $product_color_id;
            $info['num']            = 0;
            $info['amount']         = 0;
        }
        $this->create($info)->insert(true);
    }

    public function refresh_user_product ($user_id, $product_id,$product_color_id) {
        $UserSlave  = new UserSlave;
        $info       = $UserSlave->findone("user_slave_id={$user_id}");
        $zd_user_id = $info['user_id'];
        if($zd_user_id) {
            $this->refresh_agent_product($zd_user_id, $product_id,$product_color_id);
        }
    }

    public function get_rank ($zd_user_id, $product_id,$product_color_id=0, $params=array()) {
        if(!$product_color_id)
            $info   = $this->findone("user_id={$zd_user_id} AND product_id={$product_id}",array("fields"=>"sum(num) as num"));
        else{
            $info   = $this->findone("user_id={$zd_user_id} AND product_id={$product_id} AND product_color_id={$product_color_id}");
        }
        $num    = $info['num'];
        if($num){
            $options['tablename']   = "orderlist_agent as oa left join product as p on oa.product_id=p.id";
            $options['limit']       =   10000;
            if($product_color_id){
                $condition[]    = "num>{$num}";
            }else{
                $options['fields']      = "product_id";
                $options['group']       = "product_id";
                $options['having']      = "sum(num) >{$num}";
            }
            //$options['db_debug']    = true;
            $condition[]    = "oa.user_id={$zd_user_id}";
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

    public function get_num ($zd_user_id, $product_id) {
        $info   = $this->findone("user_id={$zd_user_id} AND product_id={$product_id}");
        $num    = $info['num'];
        return $num;
    }

}

