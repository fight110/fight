<?php

class Control_order_manage {
    public static function Action_index($r){
    }

    public static function Action_user($r){
        Flight::validateEditorHasLogin();

        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 15;
        $options['page']    = $p;
        $options['limit']   = $limit;
        $options['count']   = true;
        // $options['db_debug']    = true;
        $condition      = array();
        $keys   = array('search_user', 'area1', 'area2', 'style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        $OrderList      = new OrderList;
        $list           = $OrderList->getOrderUserList($condition, $options);
        $total          = $OrderList->get_count_total();
        $list           = Flight::listFetch($list, "location", "area2", "id");
        $result['list'] = $list;
        $result['t']    = 'user';
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display('order_manage/user.html', $result);
    }

    public static function Action_style($r){
        Flight::validateEditorHasLogin();

        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 15;
        $condition      = array();
        $options        = array();
        $options['page']        = $p;
        $options['limit']       = $limit;
        $options['count']   = true;
        // $options['db_debug']    = true;
        $keys   = array('search', 'area1', 'area2', 'style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        $order          = $data->order;
        switch ($order) {
            case 1  :
                $options['order']   = "price desc";
                break;
            case 2  :
                $options['order']   = "price asc";
                break;
            case 3  :
                $options['order']   = "num desc";
                break;
            case 4  :
                $options['order']   = "num asc";
                break;
            default :
                $options['order']   = "num desc";
        }
        $OrderList      = new OrderList;
        $list           = $OrderList->getOrderProductList($condition, $options);
        $total          = $OrderList->get_count_total();
        $result['list'] = $list;
        $result['t']        = 'style';
        $result['order']    = $order;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display("order_manage/style.html", $result);
    }


    public static function Action_user_style($r, $id){
        Flight::validateEditorHasLogin();

        $data           = $r->query;
        $condition      = array();
        $keys   = array('search', 'style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        $condition['user_id']   = $id;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 15;
        $options['page']        = $p;
        $options['limit']       = $limit;
        $options['count']       = true;
        $OrderList      = new OrderList;
        $Location       = new Location;
        $list           = $OrderList->getOrderProductList($condition, $options);
        $total          = $OrderList->get_count_total();
        $result['list'] = $list;
        $result['t']    = 'user';
        $result['id']   = $id;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);

        //经销商的订货信息统计
        $statistics     = $OrderList->getOrderUserList(array("user_id"=>$id));
        $result['user'] = $statistics[0];
        $areaid         = $result['user']['area2']  ? $result['user']['area2']  : $result['user']['area1'];
        $result['user']['location'] = $Location->getCurrent($areaid);

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display("order_manage/user_style.html", $result);
    }

    public static function Action_style_user($r, $id){
        Flight::validateEditorHasLogin();

        $data           = $r->query;
        $condition      = array();
        $keys   = array('search', 'area1', 'area2', 'style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        $condition['product_id']    = $id;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 15;
        $options['page']        = $p;
        $options['limit']       = $limit;
        $options['count']       = true;
        $OrderList      = new OrderList;
        $list           = $OrderList->getOrderUserList($condition, $options);
        $total          = $OrderList->get_count_total();
        $list           = Flight::listFetch($list, "location", "area2", "id");
        $result['list'] = $list;
        $result['t']    = 'style';
        $result['id']   = $id;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);

        //单个款式的订货信息统计
        $statistics         = $OrderList->getOrderProductList(array("product_id"=>$id));
        $result['product']  = $statistics[0];

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display("order_manage/style_user.html", $result);
    }

    public static function Action_print($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $condition      = array();
        $options        = array();
        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 10;
        $t              = $data->t      ? $data->t      : 1;

        $keys   = array('search_user', 'area1', 'area2');
        foreach($keys as $k){
            $v  = $data->$k;
            $condition[$k]  = $v;
            $result[$k]     = $v;
        }
        $options['page']    = $p;
        $options['limit']   = $limit;
        $options['count']   = true;
        // $options['db_debug']    = true;
        if($t == 2){
            $options['group']   = "us.user_id";
            $list           = $OrderList->getDealer2OrderList($condition, $options);
            $total          = $OrderList->get_count_total();
        }else{
            $list           = $OrderList->getOrderUserList($condition, $options);
            $total          = $OrderList->get_count_total();
        }

        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['t']    = "print";

        Flight::display("order_manage/print.html", $result);
    }

    public static function Action_print2($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $User           = new User;
        $condition      = array();
        $options        = array();
        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 10;
        $t              = $data->t      ? $data->t      : 1;

        $keys   = array('search_user', 'area1', 'area2');
        foreach($keys as $k){
            $v  = $data->$k;
            $condition[$k]  = $v;
            $result[$k]     = $v;
        }
        $options['page']    = $p;
        $options['limit']   = $limit;
        $options['count']   = true;
        // $options['db_debug']    = true;
        $list           = $User->find("type=2", $options);
        $total          = $User->get_count_total();

        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['t']    = "print2";

        Flight::display("order_manage/print2.html", $result);
    }

    public static function Action_productstatusset($r, $id){
        Flight::validateEditorHasLogin();

        $ProductStatus  = new ConfigDataList('ProductStatus0');
        if(!in_array($id, $ProductStatus->getData())){
            $Product    = new Product($id);
            if($Product->status != 0){
                $ProductStatus->add($id);
            }
        }

        Flight::redirect("/order_manage/deleted");
    }

    public static function Action_deleted($r){
        Flight::validateEditorHasLogin();

        //待删款
        $OrderList      = new OrderList;
        $ProductStatus  = new ConfigDataList('ProductStatus0');
        $product_list   = $ProductStatus->getData();
        if(count($product_list)){
            $product_ids    = implode(',', $product_list);
            $result['product_list'] = $OrderList->getOrderProductList(array('product_ids'=>$product_ids));
        }

        $Product    = new Product;
        $list       = $Product->find("status=0", array("limit"=>100));
        foreach($list as &$row){
            $orderlist  = $OrderList->getOrderProductList(array('product_id'=>$row['id']), array('status'=>false));
            $row['orderlist']   = $orderlist[0];
        }
        $result['list'] = $list;

        $result['t']    = "deleted";

        Flight::display("order_manage/deleted.html", $result);
    }

    public static function Action_cancel_wd($r, $id){
        Flight::validateEditorHasLogin();

        $ProductStatus  = new ConfigDataList('ProductStatus0');
        $OrderList      = new OrderList;
        $list   = $ProductStatus->getData();
        foreach($list as $key => $val){
            if($val == $id){
                $ProductStatus->remove($key);
                if($r->query->delete){
                    $Product    = new Product;
                    $Product->update(array('status'=>0), "id={$id}");
                    $OrderList->refresh_product_id($id);
                }
                break;
            }
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_copy($r){
        Flight::validateEditorHasLogin();

        $data           = $r->query;
        $condition      = array();
        $keys   = array('search_user', 'area1', 'area2','agent_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
        }
        $User           = new User;
        $OrderList      = new OrderList;
        $Location       = new Location;
        $Cache          = new Cache(function($id) use($Location){
            return $Location->getCurrent($id);
        });
        if($result['search_user']){
            $qt     = trim($result['search_user']);
            if($qt){
                $condition[]    = "(name like '%{$qt}%' OR username='{$qt}')";
            }
        }
        if($result['area1'])    $condition[]    = "area1={$result['area1']}";
        if($result['area2'])    $condition[]    = "area2={$result['area2']}";
        if($result['agent_id']){
            $condition[]    = "(id in (select user_slave_id from user_slave where user_id={$result['agent_id']}) or id={$result['agent_id']})";
        }
        $condition[]    = "type=1";
        $where  = implode(' AND ', $condition);
        $list           = $User->find($where, array("page"=>$r->query->p));
        $total          = $User->getCount($where);
        foreach($list as &$row){
            $agent      = User::find_agent ($row['id']);
            if($agent) {
                $row['agentname']   = $agent->name;
            }
            /* $areaid     = $row['area2'] ? $row['area2'] : $row['area1'];
            $row['location']    = $Cache->get("Location-{$areaid}", array($areaid)); */
            $row['area1']   =   $Cache->get("Location-{$row['area1']}", array($row['area1']));
            $row['area2']   =   $Cache->get("Location-{$row['area2']}", array($row['area2']));
            $orderlist  = $OrderList->getOrderUserList(array("user_id"=>$row['id']));
            $row['num']     = $orderlist[0]['num'];
            $row['pnum']    = $orderlist[0]['pnum'];
            $row['price']   = $orderlist[0]['price'];
        }
        //print_r($list);exit;
        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total);

        $result['t']    = "copy";
        $copy   = SESSION::get('order_copy');
        $result['copy'] = $copy;
        $result['agent_list']       = $User->find("type=2", array("limit"=>1000));


        Flight::display('order_manage/copy.html', $result);
    }

    public static function Action_setcopy($r, $type='from'){
        Flight::validateEditorHasLogin();

        $copy   = SESSION::get('order_copy');
        if(!is_array($copy)){
            $copy   = array();
        }
        $username   = $r->query->username;
        $copy[$type]    = $username;
        SESSION::set('order_copy', $copy);

        $returl     = $r->query->returl;
        if(!$returl)    $returl = $r->referrer;
        Flight::redirect($returl);
    }

    public static function Action_copy_order($r){
        Flight::validateEditorHasLogin();

        $copy_from  = $r->data->copy_from;
        $copy_to    = $r->data->copy_to;
        $copy_type  = $r->data->copy_type;
        $copy_time  = $r->data->copy_time;
        if(!$copy_from){
            SESSION::message("复制来源帐号不能为空");
        }elseif(!$copy_to){
            SESSION::message("复制目标帐号不能为空");
        }else{
            $copy   = SESSION::get('order_copy');
            if(!is_array($copy)){
                $copy   = array();
            }
            $copy['from']    = $copy_from;
            $copy['to']      = $copy_to;
            SESSION::set('order_copy', $copy);

            $OrderList  = new OrderList;
            $result = $OrderList->copy_order($copy_from, $copy_to, $copy_type, $copy_time);
            if($result['valid'] == false){
                SESSION::message($result['message']);
            }else{
                SESSION::message("复制成功，总共复制订单总数:{$result['count']}");
            }
        }

        Flight::redirect($r->referrer);
    }
    
    public static function Action_clear($r){
        Flight::validateEditorHasLogin();
        
        $data           = $r->query;
        $condition      = array();
        $keys   = array('search_user', 'area1', 'area2','agent_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
        }
        $User           = new User;
        $OrderList      = new OrderList;
        $Location       = new Location;
        $Cache          = new Cache(function($id) use($Location){
            return $Location->getCurrent($id);
        });
        if($result['search_user']){
            $qt     = trim($result['search_user']);
            if($qt){
                $condition[]    = "(name like '%{$qt}%' OR username='{$qt}')";
            }
        }
        if($result['area1'])    $condition[]    = "area1={$result['area1']}";
        if($result['area2'])    $condition[]    = "area2={$result['area2']}";
        if($result['agent_id']){
            $condition[]    = "(id in (select user_slave_id from user_slave where user_id={$result['agent_id']}) or id={$result['agent_id']})";
        }
        $condition[]    = "type=1";
        $where  = implode(' AND ', $condition);
        $list           = $User->find($where, array("page"=>$r->query->p));
        $total          = $User->getCount($where);
        foreach($list as &$row){
            $agent      = User::find_agent ($row['id']);
            if($agent) {
                $row['agentname']   = $agent->name;
            }
            /* $areaid     = $row['area2'] ? $row['area2'] : $row['area1'];
             $row['location']    = $Cache->get("Location-{$areaid}", array($areaid)); */
            $row['area1']   =   $Cache->get("Location-{$row['area1']}", array($row['area1']));
            $row['area2']   =   $Cache->get("Location-{$row['area2']}", array($row['area2']));
            $orderlist  = $OrderList->getOrderUserList(array("user_id"=>$row['id']));
            $row['num']     = $orderlist[0]['num'];
            $row['pnum']    = $orderlist[0]['pnum'];
            $row['price']   = $orderlist[0]['price'];
        }
        //print_r($list);exit;
        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total);
        
        $result['t']    = "clear";
        $copy   = SESSION::get('order_copy');
        $result['copy'] = $copy;
        $result['agent_list']       = $User->find("type=2", array("limit"=>1000));
        
        Flight::display('order_manage/clear.html', $result);
    }
    
    public static function Action_clear_order($r){
        $data       = $r->data;
        $username   = $data->username;
        $Company    = new Company;
        $User       = new User;
        // 清空帐号订单
        if($username){
            $u     = $User->findone("username='{$username}'");
            if($user_id = $u['id']){
                if($Company->order_proportion_status) {
                    $OrderListProportion    = new OrderListProportion;
                    $list   = $OrderListProportion->find("user_id={$user_id}", array("limit"=>100000));
                    foreach($list as $row) {
                        ProductOrder::proportion_add($user_id, $row['product_id'], $row['product_color_id'], $row['proportion_id'], 0);
                    }
                }else{
                    $OrderList  = new OrderList;
                    $list   = $OrderList->find("user_id={$user_id}", array("limit"=>1000000));
                    foreach($list as $row){
                        ProductOrder::add($user_id, $row['product_id'], $row['product_color_id'], $row['product_size_id'], 0);
                    }
                }
                ProductOrder::run();
                // $OrderList->remove_order($user_id);
                SESSION::message(sprintf("删除%s的订单成功", $username));
            }else{
                SESSION::message(sprintf("用户名%s不存在", $username));
            }
        }
        Flight::redirect($r->referrer);
    }
}