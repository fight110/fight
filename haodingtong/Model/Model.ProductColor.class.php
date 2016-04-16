<?php

class ProductColor Extends BaseClass {
    private $clear_product_ids = array();
    private $product_color_info = array();
    private static $_product_color_instances = array();
    public static function getProductColorInstance ($product_id, $color_id=0) {
        $product_color  = STATIC::$_product_color_instances[$product_id][$color_id];
        if(!$product_color) {
            $ProductColor  = new ProductColor;
            $product_color = $ProductColor->findone("product_id={$product_id} AND color_id={$color_id}");
            STATIC::$_product_color_instances[$product_id][$color_id]   = $product_color;
        }
        return $product_color;
    }

    public function __construct(){
        $this->setFactory('product_color');
        $this->keywords = new Keywords;
    }

    public function add_clear_product_ids($product_id) {
        $this->clear_product_ids[$product_id]++;
    }

    public function create_color($product_id, $color_id, $skc_id=0, $color_code="",$is_need="",$mininum="",$main_push_id=0){
        $data   = array('product_id'=>$product_id, 'color_id'=>$color_id, 'skc_id'=>$skc_id, 'color_code'=>$color_code,'is_need'=>$is_need,'mininum'=>$mininum,'main_push_id'=>$main_push_id);
        $this->create($data)->insert(true);
        $this->add_clear_product_ids($product_id);
    }

    public function add_color($product_id, $color_id){
        $data   = array('product_id'=>$product_id, 'color_id'=>$color_id);
        $this->create($data)->insert(true);
        $this->add_clear_product_ids($product_id);
    }

    public function remove_color($product_id, $color_id){
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "color_id={$color_id}";
        $where  = implode(' AND ', $condition);
        $this->delete($where);
        $ProductDisplayMemberColor     = new ProductDisplayMemberColor;
        $ProductDisplayMemberColor->remove_color($product_id, $color_id);
    }

    // public function get_color_list($product_id){
    //     $list   = $this->find("product_id={$product_id}", array("limit"=>20));
    //     foreach($list as &$row){
    //         $row['name']    = $this->keywords->getName_File($row['color_id']);
    //     }
    //     return $list;
    // }

    public function get_color_list($product_id){
    	$that = $this;
        $cache  = new Cache(function($product_id) use ($that){
            $field                  = "color";
            $where                  = "pc.product_id={$product_id}";
            $options['limit']       = 100;
            $options['tablename']   = "product_color as pc left join keywords as k on pc.color_id=k.id left join products_attr as pa on pa.keyword_id=pc.color_id AND pa.field='{$field}'";
            $options['fields']      = "pc.*,k.name";
            $options['order']       = "pa.rank asc";
            // $options['db_debug']    = true;
            $list = $that->find($where, $options);
            foreach($list as &$row){
                $row['name']    = preg_replace("/\#.*$/", "", $row['name']);
            }
            return $list;
        }, 60);
        return $cache->get("ProductColor_{$product_id}", array($product_id));
    }

    public function get_distinct_skc_ids ($product_id) {
        $color_list     = $this->get_color_list($product_id);
        $skc_list       = array();
        foreach($color_list as $color) {
            if($color['status']){
                $skc_list[$color['skc_id']]++;
            }
        }
        return array_keys($skc_list);
    }

    public function is_need($product_id){
        $result = $this->findone("product_id={$product_id} AND is_need=1",array("fields"=>"is_need","limit"=>1));
        return $result['is_need'];
    }
    public function get_need_list($permission_brand=''){
        $options['tablename'] = "product_color as pc left join product as p on pc.product_id=p.id";
        $options['fields']  =   "pc.*,p.category_id,p.classes_id";
        $options['limit'] = 10000;
        $condition[] = "pc.is_need=1";
        if($permission_brand){
            $condition[] = "p.brand_id not in ({$permission_brand})";
        }
        $where = implode(" AND ", $condition);
        return $this->find($where,$options);

    }
    public function get_by_skc_id($skc_id){
        return $this->findone("skc_id='{$skc_id}'");
    }

    public function set_skc_id ($product_id, $color_id, $skc_id) {
        $info   = $this->findone("product_id={$product_id} AND color_id={$color_id}");
        $id     = $info['id'];
        if($id) {
            $result = $this->update(array("skc_id"=>$skc_id), "product_id={$product_id} AND color_id={$color_id}");
        }else{
            $result = $this->create(array("skc_id"=>$skc_id, "product_id"=>$product_id, "color_id"=>$color_id))->insert();
        }
        $this->add_clear_product_ids($product_id);
        return $result;
    }

    public function set_color_code ($product_id, $color_id, $color_code) {
        $info   = $this->findone("product_id={$product_id} AND color_id={$color_id}");
        $id     = $info['id'];
        if($id) {
            $result = $this->update(array("color_code"=>$color_code), "product_id={$product_id} AND color_id={$color_id}");
        }else{
            $result = $this->create(array("color_code"=>$color_code, "product_id"=>$product_id, "color_id"=>$color_id))->insert();
        }
        $this->add_clear_product_ids($product_id);
        return $result;
    }

