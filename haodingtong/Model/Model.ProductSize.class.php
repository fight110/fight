<?php

class ProductSize Extends BaseClass {
    private $clear_product_ids = array();
    public function __construct(){
        $this->setFactory('product_size');
    }

    public function add_clear_product_ids($product_id) {
        $this->clear_product_ids[$product_id]++;
    }

    public function create_size($product_id, $size_id){
        $this->create(array('product_id'=>$product_id, 'size_id'=>$size_id))->insert();
        $this->add_clear_product_ids($product_id);
    }
    
    public function remove_size($product_id, $size_id){
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "size_id={$size_id}";
        $where  = implode(' AND ', $condition);
        $this->delete($where);
    }
    
    // public function get_size_list($product_id){
    //     $list   = $this->find("product_id={$product_id}");
    //     $Keywords   = new Keywords;
    //     foreach($list as &$row){
    //         $row['name']    = $Keywords->getName_File($row['size_id']);
    //     }
    //     return $list;
    // }

    public function get_size_list($product_id){
    		$that = $this;
        $cache  = new Cache(function($product_id) use ($that){
            $field                  = "size";
            $where                  = "pc.product_id={$product_id}";
            $options['limit']       = 100;
            $options['tablename']   = "product_size as pc left join keywords as k on pc.size_id=k.id left join products_attr as pa on pa.keyword_id=pc.size_id AND pa.field='{$field}'";
            $options['fields']      = "pc.*,k.name";
            $options['order']       = "pa.rank asc";
            // $options['db_debug']    = true;
            $list = $that->find($where, $options);
            foreach($list as &$row){
                $row['name']    = preg_replace("/\#.*$/", "", $row['name']);
            }
            return $list;
        }, 60);
        return $cache->get("ProductSize_{$product_id}", array($product_id));
    }

    public function __destruct() {
        foreach($this->clear_product_ids as $product_id => $val) {
            Cache::clearCache("ProductSize_{$product_id}");
            Cache::clearCache("ProductSize_str_{$product_id}");
        }
    }

    public function get_size_list_str($product_id){
        $that = $this;
        $cache  = new Cache(function($product_id) use ($that){
            $field                  = "size";
            $where                  = "pc.product_id={$product_id}";
            $options['limit']       = 100;
            $options['tablename']   = "product_size as pc  left join products_attr as pa on pa.keyword_id=pc.size_id AND pa.field='{$field}' left join products_attr_group gap on gap.attr_id = pc.size_id";
            $options['fields']      = "size_id,group_id";
            $options['order']       = "pa.rank asc";
            // $options['db_debug']    = true;
            $list = $that->find($where, $options);
            $result=array();
            foreach($list as $row){
                $str.=preg_replace("/\#.*$/", "", Keywords::cache_get($row['size_id'])).';';
            }
            $result['str'] = rtrim($str,';');
            $result['group'] = Keywords::cache_get($row['group_id']);
            return $result;
        }, 60);
        return $cache->get("ProductSize_str_{$product_id}", array($product_id));
    }

    public function set_size_mininum($product_id,$product_size_id,$mininum){
        $where = "product_id={$product_id} and size_id={$product_size_id}";
        return $this->update(array("mininum"=>$mininum),$where);
    }

    public function get_size_mininum($product_id,$product_size_id){
        $options['fields'] = "mininum";
        $where = "product_id={$product_id} and size_id={$product_size_id}";
        $info  = $this->findone($where,$options);
        return $info['mininum'];
    }
}




