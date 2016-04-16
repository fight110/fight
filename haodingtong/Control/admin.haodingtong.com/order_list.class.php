<?php

class Control_order_list {
    public static function Action_product($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $data           = $r->query;
        $condition      = array();
        $options        = array();
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 20;
        $options['limit']       = $limit;
        $options['page']        = $p;
        $options['count']       = true;
        $keys   = array('style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }

        $options['group']   = "o.product_id";
        $options['fields_more'] = "p.name, p.bianhao, p.kuanhao, p.price as s_price, COUNT(DISTINCT o.product_color_id) as count_color, COUNT(DISTINCT o.user_id) as unum";

        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $total          = $OrderList->get_count_total();

        $result['list'] = $list;
        $result['t']    = "product";
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['start']    = ($p - 1) * $limit;

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display('order_list/product.html', $result);
    }

    public static function Action_product_color($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $data           = $r->query;
        $condition      = array();
        $options        = array();
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 20;
        $options['limit']       = $limit;
        $options['page']        = $p;
        $options['count']       = true;
        $keys   = array('style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        $options['group']       = "o.product_id, o.product_color_id";
        $options['fields_more'] = "o.product_color_id as keyword_id,p.name, p.bianhao, p.kuanhao, p.price as s_price, COUNT(DISTINCT o.user_id) as unum";

        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $total          = $OrderList->get_count_total();
        $list           = Flight::listFetch($list, 'keywords', 'keyword_id', 'id');

        $result['list'] = $list;
        $result['t']    = "product_color";
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['start']    = ($p - 1) * $limit;

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display('order_list/product_color.html', $result);
    }

    public static function Action_order_count($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $data           = $r->query;
        $condition      = array();
        $options        = array();
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 20;
        $options['limit']       = $limit;
        $options['page']        = $p;
        $options['count']       = true;
        $keys   = array('style_id', 'wave_id', 'category_id', 'series_id');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }
        $options['group']       = "o.user_id";
        $options['fields_more'] = "u.name, u.exp_num, u.exp_price, u.exp_pnum";
        $order                  = $data->order;
        if(!in_array($order, array('num', 'price'))){
            $order  = "num";
        }
        $options['order']       = "{$order} desc";

        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $total          = $OrderList->get_count_total();
        foreach($list as &$row){
                // $row['percent_num']     = $row['exp_num']       ? sprintf("%.2f%%", $row['num'] / $row['exp_num'] * 100)        : "-";
                $row['percent_price']   = $row['exp_price']     ? sprintf("%.2f%%", $row['price'] / $row['exp_price'] * 100)    : "-";
        }

        $result['list'] = $list;
        $result['t']    = "order_count_{$order}";
        $result['order']    = $order;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['start']    = ($p - 1) * $limit;

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();

        Flight::display('order_list/order_count.html', $result);
    }


    public static function Action_series($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $data           = $r->query;
        $condition      = array();
        $options        = array();

        $options['group']   = "p.series_id";
        $options['fields_more'] = "p.series_id";
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 20;
        $options['limit']       = $limit;
        $options['page']        = $p;
        $options['count']       = true;

        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $total          = $OrderList->get_count_total();
        $list           = Flight::listFetch($list, 'keywords', 'series_id', 'id');
        foreach($list as &$row){
                // $row['list']    = $OrderList->getOrderUserList(array('series_id'=>$row['series_id']), array('page'=>1, 'limit'=>3));
        }

        $result['list'] = $list;
        $result['t']    = "series";
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['start']    = ($p - 1) * $limit;


        Flight::display('order_list/series.html', $result);
    }

    public static function Action_classes($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $data           = $r->query;
        $condition      = array();
        $options        = array();

        $options['group']   = "p.classes_id";
        $options['fields_more'] = "p.classes_id";
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 20;
        $options['limit']       = $limit;
        $options['page']        = $p;
        $options['count']       = true;

        // $OrderListProduct   = new OrderListProduct;
        
        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $total          = $OrderList->get_count_total();
        $list           = Flight::listFetch($list, 'keywords', 'classes_id', 'id');
        foreach($list as &$row){
                // $row['list']    = $OrderList->getOrderUserList(array('classes_id'=>$row['classes_id']), array('page'=>1,'limit'=>3));
        }

        $result['list'] = $list;
        $result['t']    = "classes";
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['start']    = ($p - 1) * $limit;


        Flight::display('order_list/classes.html', $result);
    }


}