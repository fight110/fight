<?php

class Control_orderlist {

    public static function Action_proportion_list ($r) {
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $product_id = $data->product_id;

        $User                   = new User;
        $OrderListProportion    = new OrderListProportion;
        $options['limit']       = 100;
        $condition[]    = "user_id={$User->id}";
        $condition[]    = "product_id={$product_id}";
        $where      = implode(" AND ", $condition);
        $list       = $OrderListProportion->find($where, $options);
        $result['list'] = $list;

        Flight::json($result);
    }

    public static function Action_proportion_add($r) {
        Flight::validateUserHasLogin();
        $data       = $r->data;
        $OrderList  = new OrderList;
        $User       = new User;
        $message    = $OrderList->check_user_can_order($User);
        if($message !== null){
            Flight::json(array("valid"=>false, "message"=>$message));
            return;
        }

        $user_id    = $User->id;
        if($User->type == 1) {
            $ProductColor   = new ProductColor;
            $ProductSize    = new ProductSize;
            $product_id     = $data->product_id;
            $color          = $data->color;
            foreach($color as $color_id => $proportion_hash) {
                // 如果为删除的款色 则不操作
                if(false === $ProductColor->is_active($product_id, $color_id)){
                    continue;
                }
                foreach($proportion_hash as $proportion_id => $num) {
                    ProductOrder::proportion_add($user_id, $product_id, $color_id, $proportion_id, $num); 
                }
            }
            $ret = ProductOrder::run();
            if($ret['error']){
                $valid      = false;
                $message    = $ret['message'];
            }else{
                $orderinfo  = $ret['orderinfo'];
                if(!$orderinfo['num']){
                    $orderinfo  = $OrderList->refresh_index_user($user_id);
                }
                $valid      = true;
                $message    = sprintf("订单保存成功<br>已订款:<strong>%d</strong><br>已订量:<strong>%d</strong>", $orderinfo['pnum'], $orderinfo['num']);
            }
        }

        $redirect_url   = $data->redirect_url;
        if($redirect_url){
            Flight::redirect($redirect_url);
        }else{
            $result     = array('valid'=>$valid, 'message'=>$message);
            Flight::json($result);
        }
    }

    public static function Action_add ($r) {
        Flight::validateUserHasLogin();
        $data       = $r->data;
        $OrderList  = new OrderList;
        $slave_user_id    = $data->user_id;
        $OrderList  = new OrderList;
        if($slave_user_id){
            $User   = new User($slave_user_id);
        }else{
            $User   = new User();
        }
        $message    = $OrderList->check_user_can_order($User);
        if($message !== null){
            Flight::json(array("valid"=>false, "message"=>$message));
            return;
        }

        $user_id    = $User->id;
        if($User->type == 1){
            $ProductColor    = new ProductColor;
            foreach($data as $key => $num){
                if(preg_match('/^order\-(\d+)\-(\d+)\-(\d+)$/', $key, $matches)){
                    $product_id     = $matches[1];
                    $color_id       = $matches[2];
                    $size_id        = $matches[3];
                    
                    // 如果为删除的款色 则不操作
                    if(false === $ProductColor->is_active($product_id, $color_id)){
                        continue;
                    }
                    ProductOrder::add($user_id, $product_id, $color_id, $size_id, $num);
                    if(($group_id=$data->group_id)&&($display_id=$data->display_id)){
                        $OrderListDetail = new OrderListDetail();
                        if($num == 0){
                            $where      = "user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id}";
                            $OrderListDetail->delete($where);
                        }else{
                            $currentNum=$OrderListDetail->find("user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id}  ",array('fields'=>'sum(num) as total'));
                            $currentdisplayNum=$OrderListDetail->find("user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id} AND display_id = {$display_id} AND group_id={$group_id} ",array('fields'=>'num'));
                            $updateNum=$currentdisplayNum[0]['num']+($num-$currentNum[0]['total']);
                            $OrderListDetail->create_order_detail($user_id, $product_id, $color_id, $size_id, $updateNum,$group_id,$display_id);
                        }
                    }
                }
            }
            $ret = ProductOrder::run();
            if($ret['error']){
                $valid      = false;
                $message    = $ret['message'];
            }else{
                $orderinfo  = $ret['orderinfo'];
                if(!$orderinfo['num']){
                    $orderinfo  = $OrderList->refresh_index_user($user_id);
                }
                $valid      = true;
                $message    = sprintf("订单保存成功<br>已订款:<strong>%d</strong><br>已订量:<strong>%d</strong>", $orderinfo['pnum'], $orderinfo['num']);

                $Company    =   new Company;
                $alert_exp  =   $Company->alert_exp;
                $alert_text =   $Company->alert_text;
                if($alert_exp){
                    $exp_list   =   explode(";",$alert_exp);
                    $text_list  =   explode(";",$alert_text);
                    $exp_num    =   $User->exp_num;
                    $exp_price  =   $User->exp_price;
                    $message_add=   '';
                    foreach($exp_list as $key=>$exp){
                        $cookie_val     =   "exp_".$exp;
                        $exp            =   floatval($exp);
                        $has            =   SESSION::get($cookie_val);
                        if($has){continue;}
                        if($exp_num > 0 && $orderinfo['num'] >= $exp * $exp_num){
                            SESSION::set($cookie_val,1);
                            $message_add = '<br><font color="red">'.$text_list[$key].'</font>';
                            $expires = 100000;
                        }elseif($exp_price > 0 && $orderinfo['discount_price'] >= $exp * $exp_price){
                            SESSION::set($cookie_val,1);
                            $message_add = '<br><font color="red">'.$text_list[$key].'</font>';
                            $expires = 100000;
                        }
                    }
                    $message .= $message_add;
                }

            }
        }

        $redirect_url   = $data->redirect_url;
        if($redirect_url){
            Flight::redirect($redirect_url);
        }else{
            $result     = array('valid'=>$valid, 'message'=>$message, 'expires'=>$expires);
            Flight::json($result);
        }
    }

    public static function Action_proportion_cancel($r){
        Flight::validateUserHasLogin();

        $OrderList  = new OrderList;
        $User       = new User;
        $message    = $OrderList->check_user_can_order($User);
        if($message !== null){
            Flight::json(array("valid"=>false, "message"=>$message));
            return;
        }

        $data       = $r->data;
        $product_id = $data->product_id;
        $user_id    = $User->id;
        if($product_id > 0){
            $options['limit']   = 1000;
            $condition[]    = "user_id={$user_id}";
            $condition[]    = "product_id={$product_id}";
            $where  = implode(" AND ", $condition);
            $olist  = $OrderList->find($where, $options);
            foreach($olist as $row){
                ProductOrder::add($user_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], 0);
            }
            ProductOrder::run();
            $OrderListProportion    = new OrderListProportion;
            $OrderListProportion->delete($where);
            $result     = array('valid' => true);
        }else{
            $result     = array('valid' => false, 'message' => '参数商品id错误');
        }

