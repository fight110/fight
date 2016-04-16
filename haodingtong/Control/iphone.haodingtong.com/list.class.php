<?php

class Control_list {
    
    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;
        list($rank, $orderinfo) = $OrderList->getRank($User->id);
        $result['orderinfo']    = $orderinfo;
        $result['percent_price']    = $User->exp_price  ? sprintf("%d%%", $orderinfo['price'] / $User->exp_price * 100) : "100%";
        $result['exp_price']        = $User->exp_price;

        $Product            = new Product;
        $SKC_ALL            = $Product->get_SKC();
        $result['depth']    = sprintf("%d", $result['orderinfo']['num'] / $result['orderinfo']['sku']);
        $result['width']    = sprintf("%d%%", $result['orderinfo']['sku'] / $SKC_ALL * 100);
        $result['SKC_ALL']  = $SKC_ALL;

        Flight::display("list/index.html", $result);
    }

    public static function Action_analysis_order($r){
        Flight::validateUserHasLogin();

        $data           = $r->query;
        $t              = $data->t      ? $data->t      : "series";
        $limit          = $data->limit  ? $data->limit  : 15;
        $p              = $data->p      ? $data->p      : 1;
        $zongdai        = $data->zongdai;
        $OrderList      = new OrderList;
        $User           = new User;
        $Keywords       = new Keywords;
        $Product        = new Product;
        $data           = $r->query;
        $condition      = array();
        $options        = array();
        switch ($t) {
            case 'size'     :
                $options['group']   = "o.product_{$t}_id";
                $options['fields_more'] = "o.product_{$t}_id as keyword_id";
                $ProductSize    = new ProductSize;
                $product_analysis_info  = $ProductSize->find("product_id in (SELECT id FROM product where status=1)", array("key"=>"size_id", "group"=>"size_id", "fields"=>"size_id,COUNT(*) as num"));
                $tname  = "尺码";
                break;
            case 'color'    : 
                $options['group']   = "o.product_{$t}_id";
                $options['fields_more'] = "o.product_{$t}_id as keyword_id";
                $ProductColor   = new ProductColor;
                $product_analysis_info  = $ProductColor->find("product_id in (SELECT id FROM product where status=1)", array("key"=>"color_id", "group"=>"color_id", "fields"=>"color_id,COUNT(*) as num"));
                $tname  = "色系";
                break;
            case 'product' :
                $options['group']   = "o.product_id";
                $options['fields_more'] = "p.id,p.name, p.bianhao, p.kuanhao";
                $tname  = "款式";
                break;
            default :
                $options['group']   = "p.{$t}_id";
                $options['fields_more'] = "p.{$t}_id as keyword_id";
                $product_analysis_info  = $Product->find("status=1", array("key"=>"{$t}_id", "group"=>"{$t}_id", "fields"=>"{$t}_id,COUNT(*) as num"));
                $tname_hash = array(
                    "series"    => "系列",
                    "category"  => "大类",
                    "wave"      => "波段",
                    "price_band"    => "价格带"
                );
                $tname  = $tname_hash[$t];
        }
        if($User->type == 3){
            $condition['ad_area1']  = $User->area1;
            $condition['ad_area2']  = $User->area2;
        }else{
            $condition['user_id']   = $User->id;
        }
        if($zongdai){
            $list           = $OrderList->getDealer2OrderList($condition, $options);
        }else{
            $list           = $OrderList->getOrderAnalysisList($condition, $options);
        }
        $pnum           = 0;
        $num            = 0;
        $price          = 0;
        foreach($list as $item){
            $pnum       += $item['pnum'];
            $num        += $item['num'];
            $price      += $item['price'];
        }

        // $start  = ($p - 1) * $limit;
        // $list   = array_slice($list, $start, $limit);

        foreach($list as &$row){
            if($pnum){
                $row['percent_pnum']    = sprintf("%.2f%%", $row['pnum']/$pnum * 100);
            }
            if($num){
                $row['percent_num']     = sprintf("%.2f%%", $row['num']/$num * 100);
            }
            if($price){
                $row['percent_price']   = sprintf("%.2f%%", $row['price']/$price * 100);
            }
            if($t != "product" && $t != "user"){
                $row['name']    = $Keywords->getName_File($row['keyword_id']);
            }
            if($t == "product"){
                $row['rank']    = $OrderList->getOrderProductRank($row['product_id'], $row['price']);
            }
            if($product_analysis_info){
                $row['product_all_num'] = $product_analysis_info[$row['keyword_id']]['num'];
            }
        }
        
        $result['list'] = $list;
        $result['t']    = $t;
        $result['tname']    = $tname;

        Flight::display("list/analysis_order.html", $result);
    }


}