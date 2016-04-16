<?php

class Control_order {
    public static function Action_index($r){
        Flight::validateUserHasLogin();
       
        $User        = new User;
        $OrderList   = new OrderList;

        $result['user']         = $User->getAttribute();
        list($rank, $orderinfo) = $OrderList->getRank($User->id);
        $result['rank']         = $rank;
        $result['orderinfo']    = $orderinfo;
        if($result['user']['exp_price']){
            $result['orderinfo']['percent_exp_price']   = sprintf("%d%%", $result['orderinfo']['price']/$result['user']['exp_price'] * 100);
        }
        $result['orderinfo']['price_cn']    = $orderinfo['price'] > 10000 ? sprintf('%d万', $orderinfo['price'] / 10000) : $orderinfo['price'];

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();
        
        Flight::display('order/index.html', $result);
    }

    public static function Action_rank($r){
        Flight::validateUserHasLogin();

        $User        = new User;
        $OrderList   = new OrderList;
        $Product     = new Product;
        list($rank, $orderinfo) = $OrderList->getRank($User->id);
        $result['rank']         = $rank;
        $result['orderinfo']    = $orderinfo;

        $allinfo    = array();
        $allinfo['pnum']    = $Product->getCount("");
        $result['allinfo']  = $allinfo;

        $orderinfo_price_first  = $OrderList->getOrderinfoByRank(1, "price");
        $orderinfo_price_last   = $OrderList->getOrderinfoByRank($rank - 1);
        $result['rank_price']   = $rank;
        $result['orderinfo_price_first']    = $orderinfo_price_first;
        $result['orderinfo_price_last']     = $orderinfo_price_last;

        list($rank_num, $orderinfo)         = $OrderList->getRank($User->id, "num");
        $orderinfo_num_first    = $OrderList->getOrderinfoByRank(1, "num");
        $orderinfo_num_last     = $OrderList->getOrderinfoByRank($rank_num - 1);
        $result['rank_num']     = $rank_num;
        $result['orderinfo_num_first']      = $orderinfo_num_first;
        $result['orderinfo_num_last']       = $orderinfo_num_last;

        list($rank_pnum, $orderinfo)        = $OrderList->getRank($User->id, "pnum");
        $orderinfo_pnum_first   = $OrderList->getOrderinfoByRank(1, "pnum");
        $orderinfo_pnum_last    = $OrderList->getOrderinfoByRank($rank_pnum - 1);
        $result['rank_pnum']    = $rank_pnum;
        $result['orderinfo_pnum_first']     = $orderinfo_pnum_first;
        $result['orderinfo_pnum_last']      = $orderinfo_pnum_last;

        $product_list2      = $OrderList->getOrderMyProductTopList($User->id, 3, "price");
        foreach($product_list2 as &$product2){
            $product2['percent_num']    = sprintf("%d%%", $product2['num']/$result['orderinfo']['num'] * 100);
            $product2['percent_price']  = sprintf("%d%%", $product2['price']/$result['orderinfo']['price'] * 100);
            $plist = $OrderList->getOrderProductList(array("product_id"=>$product1["id"]), array());
            $product2['all_price']      = $plist[0]['price'];
            $product2['percent_all_price']  = sprintf("%.2f%%", $product2['price']/$product2['all_price'] * 100);
            $product2['all_price_rank']     = $OrderList->getOrderAllRank($product2['price'], array('product_id'=>$product2['id']));
        }
        $len2   = count($product_list2);
        while($len2 < 3){
            $product_list2[]    = array();
            $len2++;
        }
        $result['product_list2']    = $product_list2;

        $product_list1      = $OrderList->getOrderMyProductTopList($User->id, 3, "num");
        foreach($product_list1 as &$product1){
            $product1['percent_num']    = sprintf("%d%%", $product1['num']/$result['orderinfo']['num'] * 100);
            $product1['percent_price']  = sprintf("%d%%", $product1['price']/$result['orderinfo']['price'] * 100);
            $plist = $OrderList->getOrderProductList(array("product_id"=>$product1["id"]), array());
            $product1['all_num']    = $plist[0]['num'];
            $product1['percent_all_num']    = sprintf("%.2f%%", $product1['num']/$product1['all_num'] * 100);
            $product1['all_num_rank']       = $OrderList->getOrderAllRank_num($product1['num'], array('product_id'=>$product1['id']));
        }
        $len1   = count($product_list1);
        while($len1 < 3){
            $product_list1[]    = array();
            $len1++;
        }
        $result['product_list1']    = $product_list1;

        Flight::display("order/rank.html", $result);
    }

    public static function Action_analysis($r){
        Flight::validateUserHasLogin();

        $User       = new User;
        $OrderList  = new OrderList;
        list($rank, $orderinfo) = $OrderList->getRank($User->id);
        $result['orderinfo']    = $orderinfo;
        $result['percent_price']    = $User->exp_price  ? sprintf("%d%%", $orderinfo['price'] / $User->exp_price * 100) : "100%";
        $result['percent_num']      = $User->exp_num    ? sprintf("%d%%", $orderinfo['num'] / $User->exp_num * 100)     : "100%";
        $result['exp_num']          = $User->exp_num;
        $result['exp_price']        = $User->exp_price;

        Flight::display("order/analysis.html", $result);
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

        Flight::display("order/analysis_order.html", $result);
    }


}