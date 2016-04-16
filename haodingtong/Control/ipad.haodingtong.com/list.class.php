<?php

class Control_list {

    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $result     = FrontSetting::build();

        $data       = $r->query;

        Flight::display("list/index.html", $result);
    }

    public static function Action_hotproduct($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit  ? $data->limit  : 10;
        $User       = new User;
        $OrderList  = new OrderList;
        $condition  = array();
        $options    = array();
        $options['order']   = "num desc";
        $options['page']        = $p;
        $options['limit']       = $limit;

        $list       = $OrderList->getOrderProductList($condition, $options);

        $start      = ($p - 1) * $limit;
        foreach($list as &$row){
            $row['topthree']    = SESSION::cache("ProductTopUserThree-{$row['id']}", function() use($OrderList, $row){
                return $OrderList->getOrderProductUserTopList($row['id'], 3);
            });
        }

        $result['list']     = $list;
        $result['start']    = $start;


        Flight::display("list/hotproduct.list.html", $result);
    }

    public static function Action_series($r){
        Flight::validateUserHasLogin();

        $OrderList      = new OrderList;
        $Keywords       = new Keywords;
        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 10;
        $condition      = array();
        $options        = array();
        $options['group']   = "p.series_id";
        $options['fields_more'] = "p.series_id as keyword_id";
        $options['order']   = "num desc";
        $options['page']    = $p;
        $options['limit']   = $limit;

        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $start      = ($p - 1) * $limit;
        foreach($list as &$row){
            $row['name']    = $Keywords->getName_File($row['keyword_id']);
            $row['toplist'] = SESSION::cache("TOP-SERIES-USER-{$row['keyword_id']}", function() use($OrderList, $row){
                $condition  = array();
                $options    = array();
                $condition['series_id'] = $row['keyword_id'];
                $options['page']    = 1;
                $options['limit']   = 3;
                return $OrderList->getOrderUserList($condition, $options);
            });
        }

        $result['list']     = $list;
        $result['start']    = $start;

        Flight::display("list/series.list.html", $result);
    }

    public static function Action_category($r){
        Flight::validateUserHasLogin();

        $OrderList      = new OrderList;
        $Keywords       = new Keywords;
        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 10;
        $condition      = array();
        $options        = array();
        $options['group']   = "p.category_id";
        $options['fields_more'] = "p.category_id as keyword_id";
        $options['order']   = "num desc";
        $options['page']    = $p;
        $options['limit']   = $limit;

        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $start      = ($p - 1) * $limit;
        foreach($list as &$row){
            $row['name']    = $Keywords->getName_File($row['keyword_id']);
            $row['toplist'] = SESSION::cache("TOP-CATEGORY-USER-{$row['keyword_id']}", function() use($OrderList, $row){
                $condition  = array();
                $options    = array();
                $condition['category_id'] = $row['keyword_id'];
                $options['page']    = 1;
                $options['limit']   = 3;
                return $OrderList->getOrderUserList($condition, $options);
            });
        }

        $result['list']     = $list;
        $result['start']    = $start;

        Flight::display("list/category.list.html", $result);
    }


    public static function Action_num($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit  ? $data->limit  : 10;
        $order      = $data->order  ? $data->order  : 'num';
        $condition  = array();
        $options    = array();
        $options['order']   = "num desc";
        $options['page']        = $p;
        $options['limit']       = $limit;
        $OrderList  = new OrderList;
        $list       = $OrderList->getOrderUserList($condition, $options); 
        $start      = ($p - 1) * $limit;
        foreach($list as &$row){
            $row['percent_exp_num']     = $row['exp_num']       ? sprintf("%d%%", $row['num'] / $row['exp_num'] * 100)      : "100%";
            $row['percent_exp_price']   = $row['exp_price']     ? sprintf("%d%%", $row['price'] / $row['exp_price'] * 100)  : "100%";
        }

        $result['list']     = $list;
        $result['start']    = $start;

        Flight::display("list/num.list.html", $result);
    }

    public static function Action_price($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $p          = $data->p      ? $data->p      : 1;
        $limit      = $data->limit  ? $data->limit  : 10;
        $order      = $data->order  ? $data->order  : 'price';
        $condition  = array();
        $options    = array();
        $options['page']        = $p;
        $options['limit']       = $limit;
        $OrderList  = new OrderList;
        $list       = $OrderList->getOrderUserList($condition, $options); 
        $start      = ($p - 1) * $limit;
        foreach($list as &$row){
            $row['percent_exp_num']     = $row['exp_num']       ? sprintf("%d%%", $row['num'] / $row['exp_num'] * 100)      : "100%";
            $row['percent_exp_price']   = $row['exp_price']     ? sprintf("%d%%", $row['price'] / $row['exp_price'] * 100)  : "100%";
        }

        $result['list']     = $list;
        $result['start']    = $start;

        Flight::display("list/num.list.html", $result);
    }



}