        Flight::json($result);
    }


    public static function Action_remove($r){
        Flight::validateUserHasLogin();

        $data       = $r->data;
        $slave_user_id    = $data->user_id;
        $OrderList  = new OrderList;
        if($slave_user_id) {
            $User       = new User($slave_user_id);
        }else{
            $User       = new User;
        }
        $message    = $OrderList->check_user_can_order($User);
        if($message !== null){
            Flight::json(array("valid"=>false, "message"=>$message));
            return;
        }

        $data       = $r->data;
        $product_id = $data->product_id;
        $color_id   = $data->color_id;
        $user_id    = $User->id;
        if($product_id > 0){
            $options['limit']   = 1000;
            $condition[]    = "user_id={$user_id}";
            $condition[]    = "product_id={$product_id}";
            if($color_id)   $condition[]    = "product_color_id in ({$color_id})";
			$where  = implode(" AND ", $condition);
            $olist  = $OrderList->find($where, $options);
            foreach($olist as $row){
                ProductOrder::add($user_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], 0);
            }
            ProductOrder::run();
            // $OrderList->remove_order($User->id, $product_id, $color_id);
            $result     = array('valid' => true);
        }else{
            $result     = array('valid' => false, 'message' => '参数商品id错误');
        }

        Flight::json($result);
    }

    public static function Action_add_old($r){
        Flight::validateUserHasLogin();
        $data       = $r->data;
        $OrderList  = new OrderList;
        $User       = new User;
        $message    = $OrderList->check_user_can_order($User);
        if($message !== null){
            $result['valid']    = false;
            $result['message']  = $message;
            Flight::json($result);
            return;
        }

        $user_id    = $User->id;
        if($User->type == 1){
            $ProductColor    = new ProductColor;
            $product_hash   = array();
            foreach($data as $key => $num){
                if(preg_match('/^order\-(\d+)\-(\d+)\-(\d+)$/', $key, $matches)){
                    $product_id     = $matches[1];
                    $color_id       = $matches[2];
                    $size_id        = $matches[3];
                    
                    // 如果为删除的款色 则不操作
                    if(false === $ProductColor->is_active($product_id, $color_id)){
                        continue;
                    }
                    
                    if($num == 0){
                        $where      = "user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id}";
                        $OrderList->delete($where);
                    }else{
                        $OrderList->create_order($user_id, $product_id, $color_id, $size_id, $num);
                    }
                    if(isset($data->display_id)&&$data->display_id){
                        $display_id=$data->display_id;
                        $group_id=$data->group_id;
                        $OrderListDetail = new OrderListDetail();
                        if($num == 0){
                            $where      = "user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id}";
                            $OrderListDetail->delete($where);
                        }else{
                            $currentNum=$OrderListDetail->find("user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id}  ",array('fields'=>'sum(num) as total'));
                            $currentdisplayNum=$OrderListDetail->find("user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id} AND display_id = {$display_id} AND group_id={$group_id} ",array('fields'=>'num'));
                            $updateNum=$currentdisplayNum[0]['num']+($num-$currentNum[0]['total']);
                            $OrderListDetail->create_order_detail($user_id, $product_id, $color_id, $size_id, $updateNum,$group_id,$display_id);
                        }
                    }
                    $product_hash[$product_id]++;
                }
            }
            $orderinfo  = $OrderList->refresh_index_user($user_id);
            foreach($product_hash as $product_id => $n){
                $OrderList->refresh_index_product($product_id);
            }
            $message    = sprintf("订单保存成功<br>已订款:<strong>%d</strong><br>已订量:<strong>%d</strong>", $orderinfo['pnum'], $orderinfo['num']);            
        }

        $redirect_url   = $data->redirect_url;
        if($redirect_url){
            Flight::redirect($redirect_url);
        }else{
            $result     = array('valid'=>true, 'message'=>$message);
            Flight::json($result);
        }

    }

    public static function Action_remove_old($r){
        Flight::validateUserHasLogin();

        $OrderList  = new OrderList;
        $User       = new User;
        $message    = $OrderList->check_user_can_order($User);
        if($message !== null){
            $result['valid']    = false;
            $result['message']  = $message;
            Flight::json($result);
            return;
        }

        $data       = $r->data;
        $product_id = $data->product_id;
        $color_id   = $data->color_id;
        if($product_id > 0){
            $OrderList->remove_order($User->id, $product_id, $color_id);
            $result     = array('valid' => true);
        }else{
            $result     = array('valid' => false, 'message' => '参数商品id错误');
        }

        Flight::json($result);
    }

    public static function Action_fill_display($r){
        Flight::validateUserHasLogin();
        $data           = $r->data;
        $display_id     = $data->fill_display_id;
        $add            = $data->add;
        $remove         = $data->remove;
        $User           = new User;
        $ProductSize    = new ProductSize;
        $ProductDisplayMember   = new ProductDisplayMember;
        $ProductDisplayMemberColor  = new ProductDisplayMemberColor;
        if($display_id){
            $user_id    = $User->id;
            $display_member_list    = $ProductDisplayMember->getDisplayMember($display_id, true);
            foreach($display_member_list as $dmember){
                $product    = $dmember['product'];
                $product_id = $product['id'];
                if($product['proportion_list'] && $product['status']){
                    $proportion_list = explode(';', $product['proportion_list']);
                    $proportion     = explode(':', $proportion_list[0]);
                    $color_list     = $ProductDisplayMemberColor->get_color_list($display_id, $product_id);
                    $size_list      = $ProductSize->get_size_list($product_id);
                    foreach($color_list as $color){
                        if($color['status']){
                            $color_id   = $color['color_id'];
                            for($i = 0, $len = count($size_list); $i < $len; $i++){
                                $size_id    = $size_list[$i]['size_id'];
                                $num        = $remove ? 0 : $proportion[$i];
                                $key        = "order-{$product_id}-{$color_id}-{$size_id}";
                                $r->data->$key  = $num;
                            }
                        }
                    }
                }
            }
            $data->redirect_url     = $r->referrer;
            STATIC::Action_add($r);
        }
    }

    public static function Action_list($r, $product_id=0){
        Flight::validateUserHasLogin();
        if(empty($r->query->user_id)){
            $User       = new User;
        }else{
            $User       = new User($r->query->user_id);
        }
        $OrderList  = new OrderList;
        $product_ids    = $r->query->product_ids;
        $condition[]    = "user_id={$User->id}";
        if($product_id)     $condition[]    = "product_id={$product_id}";
        if($product_ids)    $condition[]    = "product_id in ({$product_ids})";

        $where      = implode(' AND ', $condition);
        $list       = $OrderList->find($where, array("limit"=>1000));
        $result['list'] = $list;

        Flight::json($result);
    }
    public static function Action_list_by_display($r, $display_id){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;

        $list       = $OrderList->get_display_list($display_id, $User->id);

        $result['list'] = $list;

        Flight::json($result);
    }

    public static function Action_list_by_group($r, $group_id){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;

        $list       = $OrderList->get_group_list($group_id, $User->id);

        $result['list'] = $list;

        Flight::json($result);
    }

    public static function Action_list_by_show($r, $show_id){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;

        $list       = $OrderList->get_show_list($show_id, $User->id);

        $result['list'] = $list;

        Flight::json($result);
    }

    public static function Action_myorders($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;
        $Keywords   = new Keywords;
        $condition  = array();
        $options    = array();
        $data       = $r->query;
        $order      = $data->order  ? $data->order  : 'num desc';
        $p          = $data->p;
        $limit      = $data->limit ? $data->limit : 10;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $season_id      = $data->season_id;
        $price_band_id  = $data->price_band_id;
        $brand_id       = $data->brand_id;
        $wave_id        = $data->wave_id;
        if($style_id)       $condition["style_id"]  = $style_id;
        if($category_id)    $condition["category_id"]   = $category_id;
        if($classes_id)     $condition["classes_id"]    = $classes_id;
        if($wave_id)        $condition["wave_id"]   = $wave_id;
        if($series_id)      $condition["series_id"] = $series_id;
        if($season_id)      $condition["season_id"] = $season_id;
        if($brand_id)       $condition["brand_id"] = $brand_id;
        if($price_band_id)  $condition["price_band_id"] = $price_band_id;
        if($p){
            $options['page']    = $p;
            $options['limit']   = $limit;
            $result['start']    = ($p - 1) * $limit;
        }
        switch ($order) {
            case 'bianhao asc'  :
                $options['order']   = "p.bianhao asc";
                break;
            case 'bianhao desc' :
                $options['order']   = "p.bianhao desc";
                break;
            case 'num asc'      :
            case 'num desc'     :
            case 'price asc'    :
            case 'price desc'   :
                $options['order']   = $User->type == 3 ? "o.{$order}"   : $order;
                break;
            case 'all num asc'      :
                $options['order']   = $User->type == 3 ? "o.num asc"   : 'num asc';
                break;
            case 'all num desc'     :
                $options['order']   = $User->type == 3 ? "o.num desc"   : 'num desc';
                break;
            case 'all price asc'    :
                $options['order']   = $User->type == 3 ? "o.price asc"   : 'price asc';
                break;
            case 'all price desc'   :
                $options['order']   = $User->type == 3 ? "o.price desc"   : 'price desc';
                break;
            default :
                $options['order'] = "p.bianhao asc";
        }
        $result['order']    = $order;
        if($User->type == 3){
            // $condition['ad_area1']    = $User->area1;
            // $condition['ad_area2']    = $User->area2;
        }else{
            $condition['user_id']    = $User->id;
        }
        // $options['db_debug']    = true;
        $options['status_val']  = false;
        if($User->type == 3){
            $OrderListProduct       = new OrderListProduct;
            $list       = $OrderListProduct->getOrderProductList($condition, $options);
            // $countinfo  = $OrderListProduct->getOrderProductCount($condition, $options);
        }else{
            $list       = $OrderList->getOrderProductList($condition, $options);
            $list       = Flight::listFetch($list, 'moq', 'id', 'product_id', "keyword_id={$User->user_level}");
            // $countinfo  = $OrderList->getOrderProductCount($condition, $options);
        }

        foreach($list as &$row){
            $row['series']      = $Keywords->getName_File($row['series_id']);
            $row['wave']        = $Keywords->getName_File($row['wave_id']);
        }
        $len        = count($list);
        if($len > 0 && $limit > $len){
            for($i = $len; $i < $limit; $i++){
                $list[]     = array();
            }
        }
        $result['list']     = $list;
        $result['start']    = ($p - 1) * $limit;
        // $result['num']      = $countinfo['num'];
        // $result['price']    = $countinfo['price'];

        Flight::display('dealer1/myorders.list.html', $result);
    }

    public static function Action_zongdai($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;
        $Keywords   = new Keywords;
        $condition  = array();
        $options    = array();
        $data       = $r->query;
        $order      = $data->order;
        $p          = $data->p;
        $limit      = $data->limit ? $data->limit : 10;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $wave_id        = $data->wave_id;
        $price_band_id  = $data->price_band_id;
        if($style_id)       $condition["style_id"]  = $style_id;
        if($category_id)    $condition["category_id"]   = $category_id;
        if($classes_id)     $condition["classes_id"]    = $classes_id;
        if($wave_id)        $condition["wave_id"]   = $wave_id;
        if($series_id)      $condition["series_id"] = $series_id;
        if($price_band_id)  $condition["price_band_id"] = $price_band_id;
        if($p){
            $options['page']    = $p;
            $options['limit']   = $limit;
            $result['start']    = ($p - 1) * $limit;
        }
        switch ($order) {
            case 'bianhao asc'  :
                $options['order']   = "p.bianhao asc";
                break;
            case 'bianhao desc' :
                $options['order']   = "p.bianhao desc";
                break;
            case 'num asc'      :
                $options['order']   = "num asc";
                break;
            case 'num desc'     :
                $options['order']   = "num desc";
                break;
            case 'price asc'    :
                $options['order']   = "price asc";
                break;
            case 'price desc'   :
                $options['order']   = "price desc";
                break;
            default :
                $options['order'] = "num desc";
        }
        $result['order']    = $order;
        $options['fields_more'] = "p.id,p.bianhao,p.kuanhao,p.name,p.series_id,p.wave_id,p.price as p_price,COUNT(DISTINCT o.product_color_id) as color_num";
        $condition['user_id']    = $User->id;
        $options['status_val']  = false;
        $list       = $OrderList->getDealer2OrderList($condition, $options);
        $info       = $OrderList->getDealer2OrderCount($condition, $options);
        $result['num']      = $info['num'];
        $result['price']    = $info['price'];
        $result['discount_price']    = $info['discount_price'];
        $result['pnum']     = $info['pnum'];
        $result['skc']      = $info['skc'];
        foreach($list as &$row){
            $row['series']      = $Keywords->getName_File($row['series_id']);
            $row['wave']        = $Keywords->getName_File($row['wave_id']);
        }
        $len        = count($list);
        if($len > 0 && $limit > $len){
            for($i = $len; $i < $limit; $i++){
                $list[]     = array();
            }
        }
        $result['list']     = $list;

        Flight::display('dealer2/orders.list.html', $result);
    }

    public static function Action_ad($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;
        $Keywords   = new Keywords;
        $condition  = array();
        $options    = array();
        $data       = $r->query;
        $order      = $data->order;
        $p          = $data->p;
        $limit      = $data->limit ? $data->limit : 10;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $series_id      = $data->series_id;
        $wave_id        = $data->wave_id;
        if($style_id)       $condition["style_id"]  = $style_id;
        if($category_id)    $condition["category_id"]   = $category_id;
        if($wave_id)        $condition["wave_id"]   = $wave_id;
        if($series_id)      $condition["series_id"] = $series_id;
        if($p){
            $options['page']    = $p;
            $options['limit']   = $limit;
            $result['start']    = ($p - 1) * $limit;
        }
        if($order == "num"){
            $options['order']   = "num desc";
        }else{
            $options['order']   = "price desc";
        }
        $result['order']    = $order;
        $options['fields_more'] = "p.bianhao,p.kuanhao,p.name,p.series_id,p.wave_id,p.price as p_price,COUNT(DISTINCT o.product_color_id) as color_num";
        $condition['user_id']    = $User->id;
        $list       = $OrderList->getDealer2OrderList($condition, $options);
        foreach($list as &$row){
            $row['series']      = $Keywords->getName_File($row['series_id']);
            $row['wave']        = $Keywords->getName_File($row['wave_id']);
        }
        $len        = count($list);
        if($len > 0 && $limit > $len){
            for($i = $len; $i < $limit; $i++){
                $list[]     = array();
            }
        }
        $result['list'] = $list;

        Flight::display('dealer2/orders.list.html', $result);
    }


    public static function Action_zongdaiedit($r){
        Flight::validateUserHasLogin();

        $data       = $r->data;
        $user_id    = $data->user_id;
        $product_id = $data->product_id;
        $color_id   = $data->color_id;
        $size_id    = $data->size_id;
        $num        = $data->num;
        $OrderList  = new OrderList;
        $where      = "user_id={$user_id} AND product_id={$product_id} AND product_color_id={$color_id} AND product_size_id={$size_id}";
        if($num == 0){
            $OrderList->delete($where);
        }else{
            $OrderList->update(array("num"=>$num), $where);
        }

        $result     = array('valid'=>true);
        Flight::json($result);
    }


    public static function Action_iphonelist($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $limit      = $data->limit  ? $data->limit  : 10;
        $p          = $data->p      ? $data->p      : 1;
        $User       = new User;
        $OrderList  = new OrderList;
        $condition  = array();
        $options    = array();
        $condition['product_id']    = $data->product_id;
        $condition['user_id']       = $User->id;
        $condition['style_id']      = $data->style_id;
        $condition['category_id']   = $data->category_id;
        $condition['wave_id']       = $data->wave_id;
        $condition['series_id']     = $data->series_id;
        $condition['search']        = $data->search;

        $options['limit']           = $limit;
        $options['page']            = $p;

        $list       = $OrderList->getOrderProductList($condition, $options);
        foreach($list as &$row){
            $row['rank']    = $OrderList->getOrderProductRank($row['product_id'], $row['num']);
        }
        $result['list']     = $list;
        $result['start']    = ($p - 1) * $limit;

        Flight::display("order/list.html", $result);
    }

    public static function Action_mywrongorders($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $limit      = $data->limit  ? $data->limit  : 10;
        $p          = $data->p      ? $data->p      : 1;
        $wrong      = $data->wrong;
        $Company    = new Company();
        if(!$wrong){
            $wrong  = $Company->wrong_order ? $Company->wrong_order     : 100;
        }
        $User       = new User;
        $OrderList  = new OrderList;
        $UserSlave  = new UserSlave();
        
        $options    = array();
        $condition   = array();
        $options['limit']   = $limit;
        $options['page']    = $p;
        if($User->type == 1){
            $condition['uid'] = $User->id;
        }elseif($User->type == 2){
            $condition['user_id_str'] = $UserSlave->get_slave_user_id($User->id);
        }elseif($User->type == 3 && $User->username!='0'){
            $condition['ad_id'] = $User->id;
        }
        $list       = $OrderList->get_wrong_orderlist($condition, $wrong , $options);

        $result['list']     = $list;
        $result['start']    = ($p - 1) * $limit;
        $result['user']     = $User->getAttribute();;
        $result['company']     = $Company->getData();
        Flight::display("orderlist/wrongorders.html", $result);
    }

    public static function Action_masterlist($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $product_id = $data->product_id;

        $User       = new User;
        $OrderList  = new OrderList;
        if($User->mid && $product_id){
            $cslist     = $OrderList->getSlaveOrderCSList($User->mid, $product_id);
            if(count($cslist)){
                $OrderTable     = new OrderTable($product_id, $cslist);
                $result         = $OrderTable->byAll();
                $result['viewall_txt']     = '查看下线明细';
                Flight::display("orderlist/masterlistall.table.html", $result);
            }
        }
    }

    public static function Action_masterlistuser($r){
        Flight::validateUserHasLogin();
        $data       = $r->query;
        $product_id = $data->product_id;

        $User       = new User;
        $OrderList  = new OrderList;
        if($User->mid && $product_id){
            $ulist  = $User->find("type=1 AND id not in (select DISTINCT user_id from orderlist where product_id={$product_id}) and id in (select user_slave_id from user_slave where user_id={$User->mid})", array("limit"=>10000, "fields"=>"name"));
            $ucslist    = $OrderList->getSlaveOrderUCSList($User->mid, $product_id);
            if(count($ucslist)){
                $OrderTable     = new OrderTable($product_id, $ucslist);
                $result         = $OrderTable->byUser();
            }
            $result['unorderulist'] = $ulist;
            Flight::display("orderlist/masterlistuser.table.html", $result);
        }
    }

    public static function Action_print_now($r, $uname){
        $username       = addslashes($uname);
        $User           = new User;
        $OrderListProportion    = new OrderListProportion;
        $OrderList      = new OrderList;
        $Keywords       = new Keywords;
        $Company        = new Company;
        $result['company']  = $Company->getData();
        $order_proportion_status    = $Company->order_proportion_status;
        $u              = $User->findone("username='$username'");
        $data           = $r->query;
        $key            = $data->key;
        $shendan        = $data->shendan;
        $filter_id      = $data->filter_id;
        switch($key){
            case 'wave' :
                $keyword1   = 'wave';
                $keyword2   = 'wave_id';
                break;
            case 'kuanhao'  :
                $keyword1   = 'category';
                $keyword2   = 'category_id';
                break;
            default :
                $keyword1   = $key;
                $keyword2   = "{$key}_id";
        }
        $template = "order_manage/print_now.html";
        $result['key']  = $key;
        if($u['id']){
            if($u['type'] == 2){
                $UserSlave  = new UserSlave;
                $master_uid     = $u['id'];
                $slave_id   = $UserSlave->get_slave_user_id($u['id']);
                $master     = $User->findone("mid={$u['id']}");
                $discount   = $master['discount'];
            }else{
                $slave_id   = $u['id'];
            }
            if($filter_id){
                $filter_value = "p.{$keyword2}=$filter_id";
            }
            $list       = $OrderList->get_user_orderlist_info($slave_id, false, false, false, $discount, $filter_value);
            $category   = array();

            $skc_size_option    = array();
            switch ($u['type']) {
                case 2:
                    $skc_size_option['mid'] = $u['id'];
                    break;
                case 3:
                    $skc_size_option['ad_id']   = $u['id'];
                    break;
                default : 
                    $skc_size_option['user_id'] = $u['id'];
            }
            foreach($list as &$row){
                $category_id    = $row[$keyword2];
                if(!$category[$category_id]){
                    $myrow  = array('category_id'=>$category_id);
                    $category[$category_id]     = $myrow;
                }
                $size_group_id      = $row['size_group_id'];
                $SizeGroup      = SizeGroup::getInstance($size_group_id);
                $size_list      = $SizeGroup->get_size_list();
                if(!$category[$category_id]['size_group'][$size_group_id]) {
                    $size_length    = count($size_list);
                    $size_number    = count($category[$category_id]['size_group']);
                    $category[$category_id]['size_group'][$size_group_id] = $size_list;
                    $size_index     = 0;
                    foreach($size_list as $size) {
                        $currentSizeNumber  = count($category[$category_id]['size_list'][$size_index]);
                        while($size_number > $currentSizeNumber) {
                            $category[$category_id]['size_list'][$size_index][] = "";
                            $currentSizeNumber  = count($category[$category_id]['size_list'][$size_index]);
                        }
                        $category[$category_id]['size_list'][$size_index++][] = $size;
                    }
                    if($size_length > $category[$category_id]['size_length']) {
                        $category[$category_id]['size_length']  = $size_length;
                    }
                    $currentSizeLength = $category[$category_id]['size_length'];
                    while($size_index < $currentSizeLength) {
                        $category[$category_id]['size_list'][$size_index++][] = "";
                    }
                }

                $mysize_list    = array();
                $skc_size_list  = $OrderList->get_skc_size_list($row['product_id'], $row['product_color_id'], $skc_size_option);
                $skc_size_hash  = array();
                foreach($skc_size_list as $skc_size){
                    $size_id    = $skc_size['product_size_id'];
                    $size_num   = $skc_size['num'];
                    $skc_size_hash[$size_id] = $size_num;
                }
                foreach($size_list as $key => $size) {
                    $size_num       = $skc_size_hash[$size['size_id']];
                    $mysize_list[]  = $size_num;
                    $category[$category_id]['sizeinfo'][$key] += $size_num;
                }

                if($order_proportion_status) {
                    $xinfo          = $OrderListProportion->get_product_color_xnum($row['product_id'], $row['product_color_id'], $skc_size_option);
                    $row['xnum']    = $xinfo['xnum'];
                }

                $row['size_list']   = $mysize_list;
                $category[$category_id]['listing'][]    = $row;
                $category[$category_id]['price']        += $row['price'];
                $category[$category_id]['discount_price']        += $row['discount_price'];
                $category[$category_id]['num']          += $row['num'];
                $category[$category_id]['xnum']         += $row['xnum'];
                $category[$category_id]['SKC']++;
                $category[$category_id]['HASH'][$row['product_id']]++;
            }
            if($key != "kuanhao"){
                $category   = ProductsAttributeFactory::fetch($category, $keyword1, 'category_id', 'attr');
                usort($category, function($a, $b){
                    return $a['attr']['rank'] > $b['attr']['rank'];
                });
            }

            $all_num    = 0;
            $all_price  = 0;
            $discount_price     = 0;
            //print_r($category);
            foreach($category as $key => $val){
                $val['pnum']        = count($val['HASH']);
                $result['list'][]   = $val;
                $all_num            += $val['num'];
                $all_price          += $val['price'];
                $discount_price     += $val['discount_price'];
            }

            $result['all_num']      = $all_num;
            $result['all_price']    = $all_price;
            if($master_uid){
                $discount_price_info    = $OrderList->getSlaveOrderinfo($master_uid);
                $result['discount_price']    = sprintf("%.2f", $discount_price_info['discount_price']);
            }else{
                $result['discount_price']    = sprintf("%.2f", $discount_price);
            }

            $result['size_num']     = $newSizeLength;

            $Product        = new Product;
            $result['messagelist']  = $Product->getMessageList();
        }
        $result['u']    = $u;
        $result['shendan']  = $shendan;
        if($shendan){
            $rlog = new ReviewCancelLog();
            $reviewLog = $rlog->findone(' user_id="'.$u['id'].'" ');
            if($reviewLog['id']){
                $result['hasCancelLog'] = 1;
                $result['reviewLog'] = $reviewLog;
            }
        }

        $download       = $r->query->download;
        if($download){
            $filename       = quotemeta(str_replace(" ", '', "{$u['username']}-{$u['name']}-{$Company->fairname}订单"));
            $Response       = Flight::response();
            $Response->header("Content-type","text/html");
            $Response->header("Content-Disposition", "attachment; filename={$filename}.html");
        }

        $result['current_url']=$r->url;
        //print_r($r);
        if($data->show_price){
            $show_price = $data->show_price;
        }else{
            $show_price = $Company->show_price ? $Company->show_price : 2;
        }
        $result['show_price'] = $show_price;
        $result['query_data']=$data;
        //print_r($result['query_data']);
        $result['category_array']=array('wave'=>'波段','category'=>'大类','theme'=>'主题','style'=>'款别','brand'=>'品牌','season'=>'季节','series'=>'系列');
        if($result['u']['exp_num']){
            $result['u']['num_percent']=number_format($result['all_num']/$result['u']['exp_num'],4)*100;
        }
        if($result['u']['exp_price']){
            $result['u']['price_percent']=number_format($result['discount_price']/$result['u']['exp_price'],4)*100;
        }
        // $TemplateConfig = new TemplateConfig;
        // $result['template_list'] = $TemplateConfig->get_template_config();
        Flight::display($template, $result);
    }

    public static function Action_unorderproduct($r, $username=false){
        if($username){
            $User   = new User;
            $u      = $User->findone("username='{$username}'");
            if($u['id']){
                if($u['type'] == 2){
                    $UserSlave  = new UserSlave;
                    $slave_id   = $UserSlave->get_slave_user_id($u['id']);
                }else{
                    $slave_id   = $u['id'];
                }
            }
        }
        $Product            = new Product;
        $OrderListProduct   = new OrderListProduct;
        $options['limit']   = 1000;
        $condition[]        = "status=1";
        if($slave_id){
            $condition[]    = "id not in (select DISTINCT product_id from orderlist where user_id in ($slave_id))";
        }
        $where  = implode(" AND ", $condition);
        // $options['db_debug'] = true;
        $list   = $Product->find($where, $options);
        foreach($list as &$row){
            $row['rank']    = $OrderListProduct->get_rank($row['id']);
        }
        usort($list, function($a, $b){
            $rank_a = $a['rank'];
            $rank_b = $b['rank'];
            if($rank_a < 1) return true;
            if($rank_b < 1) return false;
            return $rank_a > $rank_b;
        });
        $result['list'] = $list;
        $result['u']    = $u;
        Flight::display('orderlist/unorderproduct.html', $result);
    }


    public static function Action_order_filter ($r) {
        $User           = new User;
        $OrderList      = new OrderList;
        $Keywords       = new Keywords;
        $data           = $r->query;
        $key            = $data->key;
        $username				= $r->query->username;
        $bianhao				= $r->query->bianhao;
        switch($key){
            case 'kuanhao'  :
                $keyword1   = 'kuanhao';
                $keyword2   = '';
                break;
            default :
                $keyword1   = "{$key}";
                $keyword2   = "{$key}_id";
        }
        $template = "order_manage/order_filter.html";
        $result['key']  = $key;
        if(empty($key)){
        	$OrderList  = new OrderList;
        	$result['key'] = "defsummary";
        	$result['orderinfo']    = $OrderList->getAdOrderinfo($User->area1, $User->area2);
        	Flight::display($template, $result);
        	exit;
        }
        $condition  = array();
        $options['order']   = $data->orderby . " " . $data->asc;
        foreach($data as $k => $v) {
            $condition[$k]  = $v;
        }
            $list       = $OrderList->get_orderlist_info_filter($condition, $options);
            $category   = array();
            $Factory        = new ProductsAttributeFactory('size');
            $size_group_list    = $Factory->get_group_list();
            foreach($size_group_list as $s){
                $tmpl_array[$s['group_id']][]   = $s['keyword_id'];
            }

            $newSizeList    = array();
            $newSizeHash    = array();
            $newSizeLength  = 0;
            foreach($tmpl_array as $group_id => $ary){
                $len    = count($ary);
                if($len > $newSizeLength){
                    $newSizeLength = $len;
                }
            }
            foreach($tmpl_array as $group_id => $ary){
                for($i = 0; $i < $newSizeLength; $i++){
                    $size_id    = $ary[$i];
                    $newSizeList[$i][]  = $size_id;
                    $newSizeHash[$size_id]  = $i;
                }
            }
            $result['newSizeList']  = $newSizeList;

            foreach($list as &$row){
                $category_id    = $row[$keyword2];
                $kuanhao        = $row['kuanhao'] . '-' . $row['product_color_id'];
                if(!$category[$category_id]){
                    $category[$category_id]     = array('category_id'=>$category_id, 'listing'=>array());
                }
                if(!$category[$category_id]['listing'][$kuanhao]) {
                    $category[$category_id]['listing'][$kuanhao] = $row;
                }else{
                    $category[$category_id]['listing'][$kuanhao]['num'] += $row['num'];
                    $category[$category_id]['listing'][$kuanhao]['price'] += $row['price'];
                }

                if(!$category[$category_id]['listing'][$kuanhao]['size_list']) {
                    $category[$category_id]['listing'][$kuanhao]['size_list'] = array_pad(array(), $newSizeLength, '');
                }
                $size_hash_num  = $newSizeHash[$row['product_size_id']];
                $category[$category_id]['listing'][$kuanhao]['size_list'][$size_hash_num]  += $row['num'];
                // $myrow['size_list']   = $mysize_list;

                //$category[$category_id]['listing'][]    = $row;
                $category[$category_id]['price']        += $row['price'];
                $category[$category_id]['discount_price']        += $row['discount_price'];
                $category[$category_id]['num']          += $row['num'];
                $category[$category_id]['SKC']++;
                $category[$category_id]['HASH'][$row['product_id']]++;
            }

            if($key != "kuanhao"){
                $category   = ProductsAttributeFactory::fetch($category, $keyword1, 'category_id', 'attr');
                usort($category, function($a, $b){
                    return $a['attr']['rank'] > $b['attr']['rank'];
                });
            }

            $all_num    = 0;
            $all_price  = 0;
            $discount_price     = 0;
            foreach($category as $key => $val){
                $val['pnum']        = count($val['HASH']);
                $result['list'][]   = $val;
                $all_num            += $val['num'];
                $all_price          += $val['price'];
                $discount_price     += $val['discount_price'];
            }

            $result['all_num']      = $all_num;
            $result['all_price']    = $all_price;
            $result['discount_price']    = sprintf("%.2f", $discount_price);

            $result['size_num']     = $newSizeLength;

            $Company        = new Company;
            $result['company']  = $Company->getData();
            $Product        = new Product;
            $result['messagelist']  = $Product->getMessageList();

        $download       = $r->query->download;
        if($download){
            $filename       = quotemeta(str_replace(" ", '', "{$Company->name}{$Company->fairname}"));
            $Response       = Flight::response();
            $Response->header("Content-type","text/html");
            $Response->header("Content-Disposition", "attachment; filename={$filename}.html");
        }

        Flight::display($template, $result);
    }


    public static function Action_product_color_orderlist ($r) {
        Flight::validateUserHasLogin();

        $data   = $r->query;
        $p      = $data->p              ? $data->p          : 1;
        $limit  = $data->limit          ? $data->limit      : 30;
        $order_key  = $data->order_key  ? $data->order_key  : "skc_id";
        $order_ud   = $data->order_ud   ? $data->order_ud   : 0;
        $status     = is_numeric($data->status)     ? $data->status     : "";
        $keys   = array('category_id','medium_id','classes_id','edition_id','contour_id', 'wave_id','style_id','price_band_id','brand_id','series_id','theme_id','nannvzhuan_id','sxz_id','season_id');
        
        //$keys   = array("category_id", "classes_id", "style_id", "wave_id", "nannvzhuan_id", "brand_id", "price_band_id", "series_id");
        $condition  = array();
        $options    = array();
        foreach($keys as $key){
            if($data->$key) {
                $condition[$key]    = $data->$key;
            }
        }
        $condition['status']    = $status;
        // $OrderList  = new OrderList;
        // $count      = $OrderList->getOrderAnalysisCount($condition, $options);
        // $result['count']    = $count;

        $OrderListProductColor  = new OrderListProductColor;
        $list   = $OrderListProductColor->get_product_color_list($condition, array());
        usort($list, function($a, $b) use ($order_key, $order_ud){
            if($order_ud){
                return $a[$order_key] < $b[$order_key];
            }else{
                return $a[$order_key] > $b[$order_key];
            }
        });
        $info   = array("status"=>array());
        foreach($list as $row){
            $pc_status = $row['pc_status'];
            $info['status'][$pc_status]++;
            $info['num'][$pc_status] += $row['num'];
            $info['price'][$pc_status] += $row['count_price'];
        }

        $result['list']     = $list;
        $result['info']     = $info;

        Flight::display("ad/product_color_orderlist.html", $result);
    }

    public static function Action_orders_zd($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;
        $Keywords   = new Keywords;
        $condition  = array();
        $options    = array();
        $data       = $r->query;
        $order      = $data->order;
        $p          = $data->p;
        $limit      = $data->limit ? $data->limit : 10;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $wave_id        = $data->wave_id;
        $price_band_id  = $data->price_band_id;
        $brand_id       = $data->brand_id;
        $area2          = $data->area2;
        $user_id          = $data->fliter_uid;
        if($style_id)       $condition["style_id"]  = $style_id;
        if($category_id)    $condition["category_id"]   = $category_id;
        if($classes_id)     $condition["classes_id"]    = $classes_id;
        if($wave_id)        $condition["wave_id"]   = $wave_id;
        if($series_id)      $condition["series_id"] = $series_id;
        if($price_band_id)  $condition["price_band_id"] = $price_band_id;
        if($brand_id)       $condition["brand_id"] = $brand_id;
        if($area2)          $condition["area2"] = $area2;
        if($user_id){
            $condition["user_id"] = $user_id;
        }else{
            $userlist = $User->find('area2="'.$area2.'"',array('fields'=>'  GROUP_CONCAT(id) as  userlist'));
            $condition["user_id_str"] = $userlist[0]['userlist'];
        }
        if($p){
            $options['page']    = $p;
            $options['limit']   = $limit;
            $result['start']    = ($p - 1) * $limit;
        }
        switch ($order) {
            case 'bianhao asc'  :
                $options['order']   = "p.bianhao asc";
                break;
            case 'bianhao desc' :
                $options['order']   = "p.bianhao desc";
                break;
            case 'num asc'      :
                $options['order']   = "num asc";
                break;
            case 'num desc'     :
                $options['order']   = "num desc";
                break;
            case 'price asc'    :
                $options['order']   = "price asc";
                break;
            case 'price desc'   :
                $options['order']   = "price desc";
                break;
            default :
                $options['order'] = "num desc";
        }
        $result['order']    = $order;
        $options['fields_more'] = "p.id,p.bianhao,p.kuanhao,p.name,p.series_id,p.wave_id,p.price as p_price,COUNT(DISTINCT o.product_color_id) as color_num";
        $options['status_val']  = false;
        $list       = $OrderList->getAdOrderListInfo($condition, $options);
        $info       = $OrderList->getAdOrderCount($condition, $options);
        $result['num']      = $info['num'];
        $result['price']    = $info['price'];
        $result['discount_price']    = $info['discount_price'];
        $result['pnum']     = $info['pnum'];
        $result['skc']      = $info['skc'];
        foreach($list as &$row){
            $row['series']      = $Keywords->getName_File($row['series_id']);
            $row['wave']        = $Keywords->getName_File($row['wave_id']);
        }
        $len        = count($list);
        if($len > 0 && $limit > $len){
            for($i = $len; $i < $limit; $i++){
                $list[]     = array();
            }
        }
        $result['list']     = $list;

        Flight::display('ad/orders.list.html', $result);
    }
    
    
    public static function Action_myordersview($r){
        Flight::validateUserHasLogin();
    
        $User           = new User;
        $OrderList      = new OrderList;
        $OrderListProportion      = new OrderListProportion;
        $Keywords       = new Keywords;
        $Company        = new Company;
        $UserGuide      = new UserGuide;
        $order_proportion_status    = $Company->order_proportion_status;
        $user_guide     = $Company->user_guide;
        $u              = $User->getAttribute();
        $key            = $r->query->key;
        $cond           = array();
        if($r->query->category_id) $cond['category_id'] = $r->query->category_id;
        if($r->query->medium_id) $cond['medium_id'] = $r->query->medium_id;
        if($r->query->classes_id) $cond['classes_id'] = $r->query->classes_id;
        if($r->query->wave_id) $cond['wave_id'] = $r->query->wave_id;
        if($r->query->style_id) $cond['style_id'] = $r->query->style_id;
        if($r->query->price_band_id) $cond['price_band_id'] = $r->query->price_band_id;
        if($r->query->brand_id) $cond['brand_id'] = $r->query->brand_id;
        if($r->query->series_id) $cond['series_id'] = $r->query->series_id;
        if($r->query->theme_id) $cond['theme_id'] = $r->query->theme_id;
        if($r->query->nannvzhuan_id) $cond['nannvzhuan_id'] = $r->query->nannvzhuan_id;
        if($r->query->sxz_id) $cond['sxz_id'] = $r->query->sxz_id;
        if($r->query->season_id) $cond['season_id'] = $r->query->season_id;
        if($r->query->area1) $cond['area1'] = $r->query->area1;
        if($r->query->area2) $cond['area1'] = $r->query->area2;
        if($r->query->fliter_uid) $cond['fliter_uid'] = $r->query->fliter_uid;

        $isspot     = $User->current_isspot;
        if($isspot){
            $cond['isspot'] = $isspot;
        }

        if(isset($r->query->color_status)) {
            $cond['color_status'] = $r->query->color_status;
            if($r->query->color_status=='0'){
                $result['dontShowLink'] = 1;
            }           
        }
        //$result['dontShowLink'] =1;
        $orderTyle = $r->query->order;
        $result['key']  = $key;
        switch($key){
            case 'wave' :
                $keyword1   = 'wave';
                $keyword2   = "wave_id";
                break;
            default:
                $keyword1   = $key;
                $keyword2   = "{$key}_id";
        }
        if($u['id']){
            //$list       = $OrderList->get_user_orderlist_info($u['id']);
            if($u['type']==2){
                $UserSlave = new UserSlave();
                $user_slave = $UserSlave->get_slave_user_id($u['id']);
                $user_where = $user_slave?$user_slave:0;
            }elseif($u['type']==3&&$u['username']!='0'){
                $user_where = $User->get_ad_user_id($u['id']);
            }elseif($u['type']==3) {
                $user_where = '';
            }else{
                $user_where = $u['id'];
            }
            $list       = $OrderList->get_user_condition_orderlist($user_where,$cond,$orderTyle);
            $category   = array();
            
            $skc_size_option    = array();
            switch ($u['type']) {
                case 2:
                    $skc_size_option['mid'] = $u['id'];
                    break;
                case 3:
                    if($u['username']!='0'){
                        $skc_size_option['ad_id']   = $u['id'];
                        $skc_size_option['user_id'] = $cond['fliter_uid'];
                    }else{
                        $skc_size_option['area1']   = $cond['area1'];
                        $skc_size_option['area2']   = $cond['area2'];
                        $skc_size_option['user_id'] = $cond['fliter_uid'];
                    }
                    break;
                default : 
                    $skc_size_option['user_id'] = $u['id'];
            }
            foreach($list as &$row){
                $category_id    = $row[$keyword2];
                if(!$category[$category_id]){
                    $myrow  = array('category_id'=>$category_id);
                    $category[$category_id]     = $myrow;
                }

                $size_group_id      = $row['size_group_id'];
                $SizeGroup      = SizeGroup::getInstance($size_group_id);
                $size_list      = $SizeGroup->get_size_list();
                if(!$category[$category_id]['size_group'][$size_group_id]) {
                    $size_length    = count($size_list);
                    $size_number    = count($category[$category_id]['size_group']);
                    $category[$category_id]['size_group'][$size_group_id] = $size_list;
                    $size_index     = 0;
                    foreach($size_list as $size) {
                        $currentSizeNumber  = count($category[$category_id]['size_list'][$size_index]);
                        while($size_number > $currentSizeNumber) {
                            $category[$category_id]['size_list'][$size_index][] = "";
                            $currentSizeNumber  = count($category[$category_id]['size_list'][$size_index]);
                        }
                        $category[$category_id]['size_list'][$size_index++][] = $size;
                    }
                    if($size_length > $category[$category_id]['size_length']) {
                        $category[$category_id]['size_length']  = $size_length;
                    }
                    $currentSizeLength = $category[$category_id]['size_length'];
                    while($size_index < $currentSizeLength) {
                        $category[$category_id]['size_list'][$size_index++][] = "";
                    }
                }

    
                $mysize_list    = array();
                $skc_size_list  = $OrderList->get_skc_size_list($row['product_id'], $row['product_color_id'], $skc_size_option);
                $skc_size_hash  = array();
                foreach($skc_size_list as $skc_size){
                    $size_id    = $skc_size['product_size_id'];
                    $size_num   = $skc_size['num'];
                    $skc_size_hash[$size_id] = $size_num;
                }
                foreach($size_list as $key => $size) {
                    $size_num       = $skc_size_hash[$size['size_id']];
                    $mysize_list[]  = $size_num;
                    $category[$category_id]['sizeinfo'][$key] += $size_num;
                }

                if($order_proportion_status) {
                    $xinfo          = $OrderListProportion->get_product_color_xnum($row['product_id'], $row['product_color_id'], $skc_size_option);
                    $row['xnum']    = $xinfo['xnum'];
                }

                if($user_guide) {
                    $row['user_guide']     = $UserGuide->get_guide_num($u['id'],$row['product_id'],$row['product_color_id']);
                }

                $row['size_list']   = $mysize_list;
                $category[$category_id]['listing'][]    = $row;
                $category[$category_id]['price']        += $row['price'];
                $category[$category_id]['discount_price']   += $row['discount_price'];
                $category[$category_id]['num']          += $row['num'];
                $category[$category_id]['xnum']         += $row['xnum'];
                $category[$category_id]['SKC']++;
                $category[$category_id]['HASH'][$row['product_id']]++;
            }
    
            $category   = ProductsAttributeFactory::fetch($category, $keyword1, 'category_id', 'attr');
            usort($category, function($a, $b){
                return $a['attr']['rank'] > $b['attr']['rank'];
            });
    
                $all_num    = 0;
                $all_price  = 0;
                $discount_price     = 0;
                foreach($category as $key => $val){
                    $val['pnum']        = count($val['HASH']);
                    $result['list'][]   = $val;
                    $all_num            += $val['num'];
                    $all_price          += $val['price'];
                    $discount_price     += $val['discount_price'];
                }
    
                $result['all_num']      = $all_num;
                $result['all_price']    = $all_price;
                $result['discount_price']    = sprintf("%.2f", $discount_price);
    
                $result['size_num']     = $newSizeLength;
                $result['company']  = $Company->getData();
        }
        $result['u']    = $u;     
        Flight::display("orderlist/myordersview.html", $result);
    }

    public static function Action_inspection($r,$username){
        Flight::validateUserHasLogin();
        $User   = new User;
        $u      = $User->findone("username='{$username}'");
        if($u['id']){            
            $Location       = new Location;
            $callback       = function($id) use ($Location){
                return $Location->getCurrent($id);
            };
            $Cache          = new Cache($callback);
            $result['area1']= $Cache->get($u['area1'], array($u['area1']));
            $result['u']    = $u;
            $permission_brand = $u['permission_brand'];   //过滤品牌

            if($permission_brand)   $where_brand = "p.brand_id not in ({$permission_brand})";

            if($u['type']==2){
                $user_num   = UserSlave::get_user_slave_num($u['id']);
                $where_user = "zd_user_id={$u['id']}";
            }else{
                $user_num   = 1;
                $where_user = "user_id={$u['id']}";
                $agent      = User::find_agent($u['id']);
            }
            $result['agentname']    = $agent ? $agent->name : "公司";
            $result['user_num']     = $user_num;
            //用户指标
            $OrderList              = new OrderList;
            $options                = array();
            $options['tablename']   = " product as p left join orderlist as o on p.id=o.product_id and ".$where_user;
            $options['fields']      = " p.category_id,p.classes_id,sum(o.num) as num,sum(o.amount) as price,count(DISTINCT product_id,product_color_id) as skc";
            $options['group']       = " p.category_id";
            $options['key']         = "category_id";
            $options['limit']       = 1000;
            // $options['db_debug']=true;
            $where                  = $where_brand;
            $user_exp_list          = $OrderList->find($where,$options);
            $user_exp_total         = array();
            $UserIndicator          = new UserIndicator;
            $user_indicator_list    = $UserIndicator->get_indicator($u['id']);
            foreach ($user_exp_list as &$value) {
                $value['average_price'] = sprintf("%.2f",($value['price']/$value['num']));
                $value['skc_depth']     = sprintf("%.1f",($value['num']/$value['skc']));
                $value['indicator']     = $UserIndicator->get_indicator($u['id'],"category_id",$value['category_id']);
                $user_exp_total['num']  += $value['num'];
                $user_exp_total['price']+= $value['price'];
                $user_exp_total['skc']  += $value['skc'];
                $user_exp_total['indicator']['num']     +=  $value['indicator']['exp_num'];
                $user_exp_total['indicator']['price']   +=  $value['indicator']['exp_amount'];
                $user_exp_total['indicator']['skc']     +=  $value['indicator']['exp_skc'];
            }
            unset($value);
            foreach ($user_exp_list as &$value) {
                $value['skc_percent']       = sprintf("%.2f",$value['skc'] / $user_exp_total['skc'] * 100);
                $value['num_percent']       = sprintf("%.2f",$value['num'] / $user_exp_total['num'] * 100);
                $value['price_percent']     = sprintf("%.2f",$value['price'] / $user_exp_total['price'] * 100);
            }
            $result['user_indicator_list']  = $user_indicator_list;
            $result['user_exp_list']        = $user_exp_list;
            $result['user_exp_total']       = $user_exp_total;
            //订货结构
            $options['group'] = " p.category_id,p.classes_id";
            $options['key'] = "";
            $user_content = $OrderList->find($where,$options);
            foreach ($user_content as $row) {
                $row['average_price'] = sprintf("%.2f",($row['price']/$row['num']));
                $row['skc_depth'] = sprintf("%.1f",($row['num']/$row['skc']));
                $row['indicator'] = $UserIndicator->get_indicator($u['id'],"classes_id",$row['classes_id']);
                $user_content_list[$row['category_id']][] = $row;
            }
            $result['user_content_list'] = $user_content_list;

            //必定款
            $ProductColor = new ProductColor;
            $pc_need_list = $ProductColor->get_need_list($permission_brand);
            $need_list_total = array();  
            foreach ($pc_need_list as $val) {
                $info = $OrderList->findone("product_id={$val['product_id']} and product_color_id={$val['color_id']} and ".$where_user,
                                        array("fields"=>"sum(num) as num,sum(amount) as price"));
                $user_need_list[$val['category_id']]['design_skc'] ++;
                if($info['num']){
                    $user_need_list[$val['category_id']]['skc']++;
                    $user_need_list[$val['category_id']]['num'] += $info['num'];
                    $user_need_list[$val['category_id']]['price'] += $info['price'];
                }
            }
            foreach ($user_need_list as $key=>&$val) {
                $val['num_percent']     = sprintf("%.1f%%",($val['num']/$user_exp_list[$key]['num'])*100);
                $val['price_percent']   = sprintf("%.1f%%",($val['price']/$user_exp_list[$key]['price'])*100);

                $need_list_total['design_skc']  += $val['design_skc'];
                $need_list_total['skc']  += $val['skc'];
                $need_list_total['num']  += $val['num'];
                $need_list_total['price']+= $val['price'];
                $need_list_total['price_total']+= $user_exp_list[$key]['price'];
                $need_list_total['num_total']+= $user_exp_list[$key]['num'];
            }
            $result['user_need_list'] = $user_need_list;
            $result['need_list_total']= $need_list_total;

            //审核项：款别
            $options   = array();
            $options['tablename'] = " product as p left join products_attr as pa on p.style_id=pa.keyword_id 
                                    left join orderlist as o on p.id=o.product_id and ".$where_user;
            $options['fields']= " p.style_id,sum(o.num) as num,sum(o.amount) as price,count(DISTINCT product_id,product_color_id) as skc";
            $options['group'] = " p.style_id";
            $options['key']   = "style_id";
            $options['order'] = "pa.rank";
            $options['limit'] = 1000;
            //$options['db_debug']=true;
            $where = $where_brand;
            $user_style_list = $OrderList->find($where,$options);
            $user_style_total= array();
            $Product = new Product;
            foreach ($user_style_list as &$user_style) {
                $user_style['average_price'] = sprintf("%.2f",($user_style['price']/$user_style['num']));
                $user_style['skc_depth'] = sprintf("%.1f",($user_style['num']/$user_style['skc']));
                $user_style_total['num']  += $user_style['num'];
                $user_style_total['price']+= $user_style['price'];
                $user_style_total['skc']  += $user_style['skc'];
            }
            foreach ($user_style_list as $key=>&$user_style2) {
                $user_style2['num_percent']     = sprintf("%.1f%%",($user_style2['num']/$user_style_total['num'])*100);
                $user_style2['price_percent']   = sprintf("%.1f%%",($user_style2['price']/$user_style_total['price'])*100);
                $design_skc = $Product->findone("p.style_id={$key}",array("fields"=>"count(*) as design_skc","tablename"=>"product as p left join product_color as pc on p.id=pc.product_id"));
                $user_style2['design_skc']      = $design_skc['design_skc'];
                $user_style_total['design_skc'] +=$design_skc['design_skc'];
            }
            $result['user_style_list'] = $user_style_list;
            $result['user_style_total']= $user_style_total;

            //审核项：款色主推款
            // $options   = array();
            // $options['tablename'] = " product_color as pc left join product as p on pc.product_id=p.id
            //                         left join keywords as k on pc.main_push_id=k.id
            //                         left join orderlist as o on pc.product_id=o.product_id and pc.color_id=o.product_color_id and ".$where_user;
            // $options['fields']= " pc.main_push_id,sum(o.num) as num,sum(o.amount) as price,count(DISTINCT pc.product_id,pc.color_id) as design_skc,count(DISTINCT o.product_id,o.product_color_id) as skc";
            // $options['group'] = " pc.main_push_id";
            // $options['key']   = "main_push_id";
            // $options['order'] = "k.name";
            // $options['limit'] = 1000;
            // //$options['db_debug']=true;
            // $where = $where_brand;
            // $user_main_push_list = $OrderList->find($where,$options);
            // $user_main_push_total= array();
            // foreach ($user_main_push_list as &$main_push) {
            //     $main_push['average_price'] = sprintf("%.2f",($main_push['price']/$main_push['num']));
            //     $main_push['skc_depth'] = sprintf("%.1f",($main_push['num']/$main_push['skc']));
            //     $user_main_push_total['num']  += $main_push['num'];
            //     $user_main_push_total['price']+= $main_push['price'];
            //     $user_main_push_total['skc']  += $main_push['skc'];
            //     $user_main_push_total['design_skc'] +=$main_push['design_skc'];
            // }
            // foreach ($user_main_push_list as $key=>&$main_push2) {
            //     $main_push2['num_percent']     = sprintf("%.1f%%",($main_push2['num']/$user_main_push_total['num'])*100);
            //     $main_push2['price_percent']   = sprintf("%.1f%%",($main_push2['price']/$user_main_push_total['price'])*100);
            // }
            // $result['user_main_push_list'] = $user_main_push_list;
            // $result['user_main_push_total']= $user_main_push_total;

            //审核项：品牌
            $options   = array();
            $options['tablename'] = " product as p left join orderlist as o on p.id=o.product_id and ".$where_user;
            $options['fields']= " p.brand_id,sum(o.num) as num,sum(o.amount) as price,count(DISTINCT product_id,product_color_id) as skc";
            $options['group'] = " p.brand_id";
            $options['key']   = "brand_id";
            $options['limit'] = 1000;
            //$options['db_debug']=true;
            $where = $where_brand;
            $user_brand_list = $OrderList->find($where,$options);
            $user_brand_total= array();
            $Product = new Product;
            foreach ($user_brand_list as &$user_brand) {
                //$user_brand['average_price'] = sprintf("%.2f",($user_style['price']/$user_style['num']));
                //$user_brand['skc_depth'] = sprintf("%.1f",($user_style['num']/$user_style['skc']));
                $user_brand_total['num']  += $user_brand['num'];
                $user_brand_total['price']+= $user_brand['price'];
                //$user_brand_total['skc']  += $user_brand['skc'];
            }
            foreach ($user_brand_list as $key=>&$user_brand2) {
                $user_brand2['num_percent']     = sprintf("%.1f%%",($user_brand2['num']/$user_brand_total['num'])*100);
                $user_brand2['price_percent']   = sprintf("%.1f%%",($user_brand2['price']/$user_brand_total['price'])*100);
                //$design_skc = $Product->findone("brand_id={$key}",array("fields"=>"count(*) as design_skc"));
                //$user_brand2['design_skc']      = $design_skc['design_skc'];
                //$user_brand_total['design_skc'] +=$design_skc['design_skc'];
            }
            $result['user_brand_list'] = $user_brand_list;
            $result['user_brand_total']= $user_brand_total;
        }
        $Company = new Company;
        $result['company'] = $Company->getData();
        Flight::display("orderlist/inspection.html",$result);
    }
    public static function Action_zd_inspection($r,$username){
        Flight::validateUserHasLogin();
        $User = new User;
        $u = $User->findone("username='{$username}'");
        if($u['id']){
            $userIndicator  = UserIndicator::getInstance($u['id']);
            $userIndicator->refresh();  

            $Location       = new Location;
            $callback       = function($id) use ($Location){
                return $Location->getCurrent($id);
            };
            $Cache          = new Cache($callback);
            $result['area1']= $Cache->get($u['area1'], array($u['area1']));
            $result['area2']= $Cache->get($u['area2'], array($u['area2']));
            $UserIndicator = new UserIndicator;
            $u['indicator'] = $UserIndicator->get_indicator($u['id']);
            $u['indicator']['price_percent'] = sprintf("%.2f",($u['indicator']['ord_amount']/$u['indicator']['exp_amount'])*100);
            $result['u'] = $u;

            $UserSlave  = new UserSlave;
            $user_id_list = $UserSlave->get_slave_user_id($u['id']);
            //下线客户
            $OrderList = new OrderList;
            $options   = array();
            $options['tablename'] = " user as u left join orderlistuser as ou on u.id=ou.user_id ";
            $options['fields']= " ou.price,u.name,u.id,u.username ";
            $options['key']   = "id";
            $options['limit'] = 1000;
            //$options['db_debug'] = true;
            $where = "u.id in ({$user_id_list})";
            $user_slave_list = $OrderList->find($where,$options);
            //主推款
            $options    = array();
            $options['tablename'] = " orderlist as o left join product as p on o.product_id=p.id
                                    left join keywords as k on p.style_id=k.id ";
            $options['fields']    = " sum(o.amount) as price,sum(o.num) as num,count(DISTINCT o.product_id,o.product_color_id) as skc";
            $where  = "k.name != '' and k.name != '基本款'";
            //$options['db_debug'] = true;
            
            //必定款
            $options_need = array();
            $options_need['tablename'] = " orderlist as o left join product_color as pc on o.product_id=pc.product_id and o.product_color_id=pc.color_id ";
            $options_need['fields'] = "sum(o.amount) as price,sum(o.num) as num,count(DISTINCT o.product_id,o.product_color_id) as skc";
            //$options_need['db_debug']=true;
            $where_need = " pc.status <> 0 and pc.is_need=1 ";

            //手机壳
            $options_shoujike =array();
            $options_shoujike['tablename'] = " orderlist as o left join product as p on o.product_id=p.id
                                    left join keywords as k on p.classes_id=k.id ";
            $options_shoujike['fields'] = "sum(o.amount) as price,sum(o.num) as num,count(DISTINCT o.product_id,o.product_color_id) as skc";
            $where_shoujike = " k.name = '手机壳' ";
            $style_total    = array();
            foreach ($user_slave_list as &$row) {
                $where_user = $where . " and o.user_id={$row['id']}";
                $row['style'] = $OrderList->findone($where_user,$options);
                $style_total['price'] += $row['style']['price'];
                $style_total['price_total'] += $row['price'];
                $where_user_nedd = $where_need . " and o.user_id={$row['id']}";
                $row['need'] = $OrderList->findone($where_user_nedd,$options_need);

                $where_user_shoujike = $where_shoujike . " and o.user_id={$row['id']}";
                $row['shoujike'] = $OrderList->findone($where_user_shoujike,$options_shoujike);
            }
            foreach ($user_slave_list as &$val) {
                $val['style']['price_percent'] = sprintf("%.2f",($val['style']['price']/$val['price'])*100);
            }

            $style_total['price_percent'] = sprintf("%.2f",($style_total['price']/$style_total['price_total'])*100);
            $result['style_total']     = $style_total;
            $result['user_slave_list'] = $user_slave_list;
            //print_r($user_slave_list);exit;
        }
        Flight::display("orderlist/zd_inspection.html",$result);
    }

    public static function Action_update_top(){
        $User = new User;
        $user_id = $User->id;
        if($user_id){
            $UserIndicator = new UserIndicator;
            $OrderList     = new OrderList;
            $Company       = new Company;
            $indicator  =   $UserIndicator->get_indicator($user_id);
            if($indicator){
                $orderinfo['num']   =   $indicator['ord_num'];
                $orderinfo['price'] =   $indicator['ord_amount'];
                $orderinfo['discount_price'] =   $indicator['ord_discount_amount'];
                if($indicator['exp_num']){
                    $orderinfo['percent_exp_num']   = sprintf("%d%%", $indicator['ord_num']/$indicator['exp_num'] * 100);
                }
                if($indicator['exp_amount']){
                    $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price']/$indicator['exp_amount'] * 100);
                }
            }else{
                list($rank, $orderinfo) = $OrderList->getRank($user_id);
                if($result['user']['exp_num']){
                    $orderinfo['percent_exp_num']   = sprintf("%d%%", $orderinfo['num']/$result['user']['exp_num'] * 100);
                }
                if($result['user']['exp_price']){
                    $orderinfo['percent_exp_price'] = sprintf("%d%%", $orderinfo['discount_price']/$result['user']['exp_price'] * 100);
                }
            }
            $result['user']         = $User->getAttribute();
            $result['company']      = $Company->getData();
            $result['orderinfo']    = $orderinfo;
        }
        Flight::display("orderlist/update_top.html",$result);
    }

}
