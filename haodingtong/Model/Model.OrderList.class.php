<?php

class OrderList Extends BaseClass {
    public function __construct(){
        $this->setFactory('orderlist');
        $this->DISCOUNT_CONDITION       = "SUM(o.discount_amount)";
        $this->DISCOUNT_CONDITION_UM    = "SUM(o.zd_discount_amount)";
        $this->DISCOUNT_PRICE       = "o.discount_unit_price";
        // $this->DISCOUNT_CONDITION       = "SUM(o.num * IF(u.discount_type AND p.price_purchase<>0, p.price_purchase, p.price * IF(ud.category_discount, ud.category_discount, u.discount)))";
        // $this->DISCOUNT_CONDITION_UM    = "SUM(o.num * IF(um.discount_type AND p.price_purchase<>0, p.price_purchase, p.price * IF(ud.category_discount, ud.category_discount, um.discount)))";
        // $this->DISCOUNT_PRICE       = "IF(u.discount_type AND p.price_purchase<>0, p.price_purchase, p.price * IF(ud.category_discount, ud.category_discount, u.discount))";
    }

    public function create_order($user_id, $product_id, $color_id, $size_id, $num, $unit_price, $discount_unit_price, $amount, $discount_amount, $zd_user_id, $zd_discount_unit_price, $zd_discount_amount){
        $data['user_id']        = $user_id;
        $data['product_id']     = $product_id;
        $data['product_color_id']       = $color_id;
        $data['product_size_id']        = $size_id;
        $data['num']            = $num;
        $data['unit_price']     = $unit_price;
        $data['discount_unit_price']    = $discount_unit_price;
        $data['amount']         = $amount;
        $data['discount_amount']        = $discount_amount;
        $data['zd_user_id']     = $zd_user_id;
        $data['zd_discount_unit_price']     = $zd_discount_unit_price;
        $data['zd_discount_amount']     = $zd_discount_amount;
        $data['create_ip']      = Flight::IP();
        $target                 = $this->create($data);
        $target->insert(true);
        return $target->getData();
    }

    public function remove_order($user_id=0, $product_id=0, $color_id=0, $size_id=0){
        if($user_id)    $condition[]    = "user_id={$user_id}";
        if($product_id) $condition[]    = "product_id={$product_id}";
        if($color_id)   $condition[]    = "product_color_id in ({$color_id})";
        if($size_id)    $condition[]    = "product_size_id in ({$size_id})";
        $where  = implode(' AND ', $condition);
        $this->delete($where);
        $this->refresh_index_user($user_id);
        $this->refresh_index_product($product_id);
    }

    public function change_color($product_id, $color_id, $new_color_id) {
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "product_color_id in ({$color_id})";
        $where  = implode(' AND ', $condition);
        $this->update(array("product_color_id"=>$new_color_id), $where);
    }

    public function getOrderUserList($params=array(), $options=array()){
        $fields         = "u.*, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price, {$this->DISCOUNT_CONDITION_UM} as zd_discount_price,SUM(o.num) as num, count(DISTINCT o.product_id) as pnum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id,o.product_size_id) as sku";
        if($options['fields_more']){
                $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
                'status'        => true,
                'fields'        => $fields,
                'group'         => "o.user_id",
                'order'         => "num desc"
        );

        return $this->_getOrderList($params, $options);
    }

    public function getOrderProductList($params=array(), $options=array()){
        $fields         = "p.*, p.price as p_price, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price, SUM(o.num) as num, count(DISTINCT o.user_id) as unum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id,o.product_size_id) as sku";
        if($options['fields_more']){
                $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
                'status'        => true,
                'fields'        => $fields,
                'group'         => "o.product_id",
                'order'         => "num desc"
        );

        return $this->_getOrderList($params, $options);
    }

    public function getOrderProductList1($params=array(), $options=array()){
        $fields         = "p.*";
        if($options['fields_more']){
                $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
                'status'        => true,
                'fields'        => $fields,
                'group'         => "o.product_id",
                'order'         => "num desc"
        );

        return $this->_getOrderList($params, $options);
    }
    public function getOrderProductCount($params=array(), $options=array()){
        $fields         = "SUM(o.num) as num, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price";
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

    public function getOrderAnalysisList($params, $options=array()){
        $fields         = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id,o.product_size_id) as sku";
        if($options['fields_more']){
                $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
                'status'        => true,
                'fields'        => $fields,
                'group'         => "o.product_id",
                'order'         => "num desc"
        );

        return $this->_getOrderList($params, $options);
    }

    public function getOrderAnalysisCount($params, $options=array()){
        $fields         = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price,{$this->DISCOUNT_CONDITION_UM} as zd_discount_price, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id,o.product_size_id) as sku";
        if($options['fields_more']){
                $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
                'status'        => true,
                'fields'        => $fields,
                'order'         => false
        );

