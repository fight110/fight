<?php

class Control_exp {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $OrderList      = new OrderList;
        $data           = $r->query;
        $p              = $data->p  ? $data->p  : 1;
        $limit          = 10;
        $condition      = array();
        $options        = array();
        $keys   = array('area1', 'area2');
        foreach($keys as $key){
            $val                = $data->$key;
            $result[$key]       = $val;
            $condition[$key]    = $val;
        }

        $options['group']   = "o.user_id";
        $options['fields_more'] = "u.name,u.exp_num,u.exp_price,u.exp_pnum";
        $options['page']    = $p;
        $options['limit']   = $limit;
        $options['count']   = true;

        $list           = $OrderList->getOrderAnalysisList($condition, $options);
        $total          = $OrderList->get_count_total();
        
        foreach($list as &$row){
            $row['percent_price']   = $row['exp_price']     ? sprintf("%.2f%%", $row['price']/$row['exp_price'] * 100)  : "-";
            $row['percent_num']     = $row['exp_num']       ? sprintf("%.2f%%", $row['num']/$row['exp_num'] * 100)  : "-";
        }
        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);


        Flight::display('exp/index.html', $result);
    }


}