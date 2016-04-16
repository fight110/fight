<?php

class Product Extends BaseClass {
    public function __construct($id=null){
        $this->setFactory('product');
        if(is_numeric($id)){
            // $this->setAttribute($this->getProductDetail($id));
            $this->setAttribute($this->findone("id={$id}"));
        }
    }

    public function getMydeleteNum($user_id){
        $condition[]    = "status=0";
        $condition[]    = "id in (SELECT product_id FROM orderlist where user_id={$user_id} AND num>0 group by product_id)";
        $where  = implode(" AND ", $condition);
        return $this->getCount($where);
    }

    public function getGroupList($group, $options=array()){
        if(!$group) throw new Exception("group error : {$group}");

        $status     = $options['status'];
        $user_id    = $options['user_id'];
        $condition  = array();
        if($status){
            $condition[]    = "status=1";
        }
        if($user_id){
            $condition[]    = "id in (SELECT DISTINCT product_id FROM orderlist WHERE user_id={$user_id})";
        }
        $where      = implode(' AND ', $condition);
        if(!$where) $where = "1";
        $fields     = "COUNT(*) as num,{$group}";
        $list       = $this->find($where, array("fields"=>$fields, "limit"=>100, "group"=>$group, "key"=>$group));
        return $list;
    }


    public function getGroupAnalysisList($list, $user_id, $group, $options){
        $list1      = $this->getGroupList($group, array());
        $list2      = $this->getGroupList($group, array("user_id"=>$user_id));
        $total1     = 0;
        $total2     = 0;
        foreach($list1 as $r1){
            $total1     += $r1['num'];
        }
        foreach($list2 as $r2){
            $total2     += $r2['num'];
        }
        foreach($list as &$row){
            $id     = $row['keyword_id'];
            $row['num_total']   = $list1[$id]['num'];
            $row['num_my']      = $list2[$id]['num'];
        }
        return array($total1, $total2, $list);
    }

    public function get_SKC($options=array()){
        $tablename  = "product as p left join product_color as c on p.id=c.product_id";
        $fields     = "COUNT(*) as total";
        $condition[]    = "p.status=1";
        $keys       = array('category_id', 'style_id', 'classes_id', 'wave_id', 'series_id', 'price_band_id', 'season_id', 'nannvzhuan_id', 'brand_id');
        foreach($keys as $key){
            $val    = $options[$key];
            if($val){
                $condition[]    = "p.{$key}={$val}";
            }
        }
        $where      = implode(' AND ', $condition);
        $total      = $this->getCount($where, array('tablename'=>$tablename, 'fields'=>$fields));
        return $total;
    }

    public function getMessageList(){
        $condition[]    = "message<>''";
        $condition[]    = "status=1";
        $where      = implode(" AND ", $condition);
        $options['limit']   = 1000;
        return $this->find($where, $options);
    }

    public function getFabricColorList($fabric_id){
        $tablename      = "product as p left join product_color as pc on p.id=pc.product_id left join fabric_moq as f on p.fabric_id=f.fabric_id and pc.color_id=f.color_id";
        $fields         = "pc.color_id, f.minimum";
        $condition[]    = "p.status=1";
        $condition[]    = "p.fabric_id=$fabric_id";
        $where  = implode(" AND ", $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['limit']       = 1000;
        $options['group']       = "pc.color_id";
        // $options['db_debug']    = true;
        return $this->find($where, $options);
    }

    public function getFabricOrderList($params=array()){
        $complete       = $params['complete'];
        $fabric_id      = $params['fabric_id'];
        $product_id     = $params['product_id'];
        $tablename      = "product as p left join product_color as pc on p.id=pc.product_id left join fabric_moq as f on p.fabric_id=f.fabric_id and pc.color_id=f.color_id left join orderlist as o on p.id=o.product_id and o.product_color_id=pc.color_id";
        $fields         = "p.fabric_id, pc.color_id, f.minimum, SUM(o.num) as num, COUNT(DISTINCT o.product_id) as pnum, ifnull(SUM(p.fabric_unit*o.num), 0) as fabric_total";
        $condition[]    = "p.status=1";
        $condition[]    = "p.fabric_unit>0";
        if($fabric_id)  $condition[]    = "p.fabric_id={$fabric_id}";
        if($product_id) $condition[]    = "p.id={$product_id}";
        if(is_numeric($complete)){
            if($complete){
                $options['having']  = "minimum<=fabric_total";
            }else{
                $options['having']  = "minimum>fabric_total";
            }
        }
        $where  = implode(" AND ", $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['limit']       = 1000;
        $options['group']       = "p.fabric_id, pc.color_id";
        // $options['db_debug']    = true;
        return $this->find($where, $options);
    }

    public function getProductDetail($product_id){
        $cache_id   = HDT_PRODUCT_CACHE_STRING . $product_id;
        $Product    = $this;
        $product    = fastCache::cache($cache_id, function() use($product_id, $Product){
            $data = $Product->findone("id={$product_id}");
            if($data['id']){
                $ProductColor   = new ProductColor;
                $ProductSize    = new ProductSize;
                $color_list     = $ProductColor->get_color_list($product_id);
                $size_list      = $ProductSize->get_size_list($product_id);
                $data['color_list']  = $color_list;
                $data['size_list']   = $size_list;
            }
            return $data;
        }, 60);
        return $product;
    }

    public function getProductList($category_id, $options=array()){
        $list = $this->find("category_id={$category_id}", $options);
        return $list;
    }

}