    public function set_mininum ($product_id, $color_id, $mininum) {
        $info   = $this->findone("product_id={$product_id} AND color_id={$color_id}");
        $id     = $info['id'];
        if($id) {
            $result = $this->update(array("mininum"=>$mininum), "product_id={$product_id} AND color_id={$color_id}");
        }else{
            $result = $this->create(array("mininum"=>$mininum, "product_id"=>$product_id, "color_id"=>$color_id))->insert();
        }
        $this->add_clear_product_ids($product_id);
        return $result;
    }

    public function set_main_push_id ($product_id, $color_id, $main_push_id) {
        $info   = $this->findone("product_id={$product_id} AND color_id={$color_id}");
        $id     = $info['id'];
        if($id) {
            $result = $this->update(array("main_push_id"=>$main_push_id), "product_id={$product_id} AND color_id={$color_id}");
        }else{
            $result = $this->create(array("main_push_id"=>$main_push_id, "product_id"=>$product_id, "color_id"=>$color_id))->insert();
        }
        $this->add_clear_product_ids($product_id);
        return $result;
    }

    public function set_need($product_id,$color_id,$is_need){
        $info   = $this->findone("product_id={$product_id} AND color_id={$color_id}");
        $id     = $info['id'];
        if($id) {
            $result = $this->update(array("is_need"=>$is_need), "product_id={$product_id} AND color_id={$color_id}");
        }else{
            $result = $this->create(array("is_need"=>$is_need, "product_id"=>$product_id, "color_id"=>$color_id))->insert();
        }
        $this->add_clear_product_ids($product_id);
        return $result;
    }
    public function set_status ($product_id, $color_id, $status){
        $OrderListBak       = new OrderListBak;
        $Product            = new Product;
        $OrderList          = new OrderList;
        
        $options            = array();
        $options['group']   = "user_id,product_id,product_color_id,product_size_id";
        $options['fields']  = "user_id,product_id,product_color_id,product_size_id,num";
        $options['limit']   = "10000";
        $where              = "product_id={$product_id} AND product_color_id={$color_id}";
        if($status == 0){
            $list           = $OrderList->find($where,$options);
        }else{
            $list           = $OrderListBak->find($where,$options);
        }

        $OrderListBak->bak($product_id, $color_id, $status);
        $this->update(array("status"=>$status), "product_id={$product_id} and color_id={$color_id}");
        $this->add_clear_product_ids($product_id);

        if($status == 0) {
            $count = $this->getCount("product_id={$product_id} AND status=1");
            if($count == 0){
                $Product->update(array("status"=>$status), "id={$product_id}");
            }
        }else{
            $Product->update(array("status"=>$status), "id={$product_id}");
        }
        if($status == 0) {
            foreach ($list as $row) {
                ProductOrder::add($row['user_id'], $row['product_id'], $row['product_color_id'], $row['product_size_id'], 0);
            }
        }else{
            foreach ($list as $row) {
                ProductOrder::add($row['user_id'], $row['product_id'], $row['product_color_id'], $row['product_size_id'], $row['num']);
            }
        }
        ProductOrder::run();
    }

    public function is_active ($product_id, $color_id) {
        $product_color_info = $this->get_product_color_info($product_id, $color_id);
        return $product_color_info['status']    ? true : false;
    }

    public function get_product_color_info ($product_id, $color_id) {
        $key    = "{$product_id}:{$color_id}";
        $info   = $this->product_color_info[$key];
        if(!$info){
            $info   = $this->findone("product_id={$product_id} and color_id={$color_id}");
            $this->product_color_info[$key] = $info;
        }
        return $info;
    }
    
    public function replace_color($product_id, $color_id,$new_color_id){
        $this->update(array("color_id"=>$new_color_id), "product_id='{$product_id}' and color_id='{$color_id}'");
        
        $OrderList      =   new OrderList();
        $OrderListProductColor      =new OrderListProductColor();
        $OrderListUserProductColor  =new OrderListUserProductColor();
        $OrderListProportion        =new OrderListProportion();
        $ProductColorMoq            =new ProductColorMoq();
        $OrderListAgent             =new OrderListAgent;
        $OrderListArea              =new OrderListArea;
        
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "product_color_id in ({$color_id})";
        $where  = implode(' AND ', $condition);
        
        $OrderList->update(array("product_color_id"=>$new_color_id), $where);
        $OrderListProductColor->update(array("product_color_id"=>$new_color_id), $where);
        $OrderListUserProductColor->update(array("product_color_id"=>$new_color_id), $where);
        $OrderListProportion->update(array("product_color_id"=>$new_color_id), $where);
        $ProductColorMoq->update(array("product_color_id"=>$new_color_id), $where);
        $OrderListAgent->update(array("product_color_id"=>$new_color_id), $where);
        $OrderListArea->update(array("product_color_id"=>$new_color_id), $where);
    }

    public function __destruct() {
        foreach($this->clear_product_ids as $product_id => $val) {
            Cache::clearCache("ProductColor_{$product_id}");
        }
    }
    
}




