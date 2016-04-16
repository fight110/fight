<?php

class UserProduct Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_product');
    }

    public function create_product($user_id, $product_id, $rateval=5){
        $row = $this->create(array('user_id'=>$user_id, 'product_id'=>$product_id, 'rateval'=>$rateval));
        return $row->insert(true);
    }

    public function remove_product($user_id, $product_id){
        $this->delete("user_id={$user_id} AND product_id={$product_id}");
    }

    public function get_list($user_id, $params=array()){
        // $has_store      = $params['has_store'];
        // if($has_store){
        //     $tablename      = "user_product as up left join product as p on up.product_id=p.id";
        // }else{
        //     $tablename      = "product as p left join user_product as up on up.product_id=p.id";
        //     $condition[]    = "up.product_id is null";
        // }
        $tablename      = "product as p left join user_product as up on up.product_id=p.id";
        $condition[]    = "up.user_id={$user_id}";
        $ordered        = $params['ordered'];
        $rateval        = $params['rateval'];
        $options        = array();
        $options['page']    = $params['page'];
        $options['limit']   = $params['limit'];
        $keys   = array('style_id', 'wave_id', 'category_id', 'classes_id', 'series_id', 'price_band_id');
        foreach($keys as $key){
            if($params[$key]){
                $condition[]    = "p.{$key}=" . $params[$key];
            }
        }
        if($rateval){
            $condition[]    = "up.rateval={$rateval}";
        }
        $fields         = "p.*, up.rateval";
        switch($ordered){
            case 'on'   :
                $condition[]    = "up.product_id in (SELECT DISTINCT o.product_id FROM orderlist as o WHERE o.user_id={$user_id})";
                $condition[]    = "p.status=1";
                break;
            case 'off'  :
                $condition[]    = "up.product_id not in (SELECT DISTINCT o.product_id FROM orderlist as o WHERE o.user_id={$user_id})";
                $condition[]    = "p.status=1";
                break;
            case 'unactive' :
                $condition[]    = "p.status=0";
                break;
            default     : 
                $condition[]    = "p.status=1";
        }
        // $options['db_debug']    = true;
        
        $where  = implode(" AND ", $condition);
        if(!array_key_exists('order', $options)){
            $options['order']   = "up.id desc";
        }

        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $list   = $this->find($where, $options);
        return $list;
    }

    public function is_store($user_id, $product_id){
        $where  = "user_id={$user_id} AND product_id={$product_id}";
        $count  = $this->getCount($where);
        return $count;
    }

    public function get_store_info($user_id, $product_id){
        $where  = "user_id={$user_id} AND product_id={$product_id}";
        $info   = $this->findone($where);
        return $info;
    }

    public function get_store_group_info($user_id){
        $options['tablename']   = "user_product as up left join product as p on up.product_id=p.id";
        $options['group']       = "up.rateval";
        $options['fields']      = "up.rateval, COUNT(*) as num";
        $condition[]    = "up.user_id={$user_id}";
        $condition[]    = "p.status=1";
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

    public function get_store_group_list($params=array()){
        $options['tablename']   = "user_product as up left join product as p on up.product_id=p.id left join user as u on up.user_id=u.id";
        $options['group']       = "up.product_id, up.rateval";
        $options['order']       = "p.bianhao";
        $options['fields']      = "p.bianhao,p.name as pname,p.kuanhao,p.wave_id,p.defaultimage,p.category_id,COUNT(*) as num,GROUP_CONCAT(u.name) as uname, up.rateval";
        $options['limit']       = 100000;
        // $options['db_debug']    = true;
        $condition[]    = "p.status=1";
        $keys   = array("wave_id", "category_id", "style_id", "series_id", "classes_id");
        foreach($keys as $key){
            $val = $params[$key];
            if($val){
                $condition[]    = "p.{$key}={$val}";
            }
        }
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }
    
}




