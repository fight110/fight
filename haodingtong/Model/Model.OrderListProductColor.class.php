<?php

class OrderListProductColor Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlistproductcolor');
    }

    public function refresh($product_id){
        $ProductColor = new ProductColor;
        $OrderList  = new OrderList;
        $condition['product_id']   = $product_id;
        $options    = array();
        $options['status']      = false;
        $options['key']         = "product_color_id";
        $options['DBMaster']	= true;
        $options['group']       = "o.product_id,o.product_color_id";
        $options['fields_more'] = "o.product_id,o.product_color_id";
        $list       = $OrderList->getOrderProductList($condition, $options);
        $color_list = $ProductColor->get_color_list($product_id);
        foreach($color_list as $color){
            if($color['status']){
                $info   = $list[$color['color_id']];
                $data   = array();
                $data['product_id']         = $color['product_id'];
                $data['product_color_id']   = $color['color_id'];
                $data['num']        = $info['num'];
                $data['unum']       = $info['unum'];
                $data['price']      = $info['price'];
                $data['discount_price']     = $info['discount_price'];
                $this->create($data)->insert(true);
            }
        }
    }

    public function refresh_all () {
        $ProductColor   = new ProductColor;
        $list   = $ProductColor->find("", array("limit"=>10000));
        $OrderList  = new OrderList;
        $options['status']      = false;
        $options['group']   = "o.product_id,o.product_color_id";
        $options['fields_more'] = "o.product_id,o.product_color_id";
        foreach($list as $row){
            $condition['product_id']        = $row['product_id'];
            $condition['product_color_id']  = $row['color_id'];
            $options['bak']     = $row['status'] ? 0 : 1;
            $list       = $OrderList->getOrderProductList($condition, $options);
            $info       = $list[0];
            $data   = array();
            $data['product_id']         = $info['product_id'];
            $data['product_color_id']   = $info['product_color_id'];
            $data['num']        = $info['num'];
            $data['unum']       = $info['unum'];
            $data['price']      = $info['price'];
            $data['discount_price']     = $info['discount_price'];
            $this->create($data)->insert(true);
        }
    }

    public function get_product_color_list ($params=array(), $options=array()) {
        $ret_key = $options['key'];
        $options['tablename']   = "product_color as pc left join orderlistproductcolor as opc on pc.product_id=opc.product_id and pc.color_id=opc.product_color_id left join product as p on p.id=pc.product_id";
        $options['fields']      = "p.*, pc.skc_id,pc.color_code,pc.status as pc_status, pc.product_id, pc.color_id,opc.num, opc.price as count_price";
        $options['limit']       = 10000;
        // $options['db_debug']    = true;
        $condition  = array();
        //$keys   = array("category_id", "classes_id", "style_id", "wave_id", "brand_id", "nannvzhuan_id", "price_band_id", "series_id");
        $keys   = array('category_id','medium_id','classes_id','edition_id','contour_id', 'wave_id','style_id','price_band_id','brand_id','series_id','theme_id','nannvzhuan_id','sxz_id','season_id');
        foreach($keys as $key) {
            $val = $params[$key];
            if($val) {
                $condition[]    = "p.{$key}={$val}";
            }
        }
        if(is_numeric($params['status'])){
            $condition[]    = "pc.status={$params['status']}";
        }
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        if($ret_key){
            $result     = array();
            foreach($list as $row){
                $result[$row[$ret_key]] = $row;
            }
            return $result;
        }else{
            return $list;
        }
    }

    public function get_rank($product_id, $color_id, $params=array()){
        $info   = $this->findone("product_id={$product_id} AND product_color_id={$color_id}");
        $options['fields']  = "count(*) + 1 as rank";
        if($params['category_id']){
            $options['tablename']   =   "orderlistproductcolor as o left join product as p on o.product_id=p.id";
            $condition[] = "p.category_id={$params['category_id']}";
        }
        $condition[] = "num>{$info['num']}";
        $where  = implode(" AND ", $condition);
        $rank   = $this->findone($where, $options);
        return $rank['rank'];
    }

    public function get_product_color_num($product_id, $color_id){
        return $this->findone("product_id={$product_id} AND product_color_id={$color_id}",array("fields"=>"num"));
    }
    
    public function get_product_color_rank_list(){
        $options['tablename']   =   "product as p 
                                    left join product_color as pc on p.id=pc.product_id 
                                    left join orderlistproductcolor as o on p.id=o.product_id and pc.color_id=o.product_color_id";
        $options['fields']  =   "p.id as product_id,pc.color_id as product_color_id";
        $options['order']   =   "o.num DESC,p.id,pc.color_id ASC";
        $options['limit']   =   "10000";
        //$options['db_debug']=   true;
        return $this->find("1",$options);
    }
    public function get_product_color_rank($product_id, $color_id){
        $info   = $this->findone("product_id={$product_id} AND product_color_id={$color_id}");
        $options['fields']  = "count(*) + 1 as rank";
        $rank   = $this->findone("num>{$info['num']}", $options);
        return $rank['rank'];
    }
}