        $list = $this->_getOrderList($params, $options);
        return $list[0];
    }

    private function _getOrderList($params, $options=array()){
        $options    = $options + array('status'=>true, 'status_val'=>1);

        $show_all   = $options['show_all'];
        $condition  = array();
        $category_id    = $params['category_id'];
        $medium_id      = $params['medium_id'];
        $classes_id     = $params['classes_id'];
        $wave_id        = $params['wave_id'];
        $style_id       = $params['style_id'];
        $price_band_id  = $params['price_band_id'];
        $brand_id       = $params['brand_id'];
        $series_id      = $params['series_id'];
        $theme_id       = $params['theme_id'];
        $nannvzhuan_id  = $params['nannvzhuan_id'];
        $sxz_id         = $params['sxz_id'];
        $season_id      = $params['season_id'];
        $edition_id     = $params['edition_id'];
        $contour_id     = $params['contour_id'];
        $isspot     = $params['isspot'];
        $area1      = $params['area1'];
        $area2      = $params['area2'];
        $zongdai    = $params['zongdai'];
        $user_id    = $params['user_id'];
        $is_lock    = $params['is_lock'];
        $property   = $params['property'];
        $product_id = $params['product_id'];
        $product_color_id   = $params['product_color_id'];
        $product_ids    = $params['product_ids'];
        $date_start     = $params['date_start'];
        $date_end       = $params['date_end'];
        $ad_area1   = $params['ad_area1'];
        $ad_area2   = $params['ad_area2'];
        $ad_id   = $params['ad_id'];
        $search_user    = trim($params['search_user']);
        $master_uid     = $params['master_uid'];
        $fliter_uid     = $params['fliter_uid'];
        $order_status     = $params['order_status'];
        $search     = trim($params['search']);
        $ex_classes  = $params['ex_classes'];
        $page           = $options['page'];
        $key            = $options['key'];
        $count          = $options['count'];
        $tables_more    = $options['tables_more'];
        $bak            = $options['bak'];
        if($category_id)    $condition[]    = "p.category_id in ({$category_id})";
        if($medium_id)      $condition[]    = "p.medium_id in ({$medium_id})";
        if($classes_id)     $condition[]    = "p.classes_id in ({$classes_id})";
        if($wave_id)        $condition[]    = "p.wave_id in ({$wave_id})";
        if($style_id)       $condition[]    = "p.style_id in ({$style_id})";
        if($price_band_id)  $condition[]    = "p.price_band_id in ({$price_band_id})";
        if($brand_id)       $condition[]    = "p.brand_id in ({$brand_id})";
        if($series_id)      $condition[]    = "p.series_id in ({$series_id})";
        if($theme_id)       $condition[]    = "p.theme_id in ({$theme_id})";
        if($nannvzhuan_id)  $condition[]    = "p.nannvzhuan_id in ({$nannvzhuan_id})";
        if($sxz_id)         $condition[]    = "p.sxz_id in ({$sxz_id})";
        if($season_id)      $condition[]    = "p.season_id in ({$season_id})";
        if($isspot)         $condition[]    = "p.isspot in ({$isspot})";
        if($area1)          $condition[]    = "u.area1 in ({$area1})";
        if($area2)          $condition[]    = "u.area2 in ({$area2})";
        if($user_id)        $condition[]    = "o.user_id={$user_id}";      
        if($product_id)     $condition[]    = "o.product_id={$product_id}";
        if($product_color_id)     $condition[]    = "o.product_color_id={$product_color_id}";
        if($product_ids)    $condition[]    = "o.product_id in ($product_ids)";
        if($date_start)     $condition[]    = "o.post_time>='$date_start 00:00:00'";
        if($date_end)       $condition[]    = "o.post_time<='$date_end 23:59:59'";
        if($ad_area1)       $condition[]    = "u.area1={$ad_area1}";
        if($ad_area2)       $condition[]    = "u.area2={$ad_area2}";
        if($zongdai)        $condition[]    = "us.user_id={$zongdai}";
        if($master_uid)     $condition[]    = "o.zd_user_id=$master_uid";
        if($fliter_uid)     $condition[]    = "u.id={$fliter_uid}";
        if($ex_classes)  $condition[]    = "p.classes_id not in ($ex_classes)";
        if($order_status)       $condition[]    = $order_status=='all'?"u.order_status>=1":"u.order_status = {$order_status}";
        if($ad_id){
            $condition[]    = "u.ad_id={$ad_id}";
        }
        if(is_numeric($is_lock)){
            $condition[]    = "u.is_lock={$is_lock}";
        }
        if(is_numeric($property)){
            $condition[]    = "u.property={$property}";
        }
        if($search_user){
            $qt     = addslashes($search_user);
            $condition[]        = "(u.name LIKE '%{$qt}%' OR u.username='{$qt}')";
        }
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
        if($count){
            $fields = "SQL_CALC_FOUND_ROWS {$options['fields']}";
        }else{
            $fields = $options['fields'];
        }
        $table_orderlist = $bak ? "orderlistbak" : "orderlist";
        if($show_all){
            $tablename  = "product as p left join {$table_orderlist} as o on o.product_id=p.id AND $where left join user as u on o.user_id=u.id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id left join user_slave as us on u.id=us.user_slave_id";
            // $where      = "1";
        }else{
            $tablename  = "{$table_orderlist} as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id left join user_slave as us on u.id=us.user_slave_id";
        }
        if($tables_more){
            $tablename .= " $tables_more";
        }
        $group      = $options['group'];
        $order      = $options['order']         ? $options['order'] : "num desc";
        $sql    = "SELECT {$fields} FROM {$tablename} WHERE $where";
        if($group){
                $sql .= " GROUP BY {$group}";
        }
        if($options['order'] !== false && $order){
                $sql    .= " ORDER BY {$order}";
        }
        if($page){
                $limit  = $options['limit']     ? $options['limit']     : 10;
                $start  = ( $page - 1 ) * $limit;
                $sql    .= " LIMIT $start, $limit";
        }
        if($options['db_debug']){
            echo "<p>{$sql}</p>";
        }
             //echo $sql, "<br>";
	    $dbh 	= $options['DBMaster'] ? $this->dbh : $this->dbh_slave;
        $sth    = $dbh->prepare($sql);
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
        if($count){
            $sth = $dbh->prepare("SELECT FOUND_ROWS() as total");
            $sth->execute();
            $countinfo      = $sth->fetch();
            $this->_total   = $countinfo['total'];
        }

        return $result;
    }

    public function get_count_total(){
        return $this->_total;
    }

    /*
     * @params type : default => 1
     *      1   => 覆盖
     *      2   => 追加
     * @params time : default => 1
    */
    public function copy_order($from, $to, $type=1, $time=1){
        $User   = new User;
        $from   = addslashes($from);
        $to     = addslashes($to);
        $user_from      = $User->findone("username='{$from}'");
        $user_to        = $User->findone("username='{$to}'");
        if(!$user_from['id']){
                $message        = "复制来源帐号不存在";
        }
        if(!$user_to['id']){
                $message        = "复制目标帐号不存在";
        }
        if(!$message){
                $user_to_id     = $user_to['id'];
                $list   = $this->find("user_id={$user_from['id']}", array("limit"=>100000));
                $product_hash = array();
                $total  = 0;
                if(!is_numeric($time)) $time = 1;
                if($type == 1) {
                    $clear_list   = $this->find("user_id={$user_to_id}", array("limit"=>1000000));
                    foreach($clear_list as $row){
                        ProductOrder::add($user_to_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], 0);
                    }

                    foreach($list as $row){
                        $num    = round($row['num'] * $time);
                        ProductOrder::add($user_to_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], $num);
                        $total += $num;
                    }
                }elseif($type == 2) {
                    foreach($list as $row){
                        $num    = round($row['num'] * $time);
                        $order  = $this->findone("user_id={$user_to_id} AND product_id={$row['product_id']} AND product_color_id={$row['product_color_id']} AND product_size_id={$row['product_size_id']}");
                        if($order['id']) {
                            ProductOrder::add($user_to_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], $order['num'] + $num);
                        }else{
                            ProductOrder::add($user_to_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], $num);
                        }
                        $total += $num;
                    }
                }
                ProductOrder::run();
                return array('valid'=>true, 'count'=>$total);
        }else{
                return array('valid'=>false, 'message'=>$message);
        }
    }

    // public function copy_order1($from_id, $to_id){
    //     $user_to_id     = $to_id;
    //     $list   = $this->find("user_id={$from_id}", array("limit"=>10000));
    //     foreach($list as $row){
    //             $data = array(
    //                     'user_id'       => $user_to_id,
    //                     'product_id'    => $row['product_id'],
    //                     'product_color_id'      => $row['product_color_id'],
    //                     'product_size_id'       => $row['product_size_id'],
    //                     'num'           => $row['num']
    //             );
    //             $target = $this->create($data);
    //             $target->insert(true);
    //     }
    //     return array('valid'=>true, 'count'=>count($list));
    // }

    public function getOrderProductRank($product_id, $count){
        $tablename      = "orderlist";
        $sql    = "SELECT SUM(num) as num FROM $tablename WHERE product_id={$product_id} group by user_id having num>{$count}";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $rowCount       = $sth->rowCount();
        return $rowCount + 1;
    }

    public function getOrderAllRank($count, $options=array()){
        $tablename      = "orderlist as o left join product as p on o.product_id=p.id";
        $product_id     = $options['product_id'];
        if($product_id){
            $condition[]    = "o.product_id={$product_id}";
        }
        $condition[]    = "p.status=1";
        $where  = implode(" AND ", $condition);
        $sql    = "SELECT SUM(o.amount) as price FROM $tablename WHERE $where group by o.user_id having price>{$count}";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $rowCount       = $sth->rowCount();
        return $rowCount + 1;
    }

    public function getOrderAllRank_num($count, $options=array()){
        $tablename      = "orderlist as o left join product as p on o.product_id=p.id";
        $product_id     = $options['product_id'];
        if($product_id){
            $condition[]    = "o.product_id={$product_id}";
        }
        $condition[]    = "p.status=1";
        $where  = implode(" AND ", $condition);
        $sql    = "SELECT SUM(o.num) as num FROM $tablename WHERE $where group by o.user_id having num>{$count}";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $rowCount       = $sth->rowCount();
        return $rowCount + 1;
    }



    public function getLastOnePrice($count){
        $tablename      = "orderlist as o left join product as p on o.product_id=p.id";
        $sql    = "SELECT SUM(o.amount) as price FROM $tablename WHERE p.status=1 group by o.user_id having price>{$count} order by price limit 1";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $row    = $sth->fetch();
        return $row['price'];
    }

    public function getRank($user_id, $condition=array(), $options=array()){
        if(count($condition)){
            $condition['fliter_uid']    = $user_id;
            $list   = $this->getOrderUserList($condition, $options);
            $data   = $list[0];
        }else{
            $OrderListUser  = new OrderListUser;
            $data   = $OrderListUser->findone("user_id={$user_id}", array());
        }
        //$rank   = $OrderListUser->getrank($data[$order], $order);

        return array($rank, $data);
    }
    
    public function getRankBySkcStatus($user_id, $condition=array(), $options=array()){
            $condition['fliter_uid']    = $user_id;
            if($condition['color_status']==='0'){
                $options['bak'] = 1;
                $options['status'] = false;
            }
            $list   = $this->getOrderUserList($condition, $options);
            $data   = $list[0];   
        return array($rank, $data);
    }
    
    public function getZDBySkcStatus($user_id=0, $condition=array(), $options=array()){
        if($user_id){
            $condition['master_uid']    = $user_id;
        }       
        if($condition['color_status']==='0'){
            $options['bak'] = 1;
            $options['status'] = false;
        }

        $fields         = "u.*, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price, SUM(o.num) as num, count(DISTINCT o.product_id) as pnum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id,o.product_size_id) as sku";
        if($options['fields_more']){
            $fields .= ",{$options['fields_more']}";
        }
        $options    = $options + array(
            'status'        => true,
            'fields'        => $fields
        );
        //$options['db_debug']=true;
        $list   = $this->_getOrderList($condition, $options);
        $data   = $list[0];
        return $data;
    }

    public function getSUM($colum="price"){
        $list   = $this->getOrderinfoUserList();
        $sum    = 0;
        foreach($list as $row){
            $sum    += $row[$colum];
        }
        return $sum;
    }

    public function getOrderinfoByRank($rank, $order="price"){
        if($rank < 1){
            return array();
        }
        $list  = $this->getOrderinfoUserList($order);
        return $list[$rank - 1];
    }

    public function getOrderMyProductTopList($user_id, $limit, $order="price"){
        $list   = $this->getOrderinfoMyProductList($user_id, $order);
        $len    = count($list);
        if($len > $limit){
            return array_slice($list, 0, $limit);
        }else{
            return $list;
        }
    }

    public function getOrderProductUserTopList($product_id, $limit, $order="price"){
        $list   = $this->getOrderinfoProductUserList($product_id, $order);
        $len    = count($list);
        if($len > $limit){
            return array_slice($list, 0, $limit);
        }else{
            return $list;
        }
    }

    public function getOrderinfoProductUserList($product_id, $order="price"){
        $condition  = array();
        $options    = array();
        $condition['product_id']    = $product_id;
        $list   = $this->getOrderUserList($condition, $options);
        if(in_array($order, array("num", "pnum"))){
            usort($list, function($a, $b){
                return $a['num'] > $b['num'] ? 1 : -1;
            });
        }
        return $list;
    }

    private static $_myproductlist = null, $_mycurrentOrder = "price";
    public function getOrderinfoMyProductList($user_id, $order="price"){
        if(STATIC::$_myproductlist == null){
            STATIC::$_myproductlist     = $this->getOrderProductList(array("user_id"=>$user_id), array());
        }
        if(in_array($order, array("price", "num", "pnum"))){
            if($order != STATIC::$_mycurrentOrder){
                usort(STATIC::$_myproductlist, function($a, $b){
                    return $a['num'] < $b['num'] ? 1 : -1;
                });
                STATIC::$_mycurrentOrder = $oder;
            }
        }
        return STATIC::$_myproductlist;
    }

    private static $_orderinfolist = null, $_currentOrder = "price";
    public function getOrderinfoUserList($order="price"){
        if(STATIC::$_orderinfolist == null){
            STATIC::$_orderinfolist   = $this->getOrderUserList(array(), array());
        }
        if(in_array($order, array("price", "num", "pnum"))){
            if($order != STATIC::$_currentOrder){
                usort(STATIC::$_orderinfolist, function($a, $b){
                    return $a['num'] > $b['num'] ? -1 : 1;
                });
                STATIC::$_currentOrder = $oder;
            }
        }
        return STATIC::$_orderinfolist;
    }


    //  dealer2
    public function getDealer2OrderList($condition=array(), $options=array()){
        $options    = $options + array('status'=>true, 'status_val'=>1);

        $show_all   = $options['show_all'];
        $cond       = array();
        $user_id    = $condition['user_id'];
        $product_id = $condition['product_id'];
        $series_id  = $condition['series_id'];
        $season_id  = $condition['season_id'];
        $brand_id   = $condition['brand_id'];
        $nannvzhuan_id= $condition['nannvzhuan_id'];
        $category_id= $condition['category_id'];
        $classes_id = $condition['classes_id'];
        $wave_id    = $condition['wave_id'];
        $style_id   = $condition['style_id'];
        $price_band_id = $condition['price_band_id'];
        $fliter_uid = $condition['fliter_uid'];
        $group      = $options['group']     ? $options['group']     : "o.product_id";
        $order      = $options['order'];
        $page       = $options['page'];
        $key        = $options['key'];
        $fields_more    = $options['fields_more'];
        $tables_more    = $options['tables_more'];
        $status     = $options['status'];
        $status_val = $options['status_val'];
        if($user_id)    $cond[] = "us.user_id={$user_id}";
        if($series_id)  $cond[] = "p.series_id={$series_id}";
        if($category_id)$cond[] = "p.category_id={$category_id}";
        if($classes_id) $cond[] = "p.classes_id={$classes_id}";
        if($style_id)   $cond[] = "p.style_id={$style_id}";
        if($wave_id)    $cond[] = "p.wave_id={$wave_id}";
        if($price_band_id)  $cond[] = "p.price_band_id={$price_band_id}";
        if($brand_id)   $cond[] = "p.brand_id={$brand_id}";
        if($season_id)  $cond[] = "p.season_id={$season_id}";
        if($nannvzhuan_id)  $cond[] = "p.nannvzhuan_id={$nannvzhuan_id}";
        if($fliter_uid) $cond[] = "o.user_id={$fliter_uid}";
        if($status){
            if(is_numeric($status_val)){
                $cond[] = "p.status={$status_val}";
            }else{
                $cond[] = "p.status<>0";
            }
        }

        $fields     = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price, COUNT(DISTINCT o.user_id) as unum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id,o.product_size_id) as sku,p.status";
        if($fields_more){
            $fields .= ",{$fields_more}";
        }
        $where      = implode(" AND ", $cond);
        if(!$where) $where = "1";
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join user_slave as us on u.id=us.user_slave_id left join user_discount as ud on ud.user_id=us.user_id and ud.category_id=p.category_id";
        if($tables_more){
            $tablename .= " $tables_more";
        }
        $sql        = "SELECT {$fields} FROM {$tablename} WHERE {$where}";
        if($group){
            $sql    .= " GROUP BY $group";
        }
        if($order){
            $sql    .= " ORDER BY $order";
        }
        if($page){
            $limit  = $options['limit']     ? $options['limit']     : 10;
            $start  = ($page - 1) * $limit;
            $sql    .= " LIMIT $start, $limit";
        }
        if($options['db_debug']){
            echo $sql, "<br>";
        }
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $result     = array();
        if($key){
            while($row  = $sth->fetch()){
                $result[$row[$key]] = $row;
            }
        }else{
            while($row  = $sth->fetch()){
                $result[]   = $row;
            }
        }

        return $result;
    }

    public function getDealer2OrderCount($condition=array(), $options=array()){
        $options    = $options + array('status'=>true, 'status_val'=>1);

        $cond       = array();
        $user_id    = $condition['user_id'];
        $product_id = $condition['product_id'];
        $series_id  = $condition['series_id'];
        $season_id  = $condition['season_id'];
        $brand_id   = $condition['brand_id'];
        $nannvzhuan_id= $condition['nannvzhuan_id'];
        $category_id= $condition['category_id'];
        $wave_id    = $condition['wave_id'];
        $style_id   = $condition['style_id'];
        $price_band_id= $condition['price_band_id'];
        $group      = $options['group']     ? $options['group']     : "o.product_id";
        $order      = $options['order'];
        $page       = $options['page'];
        $key        = $options['key'];
        $status     = $options['status'];
        $fliter_uid = $condition['fliter_uid'];
        if($user_id)    $cond[] = "us.user_id={$user_id}";
        if($series_id)  $cond[] = "p.series_id={$series_id}";
        if($category_id)$cond[] = "p.category_id={$category_id}";
        if($style_id)   $cond[] = "p.style_id={$style_id}";
        if($wave_id)    $cond[] = "p.wave_id={$wave_id}";
        if($price_band_id)    $cond[] = "p.price_band_id={$price_band_id}";
        if($brand_id)   $cond[] = "p.brand_id={$brand_id}";
        if($season_id)  $cond[] = "p.season_id={$season_id}";
        if($nannvzhuan_id)  $cond[] = "p.nannvzhuan_id={$nannvzhuan_id}";
        if($fliter_uid) $cond[] = "o.user_id={$fliter_uid}";
        if($status){
            if(is_numeric($status_val)){
                $cond[] = "p.status={$status_val}";
            }else{
                $cond[] = "p.status<>0";
            }
        }

        $where      = implode(" AND ", $cond);
        if(!$where) $where = "1";
        $fields     = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price,{$this->DISCOUNT_CONDITION} as discount_price, COUNT(DISTINCT o.user_id) as unum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id, o.product_size_id) as sku";
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join user_slave as us on u.id=us.user_slave_id left join user_discount as ud on ud.user_id=us.user_id and ud.category_id=p.category_id";
        $sql        = "SELECT {$fields} FROM {$tablename} WHERE {$where}";
        // echo $sql;
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $row    = $sth->fetch();
        return $row;
    }


    public function getSlaveOrderList($user_id, $product_id){
        $fields     = "o.*,u.name";
        $tablename  = "orderlist as o left join user_slave as us on o.user_id=us.user_slave_id left join user as u on o.user_id=u.id";
        $condition  = array();
        $condition[]    = "us.user_id={$user_id}";
        $condition[]    = "o.product_id={$product_id}";
        $where  = implode(" AND ", $condition);

        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['order']       = 'o.post_time';
        $options['limit']       = 10000;

        return $this->find($where, $options);
    }

    public function getSlaveOrderinfo($user_id, $produc_id=0, $DISCOUNT_CONDITION=null){
        // if($DISCOUNT_CONDITION === null){
            $DISCOUNT_CONDITION = $this->DISCOUNT_CONDITION_UM;
        // }
        $fields     = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price,{$DISCOUNT_CONDITION} as discount_price, COUNT(DISTINCT o.user_id) as unum, COUNT(DISTINCT o.product_id,o.product_color_id) as sku";
        $tablename  = "orderlist as o left join user_slave as us on o.user_id=us.user_slave_id left join user as u on o.user_id=u.id left join product as p on o.product_id=p.id left join user_discount as ud on ud.user_id=us.user_id and ud.category_id=p.category_id left join user as um on um.id=us.user_id";
        $condition  = array();
        $condition[]    = "us.user_id={$user_id}";
        if($product_id) $condition[]    = "o.product_id={$product_id}";
        $condition[]    = "o.num>0";
        $condition[]    = "p.status=1";
        $where  = implode(" AND ", $condition);

        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        // $options['db_debug']    = true;

        return $this->findone($where, $options);
    }


    public function getSelfOrderinfo($user_id, $product_id=0){
        // $User   = new User;
        // $u      = $User->findone("mid={$user_id}");
        // if($u['id']){
        //     $discount   = $u['discount'];
        //     $DISCOUNT_CONDITION     = "SUM(o.num * p.price * IF(p.category_id=".HDT_PRODUCT_ACC_CATEGORY.",".HDT_PRODUCT_ACC_DISCOUNT.",{$discount}))";
        // }
        return $this->getSlaveOrderinfo($user_id, $product_id);//, $DISCOUNT_CONDITION);
    }
    
    public function getAdSelfOrderinfo($user_id, $product_id=0){
        return $this->getSlaveOrderinfo($user_id, $product_id , 'SUM(o.num * IF(u.discount_type AND p.price_purchase<>0, p.price_purchase, p.price * IF(ud.category_discount, ud.category_discount, um.discount)))');
    }

    public function getSlaveOrderCSList($user_id, $product_id=0, $status=true){
        $fields     = "SUM(o.num) as num, o.product_id, o.product_color_id, o.product_size_id";
        $tablename  = "orderlist as o left join user_slave as us on o.user_id=us.user_slave_id left join user as u on o.user_id=u.id left join product as p on o.product_id=p.id";
        $condition  = array();
        $condition[]    = "us.user_id={$user_id}";
        $condition[]    = "o.num>0";
        if($product_id) $condition[]    = "o.product_id={$product_id}";
        if($status)     $condition[]    = "p.status=1";
        $where  = implode(" AND ", $condition);

        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['limit']       = 1000;
        $options['group']       = "o.product_id, o.product_color_id, o.product_size_id";
        // $options['db_debug']    = true;

        return $this->find($where, $options);
    }

    public function getSlaveOrderUCSList($user_id, $product_id=0, $status=true){
        $fields     = "SUM(o.num) as num, o.user_id,o.product_id, o.product_color_id, o.product_size_id, u.name";
        $tablename  = "orderlist as o left join user_slave as us on o.user_id=us.user_slave_id left join user as u on o.user_id=u.id left join product as p on o.product_id=p.id";
        $condition  = array();
        $condition[]    = "us.user_id={$user_id}";
        $condition[]    = "o.num>0";
        if($product_id) $condition[]    = "o.product_id={$product_id}";
        if($status)     $condition[]    = "p.status=1";
        $where  = implode(" AND ", $condition);

        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['limit']       = 10000;
        $options['group']       = "o.user_id, o.product_id, o.product_color_id, o.product_size_id";
        // $options['db_debug']    = true;

        return $this->find($where, $options);
    }

    public function getAdOrderinfo($area1, $area2, $produc_id=0){
        $fields     = "COUNT(o.product_id) as pnum, SUM(o.num) as num, SUM(o.price) as price, SUM(discount_price) as discount_price";
        $tablename  = "orderlistproduct as o left join product as p on o.product_id=p.id";
        $condition  = array();
        if($product_id) $condition[]    = "o.product_id={$product_id}";
        $condition[]    = "o.num>0";
        $condition[]    = "p.status=1";
        $where  = implode(" AND ", $condition);
        $sql    = "SELECT {$fields} FROM {$tablename} WHERE {$where}";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        return $sth->fetch();
    }
	public function getAdOrderInfo1($area1=null, $area2=null, $user_id=0){
		$options['tablename'] 	= "orderlistuser as o left join user as u on o.user_id=u.id";
		$options['fields']	= "sum(o.price) as price, sum(o.discount_price) as discount_price, sum(o.num) as num";
		if($area1)	$condition[]	= "u.area1={$area1}";
		if($area2)	$condition[]	= "u.area2={$area2}";
		if($user_id)	$condition[]	= "u.id={$user_id}";
		if($area2){
			//$options['group']	= "u.area2";
		}else{
			$options['group']	= "u.area1";
		}
		// $options['db_debug'] = true;
		$where = implode(" AND ", $condition);
		return $this->findone($where, $options);
	}

    public function getAdOrderList($area1, $area2, $product_id){
        $fields     = "o.*,u.name";
        $tablename  = "orderlist as o left join user as u on o.user_id=u.id";
        $condition  = array();
        // if($area1)  $condition[]    = "u.area1={$area1}";
        // if($area2)  $condition[]    = "u.area2={$area2}";
        $condition[]    = "o.product_id={$product_id}";
        $where  = implode(" AND ", $condition);
        $sql    = "SELECT {$fields} FROM {$tablename} WHERE {$where} ORDER BY o.post_time asc";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $result = array();
        while($row = $sth->fetch()){
            $result[]   = $row;
        }
        return $result;
    }

    public function getAdProOrderList($aid, $product_id){
        $fields     = "o.*,u.name";
        $tablename  = "orderlist as o left join user as u on o.user_id=u.id";
        $condition  = array();
        if($aid)  $condition[]    = "u.ad_id={$aid}";
        //if($area2)  $condition[]    = "u.area2={$area2}";
        $condition[]    = "o.product_id={$product_id}";
        $where  = implode(" AND ", $condition);
        $sql    = "SELECT {$fields} FROM {$tablename} WHERE {$where} ORDER BY o.post_time asc";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $result = array();
        while($row = $sth->fetch()){
            $result[]   = $row;
        }
        return $result;
    }


    public function get_display_list($display_id, $user_id){
        $fields     = "o.*";
        $tablename  = "orderlist as o left join product_display_member_color as d on o.product_id=d.product_id";
        $condition  = array();
        $condition[]    = "d.display_id={$display_id}";
        $condition[]    = "o.user_id={$user_id}";
        $where          = implode(" AND ", $condition);
        $options['fields']      = $fields;
        $options['tablename']   = $tablename;
        $options['limit']       = 10000;
        // $options['db_debug']    = true;
        return $this->find($where, $options);
    }

    public function get_group_list($group_id, $user_id){
        $fields     = "o.*";
        $tablename  = "orderlist as o left join product_group_member as pm on o.product_id=pm.product_id";
        $condition  = array();
        $condition[]    = "pm.group_id={$group_id}";
        $condition[]    = "o.user_id={$user_id}";
        $where          = implode(" AND ", $condition);
        $options['fields']      = $fields;
        $options['tablename']   = $tablename;
        $options['limit']       = 10000;
        // $options['db_debug']    = true;
        return $this->find($where, $options);
    }

    public function get_show_list($show_id, $user_id){
        $ProductShow    = new ProductShow;
        $show           = $ProductShow->findone("id={$show_id}");
        $product_ids    = $show['product_ids'];
        if(!$product_ids) return array();

        $condition[]    = "user_id={$user_id}";
        $condition[]    = "product_id in ({$product_ids})";
        $where      = implode(' AND ', $condition);
        $options['limit']   = 10000;
        return $this->find($where, $options);
    }

    public function refresh_product_id($product_id){
        $list   = $this->find("product_id={$product_id}", array("group"=>"user_id", "fields"=>"user_id", "limit"=>10000));
        $OrderListUser  = new OrderListUser;
        foreach($list as $row){
            $user_id    = $row['user_id'];
            $OrderListUser->refresh($user_id);
        }
    }


    public function get_user_orderlist_info($user_id, $product_id=false, $color_id=false, $size_id=false, $discount=false, $filter=false){
        $DISCOUNT_CONDITION     = $this->DISCOUNT_CONDITION;
        $DISCOUNT_PRICE         = $this->DISCOUNT_PRICE;
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";

        // $fields     = "o.product_id,o.product_color_id,p.name,p.size_group_id,p.wave_id,p.category_id,p.edition_id,p.contour_id,p.brand_id,p.theme_id,p.style_id,p.season_id,p.classes_id,p.kuanhao,p.bianhao,p.series_id,SUM(o.num) as num,p.price as p_price,SUM(o.amount) as price,{$DISCOUNT_CONDITION} as discount_price,GROUP_CONCAT(o.product_size_id,':',o.num) as F,pc.skc_id,pc.color_code";
        $fields     = "o.product_id,o.product_color_id,p.*,SUM(o.num) as num,p.price as p_price,SUM(o.amount) as price,{$DISCOUNT_CONDITION} as discount_price,GROUP_CONCAT(o.product_size_id,':',o.num) as F,pc.skc_id,pc.color_code,pc.main_push_id";
        //$condition[]    = "p.status=1";
        $condition[]    = "p.status<>0";
        $condition[]    = "o.user_id in ($user_id)";
        if($product_id) $condition[]    = "o.product_id=$product_id";
        if($color_id)   $condition[]    = "o.product_color_id=$color_id";
        if($size_id)    $condition[]    = "o.product_size_id=$size_id";
        if($filter)     $condition[]    = $filter;
        $where      = implode(" AND ", $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['group']       = "o.product_id,o.product_color_id";
        $options['order']       = "pc.skc_id asc";
        $options['limit']       = 10000;
        // $options['db_debug']    = true;
        return $this->find($where, $options);
    }

    public function get_orderlist_info_filter($condition=array(), $options=array()){
        $discount   = $condition['discount'];
        $DISCOUNT_CONDITION     = $this->DISCOUNT_CONDITION;
        $keys   = array('area1', 'area2', 'ad', 'username', 'bianhao');
        foreach($keys as $key){
            $val = $condition[$key];
            if(is_array($val)){
                $val = implode(",", array_map(function($v){ return "'" . addslashes($v) . "'"; }, $val) );
            }
            if($val){
                $cond[]    = "u.{$key} in ({$val})";
            }
        }
        $keys   = array('brand_id', 'series_id', 'season_id', 'category_id', 'classes_id', 'wave_id', 'style_id', 'nannvzhuan_id', 'status');
        foreach($keys as $key){
            $val = $condition[$key];
            if(is_array($val)){
                $val = implode(",", $val);
            }
            if($val){
                $cond[]    = "p.{$key} in ({$val})";
            }
        }
        if($condition['master_uid']){
            $cond[]    = "us.user_id in ({$master_uid})";
        }

        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id left join user_slave as us on o.user_id=us.user_slave_id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
        $fields     = "o.product_id,o.product_color_id,o.product_size_id,p.name,p.wave_id,p.category_id,p.wave_id,p.season_id,p.brand_id,p.kuanhao,p.bianhao,SUM(o.num) as num,p.price as p_price,SUM(o.amount) as price,{$DISCOUNT_CONDITION} as discount_price,pc.skc_id";
        $cond[]    = "p.status<>0";
        $where      = implode(" AND ", $cond);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['group']       = "o.product_id,o.product_color_id,o.product_size_id";
        if(!$options['order']) $options['order']       = "pc.skc_id asc";
        $options['limit']       = 100000;
        // $options['db_debug']    = true;
        return $this->find($where, $options);
    }

    public function get_wrong_orderlist($para=array(),$wrong=0, $options){
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join product_color pc on o.product_id = pc.product_id AND o.product_color_id = pc.color_id AND pc.status =1   ";
        $fields     = "u.name as uname, p.name as pname, p.bianhao, p.kuanhao, o.* ,pc.skc_id";
        $condition[]    = "o.num>={$wrong}";
        if($para['uid'])    $condition[]    = "o.user_id={$para['uid']}";
        if($para['user_id_str']) $condition[]    = "o.user_id in (".$para['user_id_str'].") ";
        if($para['ad_id']) $condition[]    = "u.ad_id={$para['ad_id']}";
        $where      = implode(' AND ', $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['order']       = "o.post_time desc";
        //$options['db_debug'] = true;
        return $this->find($where, $options);
    }

    public function get_order_info($params=array()){
        $area1  = $params['area1'];
        $area2  = $params['area2'];
        $product_id     = $params['product_id'];
        $color_id       = $params['color_id'];
        $master_uid     = $params['master_uid'];
        $ad_id     = $params['ad_id'];
        if($master_uid) return $this->get_order_info_zongdai($master_uid);
        if($area1)  $condition[]    = "u.area1={$area1}";
        if($area2)  $condition[]    = "u.area2={$area2}";
        if($ad_id)  $condition[]    = "u.ad_id={$ad_id}";
        if($product_id) $condition[]    = "o.product_id={$product_id}";
        if($color_id)   $condition[]    = "o.product_color_id={$color_id}";
        $condition[]    = "p.status=1";
        $options['tablename']   = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
        $options['fields']      = "SUM(o.num) as num, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price";
        // $options['db_debug']    = true;
        $where  = implode(' AND ', $condition);
        $info   = $this->findone($where, $options);
        return $info;
    }

    private function get_order_info_zongdai($master_uid){
        $condition[]    = "p.status=1";
        $condition[]    = "us.user_id={$master_uid}";
        $options['tablename']   = "orderlist as o left join product as p on o.product_id=p.id left join user_slave as us on o.user_id=us.user_slave_id left join user as u on u.id=o.user_id";
        $options['fields']      = "SUM(o.num) as num, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price";
        $where  = implode(' AND ', $condition);
        $info   = $this->findone($where, $options);
        return $info;
    }

    public function refresh_index_user($user_id=0){
        $OrderListUser  = new OrderListUser;
        if($user_id){
            $result     = $OrderListUser->refresh($user_id);
        }else{
            $User       = new User;
            $ulist      = $User->find("type=1", array("limit"=>10000));
            foreach($ulist as $u){
                $OrderListUser->refresh($u['id']);
            }
        }
        return $result;
    }

    public function refresh_index_product($product_id=0){
        $OrderListProduct   = new OrderListProduct;
        if($product_id){
            $OrderListProduct->refresh($product_id);
        }else{
            $Product        = new Product;
            $plist          = $Product->find("", array("limit"=>10000));
            foreach($plist as $p){
                $OrderListProduct->refresh($p['id']);
            }
        }
    }

    public function get_product_color_rank($product_id, $color_id){
        if(!$this->OrderListProductColor){
            $this->OrderListProductColor = new OrderListProductColor;
        }
        return $this->OrderListProductColor->get_product_color_rank($product_id, $color_id);
        
        $info   = $this->get_order_info(array('product_id'=>$product_id, 'color_id' =>$color_id));
        $num = $info['num'];
        return $this->get_product_color_rank_by_num($num);
    }

    public function get_product_color_rank_by_num($num){
        if($num > 0){
            $options['tablename']   = "orderlist as o left join product as p on o.product_id=p.id";
            $options['group']       = "o.product_id,o.product_color_id";
            $options['having']      = "n>{$num}";
            $options['fields']      = "SUM(o.num) as n";
            $options['limit']       = 10000;
            $condition[]    = "p.status=1";
            $where  = implode(" AND ", $condition);
            $list   = $this->find($where, $options);
            $count  = count($list);
            return $count + 1;
        }else{
            return null;
        }
    }

    public function build_area_rank_list($area1){
        if(!$this->_area_rank_list){
            $this->_area_rank_list = array();
            $rank_list  = $this->get_area_rank_list($area1);
            $rank       = 1;
            foreach($rank_list as $row) {
                $this->_area_rank_list[$row['id']]   = $rank;
                $rank++;
            }
        }
        return $this->_area_rank_list;
    }
    public function build_master_rank_list($mid){
        if(!$this->_master_rank_list){
            $this->_master_rank_list = array();
            $rank_list  = $this->get_master_rank_list($mid);
            $rank       = 1;
            foreach($rank_list as $row) {
                $this->_master_rank_list[$row['id']]   = $rank;
                $rank++;
            }
        }
        return $this->_master_rank_list;
    }
    public function get_area_rank($product_id, $area1) {
        $_area_rank_list = $this->build_area_rank_list($area1);
        return $_area_rank_list[$product_id];
    }

    public function get_area_rank_list($area1, $params=array(), $options=array()){
        $that = $this;
        $params['area1']    = $area1;
        $options['limit']   = 10000;
        $cache  = new Cache(function($area1, $params, $options) use ($that){
            return $that->getOrderProductList1($params, $options);
        }, 60);
        $cache_string       = "AREA_RANK_LIST_{$area1}_" . md5(serialize($params) . serialize($options));
        return $cache->get($cache_string, array($area1, $params, $options));
    }

    public function get_master_rank($product_id, $mid) {
        if($mid){
            $_master_rank_list = $this->build_master_rank_list($mid);
            return $_master_rank_list[$product_id];
        }
        return null;
    }

    public function get_master_rank_list($mid){
        $that = $this;
        if($mid){
            $cache  = new Cache(function($mid) use ($that){
                return $that->getOrderProductList1(array('master_uid'=>$mid), array("limit"=>10000));
            }, 60);
            return $cache->get("MASTER_RANK_LIST_{$mid}", array($mid));
        }
        return array();
    }

    public function get_area_color_rank($product_id, $product_color_id, $area1) {
        $rank_list  = $this->get_area_color_rank_list($area1);
        $rank       = 1;
        foreach($rank_list as $row) {
            if($product_id == $row['id'] && $product_color_id == $row['product_color_id']){
                return $rank;
            }
            $rank++;
        }
        return null;
    }

    public function get_area_color_rank_list($area1) {
        $that = $this;
        $cache  = new Cache(function($area1) use ($that){
            return $that->getOrderProductList1(array('area1'=>$area1), array("limit"=>10000, "group"=>"o.product_id,o.product_color_id", "fields_more"=>"o.product_color_id"));
        }, 60);
        return $cache->get("AREA_COLOR_RANK_LIST_{$area1}", array($area1));
    }

    public function get_master_color_rank($product_id, $product_color_id, $mid) {
        $rank_list  = $this->get_master_color_rank_list($mid);
        $rank       = 1;
        foreach($rank_list as $row) {
            if($product_id == $row['id'] && $product_color_id == $row['product_color_id']){
                return $rank;
            }
            $rank++;
        }
        return null;
    }

    public function get_master_color_rank_list($mid){
        $that = $this;
        $cache  = new Cache(function($mid) use ($that){
            return $that->getOrderProductList1(array('master_uid'=>$mid), array("limit"=>10000, "group"=>"o.product_id,o.product_color_id", "fields_more"=>"o.product_color_id"));
        }, 60);
        return $cache->get("MASTER_COLOR_RANK_LIST_{$mid}", array($mid));
    }

    public function getAdOrderListInfo($condition=array(), $options=array()){
        $options    = $options + array('status'=>true, 'status_val'=>1);

        $show_all   = $options['show_all'];
        $cond       = array();
        $user_id    = $condition['user_id'];
        $product_id = $condition['product_id'];
        $series_id  = $condition['series_id'];
        $season_id  = $condition['season_id'];
        $brand_id   = $condition['brand_id'];
        $nannvzhuan_id= $condition['nannvzhuan_id'];
        $category_id= $condition['category_id'];
        $classes_id = $condition['classes_id'];
        $wave_id    = $condition['wave_id'];
        $style_id   = $condition['style_id'];
        $price_band_id = $condition['price_band_id'];
        //$fliter_uid = $condition['fliter_uid'];
        $area2 = $condition['area2'];
        $user_id_str = $condition['user_id_str'];
        $group      = $options['group']     ? $options['group']     : "o.product_id";
        $order      = $options['order'];
        $page       = $options['page'];
        $key        = $options['key'];
        $fields_more    = $options['fields_more'];
        $tables_more    = $options['tables_more'];
        $status     = $options['status'];
        $status_val = $options['status_val'];

        if($series_id)  $cond[] = "p.series_id={$series_id}";
        if($category_id)$cond[] = "p.category_id={$category_id}";
        if($classes_id) $cond[] = "p.classes_id={$classes_id}";
        if($style_id)   $cond[] = "p.style_id={$style_id}";
        if($wave_id)    $cond[] = "p.wave_id={$wave_id}";
        if($price_band_id)  $cond[] = "p.price_band_id={$price_band_id}";
        if($brand_id)   $cond[] = "p.brand_id={$brand_id}";
        if($season_id)  $cond[] = "p.season_id={$season_id}";
        if($nannvzhuan_id)  $cond[] = "p.nannvzhuan_id={$nannvzhuan_id}";
        //if($fliter_uid) $cond[] = "o.user_id={$fliter_uid}";
        if($user_id){
           $cond[] = "o.user_id={$user_id}";
        }else{

           $cond[] = "o.user_id in (".($user_id_str?$user_id_str:0).") ";
        }
        if($status){
            if(is_numeric($status_val)){
                $cond[] = "p.status={$status_val}";
            }else{
                $cond[] = "p.status<>0";
            }
        }

        $fields     = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price, {$this->DISCOUNT_CONDITION} as discount_price, COUNT(DISTINCT o.user_id) as unum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id,o.product_size_id) as sku,p.status";
        if($fields_more){
            $fields .= ",{$fields_more}";
        }
        $where      = implode(" AND ", $cond);
        if(!$where) $where = "1";
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id  left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
        if($tables_more){
            $tablename .= " $tables_more";
        }
        $sql        = "SELECT {$fields} FROM {$tablename} WHERE {$where}";
        if($group){
            $sql    .= " GROUP BY $group";
        }
        if($order){
            $sql    .= " ORDER BY $order";
        }
        if($page){
            $limit  = $options['limit']     ? $options['limit']     : 10;
            $start  = ($page - 1) * $limit;
            $sql    .= " LIMIT $start, $limit";
        }
        if($options['db_debug']){
            echo $sql, "<br>";
        }
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $result     = array();
        if($key){
            while($row  = $sth->fetch()){
                $result[$row[$key]] = $row;
            }
        }else{
            while($row  = $sth->fetch()){
                $result[]   = $row;
            }
        }

        return $result;
    }

    public function getAdOrderCount($condition=array(), $options=array()){
        $options    = $options + array('status'=>true, 'status_val'=>1);

        $cond       = array();
        $user_id    = $condition['user_id'];
        $product_id = $condition['product_id'];
        $series_id  = $condition['series_id'];
        $season_id  = $condition['season_id'];
        $brand_id   = $condition['brand_id'];
        $nannvzhuan_id= $condition['nannvzhuan_id'];
        $category_id= $condition['category_id'];
        $wave_id    = $condition['wave_id'];
        $style_id   = $condition['style_id'];
        $price_band_id= $condition['price_band_id'];
        $group      = $options['group']     ? $options['group']     : "o.product_id";
        $order      = $options['order'];
        $page       = $options['page'];
        $key        = $options['key'];
        $status     = $options['status'];
        //$fliter_uid = $condition['fliter_uid'];
        $user_id_str = $condition['user_id_str'];
        //if($user_id)    $cond[] = "us.user_id={$user_id}";
        if($series_id)  $cond[] = "p.series_id={$series_id}";
        if($category_id)$cond[] = "p.category_id={$category_id}";
        if($style_id)   $cond[] = "p.style_id={$style_id}";
        if($wave_id)    $cond[] = "p.wave_id={$wave_id}";
        if($price_band_id)    $cond[] = "p.price_band_id={$price_band_id}";
        if($brand_id)   $cond[] = "p.brand_id={$brand_id}";
        if($season_id)  $cond[] = "p.season_id={$season_id}";
        if($nannvzhuan_id)  $cond[] = "p.nannvzhuan_id={$nannvzhuan_id}";
        if($user_id){
            $cond[] = "o.user_id={$user_id}";
        }else{

            $cond[] = "o.user_id in (".($user_id_str?$user_id_str:0).") ";
        }
        //if($fliter_uid) $cond[] = "o.user_id={$fliter_uid}";
        if($status){
            if(is_numeric($status_val)){
                $cond[] = "p.status={$status_val}";
            }else{
                $cond[] = "p.status<>0";
            }
        }

        $where      = implode(" AND ", $cond);
        if(!$where) $where = "1";
        $fields     = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price,{$this->DISCOUNT_CONDITION} as discount_price, COUNT(DISTINCT o.user_id) as unum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id, o.product_size_id) as sku";
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id  left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
        $sql        = "SELECT {$fields} FROM {$tablename} WHERE {$where}";
        $sth    = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $row    = $sth->fetch();
        return $row;
    }

    public function getOrderinfoByArea($area){
        $User=new User();
        $userlist = $User->find('area2="'.$area.'"',array('fields'=>'  GROUP_CONCAT(id) as  userlist'));
        $user_id_str = $userlist[0]['userlist'];
        $fields     = "COUNT(DISTINCT o.product_id) as pnum, SUM(o.num) as num, SUM(o.amount) as price,{$this->DISCOUNT_CONDITION} as discount_price, COUNT(DISTINCT o.user_id) as unum, COUNT(DISTINCT o.product_id,o.product_color_id) as skc, COUNT(DISTINCT o.product_id,o.product_color_id, o.product_size_id) as sku";
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id  left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
        $condition  = array();
        $condition[]    = "p.status <> 0 ";
        $condition[] = "o.user_id in (".($user_id_str?$user_id_str:0).") ";
        $where  = implode(" AND ", $condition);

        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        //$options['db_debug']      = true;
        return $this->findone($where, $options);
    }

    public function check_user_can_order ($User) {
        $Company    = new Company;
        if($Company->lock_order_message){
            return '下单已被锁定';
            //return $Company->lock_order_message;
        }
        if($User->is_lock){
            return "帐号已锁定,保存失败";
        }else{
            $u      = $User->findone("id={$User->id}");
            if($u['is_lock']){
                SESSION::set('user', "");
                return "帐号已锁定,保存失败";
            }
            if($Company->check_order){
                if($u['order_status']==1||$u['order_status']==3){
                    return "订单已锁定,保存失败";
                }
            }
        }
        return null;
    }
    
    public function get_user_orderlist_info_new($user_id, $fields_more='',$product_id=false, $color_id=false, $size_id=false, $filter=false){
        $DISCOUNT_CONDITION     = $this->DISCOUNT_CONDITION;
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id";
    
        $fields     = "o.product_id,o.product_color_id,p.name,p.wave_id,p.category_id,p.brand_id,p.theme_id,p.style_id,p.season_id,p.wave_id,p.kuanhao,p.bianhao,p.series_id,SUM(o.num) as num,p.price as p_price,SUM(o.amount) as price,{$DISCOUNT_CONDITION} as discount_price,GROUP_CONCAT(o.product_size_id,':',o.num) as F,pc.skc_id,p.huohao,p.classes_id,pc.color_code,pc.main_push_id,p.price_purchase,p.designer,p.theme_id,p.brand_id,p.sxz_id,p.nannvzhuan_id,p.neiwaida_id,p.changduankuan_id,p.date_market,o.product_size_id,{$this->DISCOUNT_PRICE} as p_d_price".($fields_more?",".$fields_more:'');
        //$condition[]    = "p.status=1";
        $condition[]    = "p.status<>0";
        $condition[]    = "o.user_id = $user_id";
        if($product_id) $condition[]    = "o.product_id=$product_id";
        if($color_id)   $condition[]    = "o.product_color_id=$color_id";
        if($size_id)    $condition[]    = "o.product_size_id=$size_id";
        if($filter)     $condition[]    = $filter;
        $where      = implode(" AND ", $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['group']       = "o.product_id,o.product_color_id";
        $options['order']       = "o.product_id,o.product_color_id";
        $options['limit']       = 10000;
        //$options['db_debug']    = true;
        return $this->find($where, $options);
    }
    
    public function get_user_orderlist_sku_info($user_id, $fields_more='',$product_id=false, $color_id=false, $size_id=false, $filter=false){
        $DISCOUNT_CONDITION     = $this->DISCOUNT_CONDITION;
        $tablename  = "orderlist as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
    
        $fields     = "o.product_id,o.product_color_id,p.kuanhao,p.bianhao,SUM(o.num) as num,p.price as p_price,SUM(o.amount) as price,{$DISCOUNT_CONDITION} as discount_price,pc.skc_id,pc.color_code,p.price_purchase,o.product_size_id,{$this->DISCOUNT_PRICE} as p_d_price".($fields_more?",".$fields_more:'');
        $condition[]    = "p.status<>0";
        $condition[]    = "o.user_id = {$user_id}";
        if($product_id) $condition[]    = "o.product_id=$product_id";
        if($color_id)   $condition[]    = "o.product_color_id=$color_id";
        if($size_id)    $condition[]    = "o.product_size_id=$size_id";
        if($filter)     $condition[]    = $filter;
        $where      = implode(" AND ", $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['group']       = "o.product_id,o.product_color_id,o.product_size_id";
        $options['order']       = "pc.skc_id asc";
        $options['limit']       = 100000;
        //$options['db_debug']    = true;
        return $this->find($where, $options);
    }
    
    public function get_user_condition_orderlist($user_id,$cond=array(),$order){
        $condition = array();
        $DISCOUNT_CONDITION     = $this->DISCOUNT_CONDITION;
        $tableFrom = 'orderlist';
        if($cond['color_status']==='0'){
             $tableFrom = 'orderlistbak';
        }
        $tablename  = $tableFrom." as o left join product as p on o.product_id=p.id left join user as u on o.user_id=u.id left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id left join user_discount as ud on ud.user_id=o.user_id and ud.category_id=p.category_id";
    
        $fields     = "o.product_id,o.product_color_id,p.name,p.kuanhao,p.bianhao,p.size_group_id,p.category_id,p.classes_id,p.wave_id,p.style_id,p.price_band_id,p.brand_id,p.series_id,p.theme_id,p.nannvzhuan_id,p.sxz_id,p.season_id,p.defaultimage,p.mininum,SUM(o.num) as num,p.price as p_price,SUM(o.amount) as price,{$DISCOUNT_CONDITION} as discount_price,GROUP_CONCAT(o.product_size_id,':',o.num) as F,pc.skc_id";
        //$condition[]    = "p.status=1";
       
        if($user_id){
            $condition[]    = "o.user_id in ($user_id)";
        }
        if($cond['area1']){
            $condition[]    = "u.area1 = '".$cond['area1']."' ";
        }
        if($cond['area2']){
            $condition[]    = "u.area2 = '".$cond['area2']."' ";
        }
        if($cond['fliter_uid']){
            $condition[]    = "u.id = '".$cond['fliter_uid']."' ";
        }
        
        if($cond['category_id']){
            $condition[]    = "p.category_id = '".$cond['category_id']."' ";
        }
        if($cond['medium_id']){
            $condition[]    = "p.medium_id = '".$cond['medium_id']."' ";
        }
        
        if($cond['classes_id']){
            $condition[]    = "p.classes_id = '".$cond['classes_id']."' ";
        }
        if($cond['wave_id']){
            $condition[]    = "p.wave_id = '".$cond['wave_id']."' ";
        }
        if($cond['style_id']){
            $condition[]    = "p.style_id = '".$cond['style_id']."' ";
        }
        if($cond['price_band_id']){
            $condition[]    = "p.price_band_id = '".$cond['price_band_id']."' ";
        }
        if($cond['brand_id']){
            $condition[]    = "p.brand_id = '".$cond['brand_id']."' ";
        }
        if($cond['series_id']){
            $condition[]    = "p.series_id = '".$cond['series_id']."' ";
        }
        if($cond['theme_id']){
            $condition[]    = "p.theme_id = '".$cond['theme_id']."' ";
        }
        if($cond['nannvzhuan_id']){
            $condition[]    = "p.nannvzhuan_id= '".$cond['nannvzhuan_id']."' ";
        }
        if($cond['sxz_id']){
            $condition[]    = "p.sxz_id = '".$cond['sxz_id']."' ";
        }
        if($cond['season_id']){
            $condition[]    = "p.season_id = '".$cond['season_id']."' ";
        }
        if($cond['color_status']!=='0'){
            $condition[]    = "p.status = 1 ";
        }       
        if($cond['isspot']){
            $condition[] = "p.isspot = {$cond['isspot']}";
        }   
             
        switch($order){
            case 'skc asc':
                $ordertype = 'pc.skc_id asc';
                break;
            case 'skc desc':
                $ordertype = 'pc.skc_id desc';
                break;
            case 'num asc':
                $ordertype = 'num asc';
                break;
            case 'num desc':
                $ordertype = 'num desc';
                break;
            default:
                $ordertype = 'pc.skc_id asc';
                break;
        }
        
        $where      = implode(" AND ", $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['group']       = "o.product_id,o.product_color_id";
        $options['order']       = $ordertype;
        $options['limit']       = 10000;
        //$options['db_debug']    = true;
        return $this->find($where, $options);
    }

    // 获取skc的订量明细
    public function get_skc_size_list ($product_id, $product_color_id, $params=array()){
        $options['tablename']   = "orderlist as o left join user as u on o.user_id=u.id";
        $options['fields']      = "o.product_size_id,sum(o.num) as num";
        $options['group']       = "o.product_size_id";
        $options['limit']       = 100;
        $condition[]    = "o.product_id={$product_id}";
        $condition[]    = "o.product_color_id={$product_color_id}";
        if($user_id     = $params['user_id']){
            $condition[]    = "o.user_id={$user_id}";
        }
        if($mid         = $params['mid']){
            $condition[]    = "o.zd_user_id={$mid}";
        }
        if($ad_id       = $params['ad_id']){
            $condition[]    = "u.ad_id={$ad_id}";
        }
        if($area1   = $params['area1']){
            $condition[]    = "u.area1={$area1}";
        }
        if($area2   = $params['area2']){
            $condition[]    = "u.area2={$area2}";
        }
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

    public function get_product_price_changed_orderlist () {
        $options['tablename']   = "product as p left join orderlist as o on p.id=o.product_id";
        $options['fields']      = "o.*";
        $options['limit']       = 10000;
        $condition[]            = "p.price<>o.unit_price";
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

    public function get_product_color_list($where){
        $options    =   array();
        $options['fields']      = "product_id,product_color_id,sum(num) as num,sum(amount) as price,sum(discount_amount) as discount_price";
        $options['group']       = "product_id,product_color_id";
        $options['limit']       = "10000";
        //$options['db_debug']    =true;
        return $this->find($where,$options);
    }
    
    public function get_product_list($where){
        $options    =   array();
        $options['fields']      = "product_id,sum(num) as num,sum(amount) as price,sum(discount_amount) as discount_price";
        $options['group']       = "product_id";
        $options['limit']       = "10000";
        //$options['db_debug']    =true;
        return $this->find($where,$options);
    }

    public function get_user_product_color_rank($user_id,$num,$params=array()){
        //$options['db_debug']= true;
        $options['fields']  = "product_id,product_color_id";
        $options['group']   = "product_id,product_color_id";
        $options['having']  = "sum(num)>{$num}";
        $options['limit']   = 10000;
        if($params['category_id']){
            $options['tablename']   =   "orderlist as o left join product as p on o.product_id=p.id";
            $condition[] = "p.category_id={$params['category_id']}";
        }
        $condition[] = "user_id={$user_id}";
        $where  = implode(" and ", $condition);
        $list   = $this->find($where, $options);
        return count($list) + 1;
    }


    public function get_user_product_rank($user_id,$num,$params=array()){
        //$options['db_debug']= true;
        $options['fields']  = "product_id";
        $options['group']   = "product_id";
        $options['having']  = "sum(num)>{$num}";
        $options['limit']   = 10000;
        if($params['category_id']){
            $options['tablename']   =   "orderlist as o left join product as p on o.product_id=p.id";
            $condition[] = "p.category_id={$params['category_id']}";
        }
        $condition[] = "user_id={$user_id}";
        $where  = implode(" and ", $condition);
        $list   = $this->find($where, $options);
        return count($list) + 1;
    }

    public function get_user_order($user_id){
        $options['fields'] = "product_id,product_color_id,sum(num) as num,sum(amount) as amount";
        $options['group']  = "product_id,product_color_id";
        $options['limit']  = 10000;
        $where = "user_id={$user_id}";
        return $this->find($where,$options);
    }
}




