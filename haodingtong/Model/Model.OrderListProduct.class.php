<?php

class OrderListProduct Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlistproduct');
    }

    public function refresh($product_id){
        $OrderList  = new OrderList;
        $condition['product_id']   = $product_id;
        $options    = array();
        $options['status']  = false;
        $options['DBMaster']	= true;
        $list       = $OrderList->getOrderProductList($condition, $options);
        $info       = $list[0];
        $data['product_id']    = $product_id;
        $data['num']        = $info['num'];
        $data['pnum']       = $info['pnum'];
        $data['skc']        = $info['skc'];
        $data['sku']        = $info['sku'];
        $data['price']      = $info['price'];
        $data['discount_price']      = $info['discount_price'];
        $this->create($data)->insert(true);
        $OrderListProductColor  = new OrderListProductColor;
        $OrderListProductColor->refresh($product_id);

        $PlotProduct    =   new PlotProduct;
        $PlotProduct->refresh($data);
    }

    public function getOrderProductList($params=array(), $options=array()){
        $fields         = "p.*, o.product_id, o.num, o.price, o.discount_price, o.pnum, o.sku, o.skc, p.price as p_price";
        if($options['fields_more']){
                $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
                'status'        => true,
                'fields'        => $fields,
                'order'         => "o.num desc"
        );

        return $this->_getOrderList($params, $options);
    }

    public function getOrderProductCount($params=array(), $options=array()){
        $fields         = "SUM(o.num) as num, SUM(o.num*p.price) as price";
        if($options['fields_more']){
                $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
                'status'        => true,
                'fields'        => $fields
        );

        $list = $this->_getOrderList($params, $options);
        return $list[0];
    }

    private function _getOrderList($params, $options=array()){
        $options    = $options + array('status'=>true, 'status_val'=>1);

        $condition  = array();
        $style_id   = $params['style_id'];
        $wave_id    = $params['wave_id'];
        $category_id    = $params['category_id'];
        $series_id  = $params['series_id'];
        $classes_id = $params['classes_id'];
        $price_band_id  = $params['price_band_id'];
        $brand_id   = $params['brand_id'];
        $area1      = $params['area1'];
        $area2      = $params['area2'];
        $user_id    = $params['user_id'];
        $product_id = $params['product_id'];
        $product_ids    = $params['product_ids'];
        $date_start     = $params['date_start'];
        $date_end       = $params['date_end'];
        $search     = trim($params['search']);
        $page           = $options['page'];
        $key            = $options['key'];
        if($style_id)       $condition[]    = "p.style_id={$style_id}";
        if($wave_id)        $condition[]    = "p.wave_id={$wave_id}";
        if($category_id)    $condition[]    = "p.category_id={$category_id}";
        if($series_id)      $condition[]    = "p.series_id={$series_id}";
        if($classes_id)     $condition[]    = "p.classes_id={$classes_id}";
        if($price_band_id)  $condition[]    = "p.price_band_id={$price_band_id}";
        if($brand_id)       $condition[]    = "p.brand_id={$brand_id}";
        if($area1)          $condition[]    = "u.area1={$area1}";
        if($area2)          $condition[]    = "u.area2={$area2}";
        if($user_id)        $condition[]    = "o.user_id={$user_id}";
        if($product_id)     $condition[]    = "o.product_id={$product_id}";
        if($product_ids)    $condition[]    = "o.product_id in ($product_ids)";
        if($date_start)     $condition[]    = "o.post_time>='$date_start 00:00:00'";
        if($date_end)       $condition[]    = "o.post_time<='$date_end 23:59:59'";
        $condition[]    = "o.num>0";
        if($search){
            $qt     = addslashes($search);
            $condition[]    = "(p.bianhao='{$qt}' OR p.kuanhao='{$qt}' OR p.name like '%{$qt}%' OR p.content like '%{$qt}%')";
        }
        if($options['status'] === true){
            if(is_numeric($options['status_val'])){
                $condition[]    = "p.status={$options['status_val']}";
            }else{
                $condition[]    = "p.status<>0";
            }
        }

        if(0 == count($condition))  $condition[]    = "1";
        $where      = implode(' AND ', $condition);
        $fields     = $options['fields'];
        $tablename  = "orderlistproduct as o left join product as p on o.product_id=p.id";
        $group      = $options['group'];
        $order      = $options['order']         ? $options['order'] : "num desc";
        $sql    = "SELECT {$fields} FROM {$tablename} WHERE $where";
        if($group){
                $sql .= " GROUP BY {$group}";
        }
        if($order){
                $sql    .= " ORDER BY {$order}";
        }
        if($page){
                $limit  = $options['limit']     ? $options['limit']     : 10;
                $start  = ( $page - 1 ) * $limit;
                $sql    .= " LIMIT $start, $limit";
        }
        if($options['db_debug']){
            echo $sql, "<br>";
        }
            // echo $sql, "<br>";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $result     = array();
        if($key){
                while($row  = $sth->fetch()){
                    $result[$row[$key]] = $row;
                }
        }else{
                while($row  = $sth->fetch()){
                    $result[] = $row;
                }
        }

        return $result;
    }

    public function get_rank($product_id,$params=array()){
	// return 0;
        $info   = $this->findone("product_id={$product_id}");
        $num    = $info['num'];
        if($num){
            if($params['category_id']){
                $options['tablename'] = "orderlistproduct as o left join product as p on o.product_id=p.id";
                $condition[] = "p.category_id={$params['category_id']}";
            }
            $condition[] = "num>{$num}";
            $where = implode(" AND ", $condition);
            $rank   = $this->getCount($where,$options) + 1;
        }else{
            $rank   = 0;
        }
        return $rank;
    }
    
    public function get_product_rank_list(){
        $options['tablename']   =   "product as p left join orderlistproduct as o on p.id=o.product_id";
        $options['fields']  =   "p.id as product_id";
        $options['order']   =   "o.num DESC,p.id ASC";
        $options['limit']   =   "10000";
        return $this->find("1",$options);
    }
    public function get_num($product_id){
        return $this->findone("product_id={$product_id}",array("fields"=>"num"));
    }
}




