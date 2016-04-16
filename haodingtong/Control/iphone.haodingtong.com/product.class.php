<?php

class Control_product {
    public static function Action_index($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $limit      = $data->limit ? $data->limit : 15;
        $p          = $data->p;
        $User       = new User;
        $Product    = new Product;
        $OrderList  = new OrderList;
        $ProductComment = new ProductComment;
        $keywords   = new Keywords;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $series_id      = $data->series_id;
        $wave_id        = $data->wave_id;
        $ordered        = $data->ordered;
        $group_id       = $data->group_id;
        $group_product_id   = $data->group_product_id;
        $q              = $data->q;
        $cond           = array();
        $fields         = "*";
        if($style_id)       $cond[]    = "style_id={$style_id}";
        if($category_id)    $cond[]    = "category_id={$category_id}";
        if($wave_id)        $cond[]    = "wave_id={$wave_id}";
        if($series_id)      $cond[]    = "series_id={$series_id}";
        if($ordered == "on"){
            $cond[] = "id in (SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id)";
        }elseif($ordered == "off"){
            $cond[] = "id not in (SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id)";
            $kid    = $keywords->getKeywordId("必定款");
            if(!$kid)   $kid = 0;
            $fields = "*,IF(style_id={$kid}, 1, 0) as krank";
            $order  = "status desc,krank desc,bianhao asc";
        }elseif($ordered == "unactive"){
            $cond[] = "status=0";
            $cond[] = "id in (SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id)";
        }
        if(!$order){
            $order  = "bianhao asc";
        }
        if($group_id){
            $cond[] = "id in (SELECT product_id FROM product_group_member WHERE group_id={$group_id})";
        }
        if($group_product_id){
            $ProductGroupMember     = new ProductGroupMember;
            $other_list     = $ProductGroupMember->getGroupOtherMember($group_product_id);
            foreach($other_list as $other){
                $other_ids[]    = $other['product_id'];
            }
            if(count($other_ids) == 0){
                $other_id   = "0";
            }else{
                $other_id   = implode(',', $other_ids);
            }
            $cond[] = "id in ($other_id)";
        }
        if($q){
            $qt     = addslashes($q);
            $cond[] = "bianhao='{$qt}'";
        }

        $where      = implode(' AND ', $cond);
        $list       = $Product->find($where, array("limit"=>$limit, "page"=>$p, "order"=>$order, 'fields'=>$fields));

        if(count($list)){
            
            $condition  = array();
            $options    = array();
            $options['key']     = "product_id";
            $options['fields_more'] = "o.product_id";
            $options['status']  = false;
            if($ordered != "off"){
                $condition['user_id']   = $User->id;
                $order_user = $OrderList->getOrderAnalysisList($condition, $options);
            }else{
                $order_user = array();
            }
            
            foreach($list as &$row){
                $product_id         = $row['id'];
                $row['order_user']  = $order_user[$product_id];
                if($row['status']){
                    $row['style']   = $keywords->getName_File($row['style_id']);
                    $row['scoreinfo']   = $ProductComment->getAvgScore($product_id, true);
                }
            }
        }

        $result['list'] = $list;


        Flight::display("product/index.html", $result);
    }

}