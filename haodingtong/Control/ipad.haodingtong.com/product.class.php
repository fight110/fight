<?php

class Control_product {
    public static function Action_list($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $limit      = $data->limit ? $data->limit : 9;
        $p          = $data->p  ? $data->p : 1;
        $User       = new User;
        $Product    = new Product;
        $OrderList  = new OrderList;
        $ProductComment = new ProductComment;
        $keywords   = new Keywords;
        $Company    = new Company;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $medium_id      = $data->medium_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $season_id      = $data->season_id;
        $wave_id        = $data->wave_id;
        $fabric_id      = $data->fabric_id;
        $price_band_id  = $data->price_band_id;
        $theme_id       = $data->theme_id;
        $brand_id       = $data->brand_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $edition_id     = $data->edition_id;
        $contour_id     = $data->contour_id;
        $designer_id    = $data->designer_id;
        $sxz_id         = $data->sxz_id;
        $main_push_id   = $data->main_push_id;
        $is_need        = $data->is_need;
        $ordered        = $data->ordered;
        $group_id       = $data->group_id;
        $display_id     = $data->display_id;
        $order          = $data->order;
        $myorder        = $data->myorder;
        $view           = $data->view;
        $tType          = $data->tType;
        $area1			= $data->area1;
        $area2			= $data->area2;
        $permission_brand = $User->permission_brand;
        $isspot         = $User->current_isspot;
        if($designer_id){
            $user_designer_id=$designer_id;
        }elseif($User->type == 9){
            $name = $User->name;
            $user_designer_id = $keywords->getKeywordId($name);
        }

        if($view == "ST"){
            STATIC::list_ST($r);
            exit;
        }
        $group_product_id   = $data->group_product_id;
        $q              = $data->q;
        $cond           = array();
        $fields         = "*";
        $show_limit     = 0;
        //$kid    = $keywords->getKeywordId(STRING_BIDINGKUAN);
        if(!$order && $view == "T"){
            $order = "all num desc";
        }

        $UserSlave      = new UserSlave();
        $zongdai_id     =   $UserSlave->get_master_uid($User->id);
        //if(!$zongdai_id)$zongdai_id=$User->id;
        
        switch ($order) {
            case 'num asc':
                $fields     .= ",(SELECT SUM(o.num) FROM orderlist as o WHERE o.product_id=product.id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder asc, id asc";
                break;
            case 'num desc':
                $fields     .= ",(SELECT SUM(o.num) FROM orderlist as o WHERE o.product_id=product.id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder desc, id asc";
                break;
            case 'price asc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlist as o WHERE o.product_id=product.id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder asc, id asc";
                break;
            case 'price desc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlist as o WHERE o.product_id=product.id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder desc, id asc";
                break;
            case 'all num asc':
        		if($area1){
        			if($area2){
        				$fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and u.area2={$area2} and o.product_id=product.id) as myorder";
        			} else {
        				$fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and o.product_id=product.id) as myorder";
        			}
        		}	else {
                	$fields     .= ",(SELECT o.num FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
	            }
                $_order     = "myorder asc, id asc";
                break;
            case 'all num desc':
        		if($area1){
        			if($area2){
        				$fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and u.area2={$area2} and o.product_id=product.id) as myorder";
        			} else {
        				$fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and o.product_id=product.id) as myorder";
        			}
            	}	else {
                	$fields     .= ",(SELECT o.num FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
	            }
                $_order     = "myorder desc, id asc";
                break;
            /* case 'agentnumdesc':
                $fields .= ",(SELECT num FROM orderlist_agent as o WHERE o.user_id={$zongdai_id} AND o.product_id=product.id) as myorder";
                $_order = "myorder desc";
                break;
            case 'agentnumasc':
                $fields .= ",(SELECT num FROM orderlist_agent as o WHERE o.user_id={$zongdai_id} AND o.product_id=product.id) as myorder";
                $_order = "myorder asc";
                break; */
            default : 1;
        }

        switch ($view) {
            case 'T'    : $limit    = 10; break;
            case 'S'    : $limit    = 49; break;
            default     : 1;
        }
        if($style_id)       $cond[]    = "style_id in ({$style_id})";
        if($category_id)    $cond[]    = "category_id in ({$category_id})";
        if($medium_id)      $cond[]    = "medium_id in ({$medium_id})";
        if($classes_id)     $cond[]    = "classes_id in ({$classes_id})";
        if($wave_id)        $cond[]    = "wave_id in ({$wave_id})";
        if($series_id)      $cond[]    = "series_id in ({$series_id})";
        if($season_id)      $cond[]    = "season_id in ({$season_id})";
        if($fabric_id)      $cond[]    = "fabric_id in ({$fabric_id})";
        if($price_band_id)  $cond[]    = "price_band_id in ({$price_band_id})";
        if($brand_id)       $cond[]    = "brand_id in ({$brand_id})";
        if($theme_id)       $cond[]    = "theme_id in ({$theme_id})";
        if($nannvzhuan_id)  $cond[]    = "nannvzhuan_id in ({$nannvzhuan_id})";
        if($sxz_id)         $cond[]    = "sxz_id in ({$sxz_id})";
        if($edition_id)     $cond[]    = "edition_id in ({$edition_id})";
        if($contour_id)     $cond[]    = "contour_id in ({$contour_id})";
        // if($designer_id)     $cond[]    = "designer_id in ({$designer_id})";
        if($permission_brand) $cond[]  = "brand_id not in ({$permission_brand})";
        if($isspot)         $cond[]    = "isspot in ({$isspot})";
        // if($User->type==9) $cond[] = "designer='{$User->name}'";
        if(is_numeric($is_need)) $cond[]    = "id in (select product_id from product_color WHERE is_need={$is_need})";
        if(is_numeric($main_push_id)) $cond[]    = "id in (select product_id from product_color WHERE main_push_id={$main_push_id})";
        if($ordered == "on"){
            $cond[] = "id in (SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id)";
        }elseif($ordered == "off"){
            $cond[] = "id not in (SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id)";
            if(!$_order){
                //if(!$kid)   $kid = 0;
                //$fields .= ",IF(style_id={$kid}, 1, 0) as krank";
                $fields .= ",is_need as krank";
                $_order  = "status desc,krank desc,bianhao asc";
            }
        }elseif($ordered == "unactive"){
            $cond[] = "status=0";
            if($myorder == "on"){
                $cond[] = "id in (SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id)";
            }
        }elseif($ordered == "onunactive"){
            $cond[] = "id in (SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id)";
            $cond[] = "status=0";
        }
        if($ordered != "unactive"){
            // $cond[] = "status=1";
            $cond[] = "status<>0";
        }
        if(!$_order){
            $_order  = "id asc";
        }
        if($display_id){
            $limit  = 50;
            $cond[] = "id in (SELECT product_id FROM product_display_member WHERE display_id={$display_id})";
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
            $cond[] = "id in ({$other_id})";
        }
        if($q){
            $qt     = addslashes($q);
            $cond[] = "(id in (SELECT product_id FROM product_color WHERE skc_id = '$qt') or kuanhao='$qt' or bianhao='$qt')";
            //$cond[] = "(id in (SELECT product_id FROM product_color WHERE skc_id like '%$qt%') or kuanhao like '%$qt%' or bianhao like '%$qt%')";
            //$cond[] = "bianhao = '{$qt}'";
        }

        if($show_limit == 0 || ($show_limit > 0 && ($p - 1) * $limit < $show_limit)){
            switch ($data->orderType) {
                case 'area' :
                    $area1 = $User->area1;
                    $list  = $OrderList->get_area_rank_list($area1);
                    $start = ($p - 1) * $limit;
                    $list  = array_slice($list, $start, $limit);
                    break;
                case 'zongdai'  :
                    if($User->mid){
                        $master_uid             = $User->mid;
                    }else{
                        $UserSlave              = new UserSlave;
                        $master_uid             = $UserSlave->get_master_uid($User->id);
                    }
                    $list  = $OrderList->get_master_rank_list($master_uid);
                    $start = ($p - 1) * $limit;
                    $list  = array_slice($list, $start, $limit);
                    break;
                default     :
                    $where      = implode(' AND ', $cond);
                    $CACHE_PARAMS   = $where . $fields . $_order . $p . $limit;
                    $CACHE_KEY  = "ProductList". md5($CACHE_PARAMS) . sizeof($CACHE_PARAMS);
                    $cache_option   = array("limit"=>$limit, "page"=>$p, "order"=>$_order, 'fields'=>$fields);
                    $cache          = new Cache(function() use ($Product, $where, $cache_option){
                        $list       = $Product->find($where, $cache_option);
                        return $list;
                    }, 10);
                    $list   = $cache->get($CACHE_KEY, array());
            }

            if(count($list)){
                $condition  = array();
                $options    = array();
                $options['key']     = "product_id";
                $options['fields_more'] = "o.product_id";
                $options['status']  = false;
                if($ordered != "off" && $tType != 'book'){
                    $condition['user_id']   = $User->id;
                    $order_user = $OrderList->getOrderAnalysisList($condition, $options);
                }
                $OrderListProduct       = new OrderListProduct;
                $ProductColor           = new ProductColor;
                $ProductSize            = new ProductSize;
                $Moq                    = new Moq;
                $OrderListAgent         = new OrderListAgent();
                
                foreach($list as &$row){
                    $product_id         = $row['id'];
                    $row['order_user']  = $order_user[$product_id];
                    $skc_list           = $ProductColor->get_distinct_skc_ids($product_id);
                    $row['skc_string']  = implode(",", $skc_list);
                    $row['skc_is_need']     = $ProductColor->is_need($product_id);
                    if($view == 'T'){
                        $row['order_rank']  = $OrderListProduct->get_rank($product_id);
                        if($User->type==9){
                            $row['order_num']   = $OrderListProduct->get_num($product_id);
                            if($user_designer_id==$row['designer_id']){
                                $row['is_designer'] = 1;
                            }
                        }
                        $row['agent_rank']  = $OrderListAgent->get_rank($zongdai_id, $product_id);
                        if($User->mid) {
                            $row['agent_num'] = $OrderListAgent->get_num($zongdai_id, $product_id);
                        }
                        $row['color_list']  = $ProductColor->get_color_list($product_id);
                    }
                }
            }
        }
        $result['list'] = $list;
        $result['kid']  = $kid;
        $result['view'] = $view;
        $result['num_order_show']   = $Company->num_order_show;
        $result['order_start_num']  = $Company->order_start_num;
        $result['order_start_pass']  = $Company->order_start_pass;
        $result['company']  = $Company->getData();
        $result['start']    = ($p - 1) * $limit;

        switch ($tType) {
            case 'book' :
                $template   = 'product/list.book.html';
                break;
            default     :
                $template   = 'product/list.html';
        }
        Flight::display($template, $result);
    }

    public static function list_ST($r){
        $data       = $r->query;
        $limit      = $data->limit ? $data->limit : 9;
        $p          = $data->p;
        $User       = new User;
        $Product    = new Product;
        $OrderList  = new OrderList;
        $ProductComment = new ProductComment;
        $keywords   = new Keywords;
        $Company    = new Company;

        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $medium_id      = $data->medium_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $season_id      = $data->season_id;
        $wave_id        = $data->wave_id;
        $fabric_id      = $data->fabric_id;
        $price_band_id  = $data->price_band_id;
        $theme_id       = $data->theme_id;
        $brand_id       = $data->brand_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $sxz_id         = $data->sxz_id;
        $edition_id     = $data->edition_id;
        $contour_id     = $data->contour_id;
        $designer_id    = $data->designer_id;
        $ordered        = $data->ordered;
        $group_id       = $data->group_id;
        $area1          = $data->area1;
        $area2          = $data->area2;
        $order          = $data->order;
        $myorder        = $data->myorder;
        $q              = $data->q;
        $view           = $data->view;
        $permission_brand = $User->permission_brand;
        $cond           = array();
        $fields         = "p.*, pc.color_id, pc.skc_id";
        $show_limit     = 0;
        $user_type      = $User->type;
        if($designer_id){
            $user_designer_id=$designer_id;
        }elseif($User->type == 9){
            $name = $User->name;
            $user_designer_id = $keywords->getKeywordId($name);
        }
        if(!$order){
            // $order  = 'bianhao asc';
            $order = "all num desc";
        }

        switch ($order) {
            case 'bianhao asc':
                $_order     = "p.bianhao asc";
                break;
            case 'bianhao desc':
                $_order     = "p.bianhao desc";
                break;
            case 'num asc':
                $fields     .= ",(SELECT SUM(o.num) FROM orderlist as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder asc, bianhao asc";
                break;
            case 'num desc':
                $fields     .= ",(SELECT SUM(o.num) FROM orderlist as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder desc, p.bianhao asc";
                break;
            case 'price asc':
                $fields     .= ",(SELECT SUM(o.num * p.price) FROM orderlist as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder asc, p.bianhao asc";
                break;
            case 'price desc':
                $fields     .= ",(SELECT SUM(o.num * p.price) FROM orderlist as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id AND o.user_id={$User->id}) as myorder";
                $_order     = "myorder desc, p.bianhao asc";
                break;
            case 'all num asc':
                if($area1){
                    if($area2){
                        $fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and u.area2={$area2} and o.product_id=p.id AND o.product_color_id=pc.color_id) as myorder";
                    } else {
                        $fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and o.product_id=p.id AND o.product_color_id=pc.color_id) as myorder";
                    }
                }   else {
                    $fields     .= ",(SELECT o.num FROM orderlistproductcolor as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id ) as myorder";
                }
                $_order     = "myorder asc, p.bianhao asc";
                break;
            case 'all num desc':
                if($area1){
                    if($area2){
                        $fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and u.area2={$area2} and o.product_id=p.id AND o.product_color_id=pc.color_id) as myorder";
                    } else {
                        $fields     .= ",(SELECT sum(o.num) FROM orderlist as o left join user as u on o.user_id=u.id WHERE  u.area1={$area1} and o.product_id=p.id AND o.product_color_id=pc.color_id) as myorder";
                    }
                }   else {
                    $fields     .= ",(SELECT o.num FROM orderlistproductcolor as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id ) as myorder";
                }
                $_order     = "myorder desc, p.bianhao asc";
                break;
            case 'all price desc':
                $fields     .= ",(SELECT SUM(o.num * p.price) FROM orderlist as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id ) as myorder";
                $_order     = "myorder desc, p.bianhao asc";
                break;
            case 'all price asc':
                $fields     .= ",(SELECT SUM(o.num * p.price) FROM orderlist as o WHERE o.product_id=p.id AND o.product_color_id=pc.color_id ) as myorder";
                $_order     = "myorder asc, p.bianhao asc";
                break;
            default : 1;
        }
        if($style_id)       $cond[]    = "p.style_id in ({$style_id})";
        if($category_id)    $cond[]    = "p.category_id in ({$category_id})";
        if($medium_id)      $cond[]    = "p.medium_id in ({$medium_id})";
        if($classes_id)     $cond[]    = "p.classes_id in ({$classes_id})";
        if($wave_id)        $cond[]    = "p.wave_id in ({$wave_id})";
        if($series_id)      $cond[]    = "p.series_id in ({$series_id})";
        if($season_id)      $cond[]    = "p.season_id in ({$season_id})";
        if($fabric_id)      $cond[]    = "p.fabric_id in ({$fabric_id})";
        if($price_band_id)  $cond[]    = "p.price_band_id in ({$price_band_id})";
        if($brand_id)       $cond[]    = "p.brand_id in ({$brand_id})";
        if($theme_id)       $cond[]    = "p.theme_id in ({$theme_id})";
        if($nannvzhuan_id)  $cond[]    = "p.nannvzhuan_id in ({$nannvzhuan_id})";
        if($sxz_id)         $cond[]    = "p.sxz_id in ({$sxz_id})";
        if($edition_id)     $cond[]    = "p.edition_id in ({$edition_id})";
        if($contour_id)     $cond[]    = "p.contour_id in ({$contour_id})";
        // if($designer_id)     $cond[]    = "p.designer_id in ({$designer_id})";
        if($permission_brand) $cond[]  = "p.brand_id not in ({$permission_brand})";
        // if($User->type==9) $cond[] = "designer='{$User->name}'";
        if($user_type == 3){
            $sql_ordered = "SELECT product_id FROM orderlistproduct where num>0 group by product_id";
        }else{
            $sql_ordered = "SELECT product_id FROM orderlist where user_id={$User->id} AND num>0 group by product_id";
        }
        if($ordered == "on"){
            $cond[] = "p.id in ($sql_ordered)";
        }elseif($ordered == "off"){
            $cond[] = "p.id not in ($sql_ordered)";
        }elseif($ordered == "unactive"){
            $cond[] = "p.status=0";
            if($myorder == "on"){
                $cond[] = "p.id in ($sql_ordered)";
            }
        }
        if($ordered != "unactive"){
            // $cond[] = "p.status=1";
            $cond[] = "p.status<>0";
            $cond[] = "pc.status<>0";
        }

        if($show_limit == 0 || ($show_limit > 0 && ($p - 1) * $limit < $show_limit)){
            $OrderList  = new OrderList;
            switch ($data->orderType) {
                case 'area' :
                    $area1 = $User->area1;
                    $list  = $OrderList->get_area_color_rank_list($area1);
                    $start = ($p - 1) * $limit;
                    $list  = array_slice($list, $start, $limit);
                    break;
                case 'zongdai'  :
                    if($User->mid){
                        $master_uid             = $User->mid;
                    }else{
                        $UserSlave              = new UserSlave;
                        $master_uid             = $UserSlave->get_master_uid($User->id);
                    }
                    $list  = $OrderList->get_master_color_rank_list($master_uid);
                    $start = ($p - 1) * $limit;
                    $list  = array_slice($list, $start, $limit);
                    break;
                default     :
                    $options['tablename']   = "product as p left join product_color as pc on p.id=pc.product_id";
                    $options['fields']      = $fields;
                    $options['page']        = $p;
                    $options['limit']       = $limit;
                    $options['order']       = $_order;
                    //$options['db_debug']    = true;
                    $where      = implode(' AND ', $cond);
                    $list       = $Product->find($where, $options);
            }
            //print_r($list);
            $pcolor = new ProductColor();
            foreach($list as &$row){
                $product_id         = $row['id'];
                if($row['product_color_id']){
                    $row['color_id']    = $row['product_color_id'];
                }
                $product_color_id   = $row['color_id'];
                if($user_type == 3){
                    if($User->username!='0'){
                        $row['order_user']  = $OrderList->get_order_info(array('product_id'=>$product_id, 'color_id'=>$product_color_id,'ad_id'=>$User->id));
                    }else{
                        $row['order_user']  = $OrderList->get_order_info(array('product_id'=>$product_id, 'color_id'=>$product_color_id));
                    }
                }elseif($user_type == 9){
                    $row['order_num']  = $OrderList->get_order_info(array('product_id'=>$product_id, 'color_id'=>$product_color_id));    
                    if($user_designer_id==$row['designer_id']){
                        $row['is_designer']= 1;
                    }
                }else{
                    $order_user         = $OrderList->get_user_orderlist_info($User->id, $product_id, $product_color_id);
                    $row['order_user']  = $order_user[0];
                }
                $row['order_rank']  = $OrderList->get_product_color_rank($product_id, $product_color_id);
                $row['other_color'] = array();
                $otherColor = $pcolor->find(' product_id = "'.$product_id.'" AND color_id != "'.$product_color_id.'" ',array('fields'=>'color_id,skc_id'));
                foreach($otherColor as $ocv){
                    $other_order_rank = $OrderList->get_product_color_rank($product_id, $ocv['color_id']);
                    if($user_type == 3){
                        if($User->username!='0'){
                            $other_order_user  = $OrderList->get_order_info(array('product_id'=>$product_id, 'color_id'=>$ocv['color_id'],'ad_id'=>$User->id));
                        }else{
                            $other_order_user  = $OrderList->get_order_info(array('product_id'=>$product_id, 'color_id'=>$ocv['color_id']));
                        }

                    }else{
                        $order_user_other         = $OrderList->get_user_orderlist_info($User->id, $product_id, $ocv['color_id']);
                        $other_order_user  = $order_user_other[0];
                    }
                    $row['other_color'][] = array('order_rank'=>$other_order_rank,'color_id'=>$ocv['color_id'],'order_user'=>$other_order_user);
                }
                // if($area1){
                //     $row['area_rank']   = $OrderList->get_area_color_rank($product_id, $product_color_id, $area1);
                // }
                // if($master_uid){
                //     $row['master_rank'] = $OrderList->get_master_color_rank($product_id, $product_color_id, $master_uid);
                // }
            }
        }

        $result['list'] = $list;
        $result['kid']  = $kid;
        $result['view'] = $view;
        $result['num_order_show']   = $Company->num_order_show;
        $result['start']    = ($p - 1) * $limit;

        $template = $user_type == 3 ? 'product/list3.html' : 'product/list.html';
        Flight::display($template, $result);
    }

    public static function Action_grouplist($r,$type=1){
        Flight::validateUserHasLogin();
        $User = new User;
        if(!$User->permission_brand){
            $ProductGroup   = new ProductGroup;
            $data           = $r->query;
            $p              = $data->p      ? $data->p      : 1;
            $limit          = $data->limit  ? $data->limit  : 9;
            $dp_type        = $data->dp_type;
            $product_id     = $data->product_id;
            $condition      = array();
            $options        = array();
            $options['page']    = $p;
            $options['limit']   = $limit;
            //$options['db_debug']=true;
            if($product_id){
                $condition[]    = "id in (SELECT group_id FROM product_group_member WHERE product_id={$product_id})";
            }
            if($dp_type){
                $condition[] = "dp_type={$dp_type}";
            }
            $where          = implode(' AND ', $condition);
            $list           = $ProductGroup->find($where, $options);
            
            $result['list'] = $list;
            $result['type_id'] = $data->type_id;
        }
        $tail=($type==1?'':'_ad');
        Flight::display('product/grouplist'.$tail.'.html', $result);
    }

    public static function Action_groupdetaillist($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $group_id   = $data->group_id;
        $p          = $data->p;
        $limit      = $data->limit      ? $data->limit  : 50;
        $ProductGroupMember         = new ProductGroupMember;
        $ProductColor               = new ProductColor;
        $ProductSize                = new ProductSize;
        $UserProduct                = new UserProduct;
        $Company                    = new Company;
        $User                       = new User;
        $Moq                        = new Moq;
        $user_id                    = $User->id;

        $list   = $ProductGroupMember->find("group_id={$group_id}", array("limit"=>$limit, "page"=>$p,'group'=>'product_id'));
        //$list   = $ProductGroupMember->find("group_id={$group_id}", array("limit"=>$limit, "page"=>$p));
        $list   = Flight::listFetch($list, 'product', 'product_id', 'id');
        foreach($list as &$row){
            $product_id         = $row['product_id'];
            if($r->query->type=='new'){
                //$row['color_list']  = $ProductGroupMember->get_color_list($group_id,$product_id);
                $row['color_list']  = $ProductColor->get_color_list($product_id);
                $row['color_list_group']  = $ProductGroupMember->get_group_color_list($group_id,$product_id);
               // print_r($row['color_list']);
             //  print_r($row['color_list_group']);
            }else{
                $row['color_list']  = $ProductColor->get_color_list($product_id);
            }

            $row['size_list']   = $ProductSize->get_size_list($product_id);
            $row['storeinfo']   = $UserProduct->get_store_info($user_id, $product_id);
            $row['has_permission_brand']    = $User->has_permission_brand($row['product']['brand_id']);
            $row['moq']         = $Moq->get_user_product_moq($User->user_level, $product_id);
        }

        $result['list'] = $list;
        //$result['group_id'] = $group_id;
        $result['company']  = $Company->getData();
        if($r->query->type=='new'){
            Flight::display('product/groupdetaillistnew.html', $result);
        }else{
            Flight::display('product/groupdetaillist.html', $result);
        }

    }

    public static function Action_displaylist($r,$type=1){
        Flight::validateUserHasLogin();

        $User = new User;
        if(!$User->permission_brand){
            $ProductDisplay = new ProductDisplay;
            $data           = $r->query;
            $p              = $data->p      ? $data->p      : 1;
            $limit          = $data->limit  ? $data->limit  : 9;
            $product_id     = $data->product_id;
            $options        = array();
            $options['page']    = $p;
            $options['limit']   = $limit;
    	    $options['order']	= 'bianhao asc';
            $condition      = array();
            if($product_id){
                $condition[]    = "id in (SELECT display_id FROM product_display_member WHERE product_id={$product_id})";
            }
            $where          = implode(" AND ", $condition);
            $list           = $ProductDisplay->find($where, $options);
            $result['list'] = $list;
        }

        $tail=($type==1?'':($type==2?'_ad':'_ad_new'));
        Flight::display('product/displaylist'.$tail.'.html', $result);
    }

    public static function Action_displaydetaillist($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $display_id = $data->display_id;
        $p          = $data->p;
        $limit      = $data->limit      ? $data->limit  : 50;
        $ProductDisplayMember       = new ProductDisplayMember;
        $ProductDisplayMemberColor  = new ProductDisplayMemberColor;
        $ProductSize                = new ProductSize;
        $UserProduct                = new UserProduct;
        $Company                    = new Company;
        $User                       = new User;
        $Moq                        = new Moq;
        $user_id                    = $User->id;

        $list   = $ProductDisplayMember->find("display_id={$display_id}", array("limit"=>$limit, "page"=>$p, "order"=>"rank desc, id asc"));
        $list   = Flight::listFetch($list, 'product', 'product_id', 'id');
        foreach($list as &$row){
            $product_id         = $row['product_id'];
            $row['color_list']  = $ProductDisplayMemberColor->get_color_list($display_id, $product_id);
            $row['size_list']   = $ProductSize->get_size_list($product_id);
            $row['storeinfo']   = $UserProduct->get_store_info($user_id, $product_id);
            $row['has_permission_brand']    = $User->has_permission_brand($row['product']['brand_id']);
            $row['moq']         = $Moq->get_user_product_moq($User->user_level, $product_id);
        }

        $result['list'] = $list;
        $result['company']  = $Company->getData();

        Flight::display('product/displaydetaillist.html', $result);
    }

    public static function Action_list2($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $User       = new User;
        $Product    = new Product;
        $OrderList  = new OrderList;
        $ProductComment     = new ProductComment;
        $Keywords   = new Keywords;
        $cond       = array();
        $options    = array();
        $options['limit']   = 9;
        $options['page']    = $data->p;
        $p              = $data->p;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $medium_id      = $data->medium_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $season_id      = $data->season_id;
        $wave_id        = $data->wave_id;
        $fabric_id      = $data->fabric_id;
        $price_band_id  = $data->price_band_id;
        $theme_id       = $data->theme_id;
        $brand_id       = $data->brand_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $sxz_id         = $data->sxz_id;
        $edition_id     = $data->edition_id;
        $contour_id     = $data->contour_id;
        $designer_id    = $data->designer_id;
        $ordered        = $data->ordered;
        $order          = $data->order;
        $group_id       = $data->group_id;
        $group_product_id   = $data->group_product_id;
        $q              = $data->q;
        $fliter_uid        = $data->fliter_uid;
        $fields         = "*";
        //$kid    = $Keywords->getKeywordId(STRING_BIDINGKUAN);
        switch ($order) {
            case 'bianhao asc':
                $_order     = "bianhao asc";
                break;
            case 'bianhao desc':
                $_order     = "bianhao desc";
                break;
            case 'num asc':
                $fields     .= ",(SELECT SUM(o.num) FROM orderlist as o left join user_slave as us on o.user_id=us.user_slave_id WHERE o.product_id=product.id AND us.user_id={$User->id}) as myorder";
                $_order     = "myorder asc, bianhao asc";
                break;
            case 'num desc':
                $fields     .= ",(SELECT SUM(o.num) FROM orderlist as o left join user_slave as us on o.user_id=us.user_slave_id WHERE o.product_id=product.id AND us.user_id={$User->id}) as myorder";
                $_order     = "myorder desc, bianhao asc";
                break;
            case 'price asc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlist as o left join user_slave as us on o.user_id=us.user_slave_id WHERE o.product_id=product.id AND us.user_id={$User->id}) as myorder";
                $_order     = "myorder asc, bianhao asc";
                break;
            case 'price desc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlist as o left join user_slave as us on o.user_id=us.user_slave_id WHERE o.product_id=product.id AND us.user_id={$User->id}) as myorder";
                $_order     = "myorder desc, bianhao asc";
                break;
            case 'all num asc':
                $fields     .= ",(SELECT o.num FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder asc, bianhao asc";
                break;
            case 'all num desc':
                $fields     .= ",(SELECT o.num FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder desc, bianhao asc";
                break;
            case 'all price desc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder desc, bianhao asc";
                break;
            case 'all price asc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder asc, bianhao asc";
                break;
            default :
                $_order     = "id asc";
        }
        if($style_id)       $cond[]    = "style_id in ({$style_id})";
        if($category_id)    $cond[]    = "category_id in ({$category_id})";
        if($medium_id)      $cond[]    = "medium_id in ({$medium_id})";
        if($classes_id)     $cond[]    = "classes_id in ({$classes_id})";
        if($wave_id)        $cond[]    = "wave_id in ({$wave_id})";
        if($series_id)      $cond[]    = "series_id in ({$series_id})";
        if($season_id)      $cond[]    = "season_id in ({$season_id})";
        if($fabric_id)      $cond[]    = "fabric_id in ({$fabric_id})";
        if($price_band_id)  $cond[]    = "price_band_id in ({$price_band_id})";
        if($brand_id)       $cond[]    = "brand_id in ({$brand_id})";
        if($theme_id)       $cond[]    = "theme_id in ({$theme_id})";
        if($nannvzhuan_id)  $cond[]    = "nannvzhuan_id in ({$nannvzhuan_id})";
        if($sxz_id)         $cond[]    = "sxz_id in ({$sxz_id})";
        if($edition_id)     $cond[]    = "edition_id in ({$edition_id})";
        if($contour_id)     $cond[]    = "contour_id in ({$contour_id})";
        if($designer_id)     $cond[]    = "designer_id in ({$designer_id})";
        if($ordered){
            $select_sql     = "SELECT o.product_id FROM orderlist as o left join user_slave as us on o.user_id=us.user_slave_id where us.user_id={$User->id} group by o.product_id";
            if($ordered == "on"){
                $cond[] = "id in ({$select_sql})";
            }elseif($ordered == "off"){
                $cond[] = "id not in ({$select_sql})";
                if(!$_order){
                    //if(!$kid)   $kid = 0;
                    //$fields = "*,IF(style_id={$kid}, 1, 0) as krank";
                    $fields = "*,is_need as krank";
                    $_order  = "status desc,krank desc,bianhao asc";
                }
            }elseif($ordered == "unactive"){
                $cond[] = "status=0";
                $cond[] = "id in ({$select_sql})";
            }
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
            $cond[] = "(id in (SELECT product_id FROM product_color WHERE skc_id = '$qt') or kuanhao='$qt' or bianhao='$qt')";
            //$cond[] = "bianhao=$q";
        }

        $where      = implode(" AND ", $cond);
        if(!$where) $where = "1";
        $options['fields']  = $fields;
        $options['order']   = $_order;
        // $options['db_debug']    = true;
        $list       = $Product->find($where, $options);

        if(count($list)){
            $condition  = array();
            $options    = array();
            $options['key']     = "product_id";
            $options['fields_more'] = "o.product_id";
            $options['status']  = false;
            //$order_all  = $OrderList->getOrderAnalysisList($condition, $options);
            if($ordered != "off"){
                $condition['user_id']   = $User->id;
                if($fliter_uid) $condition['fliter_uid'] = $fliter_uid;
                $order_user = $OrderList->getDealer2OrderList($condition, $options);
            }else{
                $order_user = array();
            }


            foreach($list as &$row){
                $product_id         = $row['id'];
                //$row['order_all']   = $order_all[$product_id];
                $row['order_user']  = $order_user[$product_id];
                if($row['status']){
                    $row['style']       = $Keywords->getName_File($row['style_id']);
                    $row['scoreinfo']   = $ProductComment->getAvgScore($product_id);
                }
            }
        }


        $result['list']     = $list;

        $result['start']    = ($p - 1) * $limit;
        //$result['kid']      = $kid;

        Flight::display("product/list2.html", $result);
    }


    public static function Action_list3($r){
        Flight::validateUserHasLogin();

        $data       = $r->query;
        $User       = new User;
        $Product    = new Product;
        $OrderList  = new OrderList;
        $ProductComment     = new ProductComment;
        $Keywords   = new Keywords;
        $cond       = array();
        $options    = array();
        $options['page']    = $data->p;
        $p              = $data->p;
        $limit          = $data->limit  ? $data->limit  : 6;
        $style_id       = $data->style_id;
        $category_id    = $data->category_id;
        $medium_id      = $data->medium_id;
        $classes_id     = $data->classes_id;
        $series_id      = $data->series_id;
        $season_id      = $data->season_id;
        $wave_id        = $data->wave_id;
        $fabric_id      = $data->fabric_id;
        $price_band_id  = $data->price_band_id;
        $theme_id       = $data->theme_id;
        $brand_id       = $data->brand_id;
        $nannvzhuan_id  = $data->nannvzhuan_id;
        $sxz_id         = $data->sxz_id;
        $edition_id     = $data->edition_id;
        $contour_id     = $data->contour_id;
        $designer_id    = $data->designer_id;
        $ordered        = $data->ordered;
        $order          = $data->order;
        $group_id       = $data->group_id;
        $group_product_id   = $data->group_product_id;
        $view           = $data->view;
        $q              = $data->q;
        if($view == "ST"){
            STATIC::list_ST($r);
            exit;
        }
        $fields         = "*";
        //$kid    = $Keywords->getKeywordId(STRING_BIDINGKUAN);
        switch ($order) {
            case 'bianhao asc':
                $_order     = "bianhao asc";
                break;
            case 'bianhao desc':
                $_order     = "bianhao desc";
                break;
            case 'all num asc':
                $fields     .= ",(SELECT o.num FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder asc, bianhao asc";
                break;
            case 'all num desc':
                $fields     .= ",(SELECT o.num FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder desc, bianhao asc";
                break;
            case 'all price asc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder asc, bianhao asc";
                break;
            case 'all price desc':
                $fields     .= ",(SELECT SUM(o.num * product.price) FROM orderlistproduct as o WHERE o.product_id=product.id) as myorder";
                $_order     = "myorder desc, bianhao asc";
                break;
            default :
                $_order     = "id asc";
        }

        switch ($view) {
            case 'T'    : $limit    = 10; break;
            case 'S'    : $limit    = 49; break;
            default     : 1;
        }
        $options['limit']   = $limit;
        if($style_id)       $cond[]    = "style_id in ({$style_id})";
        if($category_id)    $cond[]    = "category_id in ({$category_id})";
        if($medium_id)      $cond[]    = "medium_id in ({$medium_id})";
        if($classes_id)     $cond[]    = "classes_id in ({$classes_id})";
        if($wave_id)        $cond[]    = "wave_id in ({$wave_id})";
        if($series_id)      $cond[]    = "series_id in ({$series_id})";
        if($season_id)      $cond[]    = "season_id in ({$season_id})";
        if($fabric_id)      $cond[]    = "fabric_id in ({$fabric_id})";
        if($price_band_id)  $cond[]    = "price_band_id in ({$price_band_id})";
        if($brand_id)       $cond[]    = "brand_id in ({$brand_id})";
        if($theme_id)       $cond[]    = "theme_id in ({$theme_id})";
        if($nannvzhuan_id)  $cond[]    = "nannvzhuan_id in ({$nannvzhuan_id})";
        if($sxz_id)         $cond[]    = "sxz_id in ({$sxz_id})";
        if($edition_id)     $cond[]    = "edition_id in ({$edition_id})";
        if($contour_id)     $cond[]    = "contour_id in ({$contour_id})";
        if($designer_id)     $cond[]    = "designer_id in ({$designer_id})";
        if($q){
            $qt     = addslashes($q);
            $cond[] = "(id in (SELECT product_id FROM product_color WHERE skc_id = '$qt') or kuanhao='$qt' or bianhao='$qt')";
            //$cond[] = "(id in (SELECT product_id FROM product_color WHERE skc_id like '%$qt%') or kuanhao like '%$qt%' or bianhao like '%$qt%')";
            //$cond[] = "bianhao = '{$qt}'";
        }
        if($ordered){
            $select_cond    = array();
            if($User->area1)    $select_cond[]  = "u.area1={$User->area1}";
            if($User->area2)    $select_cond[]  = "u.area2={$User->area2}";
            $select_where   = implode(" AND ", $select_cond);
            if(!$select_where)  $select_where = "1";
            $select_sql     = "SELECT product_id FROM orderlistproduct where num>0";
            if($ordered == "on"){
                $cond[] = "id in ({$select_sql})";
            }elseif($ordered == "off"){
                $cond[] = "id not in ({$select_sql})";
                if(!$_order){
                    //if(!$kid)   $kid = 0;
                    //$fields = "*,IF(style_id={$kid}, 1, 0) as krank";
                    $fields = "*,is_need as krank";
                    $_order  = "status desc,krank desc,bianhao asc";
                }
            }elseif($ordered == "unactive"){
                $cond[] = "status=0";
                // $cond[] = "id in ({$select_sql})";
            }
        }else{
            // $cond[] = "status=1";
            $cond[] = "status<>0";
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

        $where      = implode(" AND ", $cond);
        if(!$where) $where = "1";
        $options['fields']  = $fields;
        $options['order']   = $_order;
        // $options['db_debug']    = true;
        $list       = $Product->find($where, $options);

        if(count($list)){
            $condition  = array();
            $options    = array();
            $options['key']     = "product_id";
            $options['fields_more'] = "o.product_id";
            $options['status']  = false;
            // $order_all  = $OrderList->getOrderAnalysisList($condition, $options);
            $OrderListProduct   = new OrderListProduct;
            $order_all  = $OrderListProduct->find("", array("key"=>"product_id", "limit"=>10000));
            if($ordered != "off"){
                $condition['ad_id']   = $User->id;
                //$condition['ad_area2']   = $User->area2;
                if($User->username!='0'){
                    $order_user = $OrderList->getOrderAnalysisList($condition, $options);
                }else{
                    $order_user     = $order_all;
                }

                //$order_user     = $order_all;
            }else{
                $order_user = array();
            }

            $OrderListProduct   = new OrderListProduct;
            $ProductColor       = new ProductColor;
            foreach($list as &$row){
                $product_id         = $row['id'];
                $row['order_all']   = $order_all[$product_id];
                $row['order_user']  = $order_user[$product_id];
                $row['order_rank']  = $OrderListProduct->get_rank($product_id);
                if($row['status']){
                    $row['style']       = $Keywords->getName_File($row['style_id']);
                    // $row['scoreinfo']   = $ProductComment->getAvgScore($product_id);
                }
                $color_list = $ProductColor->get_color_list($product_id);               
                //$skc_list   = ProductsAttributeFactory::fetch($color_list, 'color', "color_id", "products_color");
                //usort($skc_list, function($a, $b){
                    //return $a['products_color']['rank'] > $b['products_color']['rank'] ? 1 : -1;
                //});
                $row['color_list']  = $color_list;
                //$row['skc_list']   = $skc_list;
            }
        }


        $result['start']    = ($p - 1) * $limit;
        $result['list']     = $list;
        //$result['kid']      = $kid;
        $result['view']     = $view;

        Flight::display("product/list3.html", $result);
    }

    public static function Action_set_user_product($r){
        Flight::validateUserHasLogin();

        $User           = new User;
        $UserProduct    = new UserProduct;
        $data           = $r->data;
        $product_id     = $data->product_id;
        $status         = $data->status;
        $rateval        = $data->rateval;
        if($product_id){
            if($status){
                $UserProduct->create_product($User->id, $product_id, $rateval);
            }else{
                $UserProduct->remove_product($User->id, $product_id);
            }
        }

        Flight::json(array('valid'=>true));
    }

    public static function Action_storelist($r){
        Flight::validateUserHasLogin();

        $User           = new User;
        $UserProduct    = new UserProduct;
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $Company        = new Company;
        $Moq            = new Moq;
        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 6;
        $options            = array();
        $options['page']    = $p;
        $options['limit']   = $limit;
        $keys   = array('style_id', 'wave_id', 'category_id', 'classes_id', 'series_id', 'price_band_id', 'ordered', 'rateval');
        foreach($keys as $key){
            $options[$key]  = $data->$key;
        }

        $list           = $UserProduct->get_list($User->id, $options);
        foreach($list as &$row){
            $row['size_list']   = $ProductSize->get_size_list($row['id']);
            $row['color_list']  = $ProductColor->get_color_list($row['id']);
            $row['has_permission_brand']    = $User->has_permission_brand($row['brand_id']);
            $row['moq']         = $Moq->get_user_product_moq($User->user_level, $row['id']);
        }
        $result['list'] = $list;
        $result['company'] = $Company->getData();

        Flight::display("product/storelist.html", $result);
    }

    public static function Action_get_product_id_by_bianhao($r){
        $Product    = new Product;
        $data       = $r->query;
        $bianhao    = $data->bianhao;
        $f          = $data->f;
        if(is_numeric($bianhao)){
            $options    = array();
            $condition  = array();
            if($f == "up"){
                $condition[]    = "id<{$bianhao}";
                $options['order']   = "id DESC";
                $_message       = "已经是第一款";
            }elseif($f == "down"){
                $condition[]    = "id>{$bianhao}";
                $options['order']   = "id ASC";
                $_message       = "已经是最后一款";
            }else{
                $condition[]    = "id={$bianhao}";
                $_message       = "未找到该款";
            }
            // $options['db_debug']    = true;
            $where      = implode(' AND ', $condition);
            $product    = $Product->findone($where, $options);
            if(!$product['id']){
                $message = $_message;
            }
        }
        $result['message']  = $message;
        $result['product']  = $product;

        Flight::json($result);
    }

    public static function Action_fabric($r){
       $data        = $r->query;
       $complete    = $data->complete;
       $fabric_id   = $data->fabric_id;
       $Product     = new Product;
       $params['complete']  = $complete;
       $params['fabric_id'] = $fabric_id;
       $list    = $Product->getFabricOrderList($params);
       $result['list']  = $list;
       Flight::display('product/fabric.html', $result);
    }

    public static function Action_set_product_color_status ($r) {
        Flight::validateUserHasLogin();

        $data       = $r->data;
        $product_id         = $data->product_id;
        $product_color_id   = $data->product_color_id;
        $status             = $data->status;
        if($product_id && $product_color_id) {
            $ProductColor   = new ProductColor;
            $ProductColor->set_status($product_id, $product_color_id, $status);
            $result['error']    = 0;
        }else{
            $result['error']    = 1;
        }
        Flight::json($result);
    }

    public static function Action_stocktable ($r) {
        Flight::validateUserHasLogin();

        $data           = $r->query;
        $product_id     = $data->product_id;
        $ProductStock   = new ProductStock;
        $stock_list     = $ProductStock->get_product_stock_list($product_id);
        $ProductStockTable  = new ProductStockTable($product_id, $stock_list);
        $result         = $ProductStockTable->make();

        Flight::display("product/stocktable.html", $result);
    }

    
    /*产品建议*/
    public static function Action_set_comment($r){
        $data				= $r->data;
        $product_id 		= $data->product_id;
        $product_comment	= $data->product_comment;
        $ProductComment		= new ProductComment;
        $User       		= new User;
        $flag				= false;
        if($product_id && $product_comment){
            $comment_info = $ProductComment->get_product_comment($User->id,$product_id);
            if(empty($comment_info)){
                $comment_id = $ProductComment->create_comment($User->id,array('product_id'=>$product_id,'content'=>$product_comment));
            } else {
                $where = "user_id={$User->id} AND product_id={$product_id}";
                $ProductComment->update(array("content"=>$product_comment), $where);
            }
    
            $flag = true;
        }
    
        Flight::json(array('flag'=>$flag));
    }
    
    public static function Action_search($r){
        Flight::validateUserHasLogin();
        $data = $r->query;
        $q = trim($data->q);
        $p = $data->p;
        
        if($q!==''){
            $limit = 12;
            $option = array();
            $product = new Product();
            $option['tablename'] = ' product p left join product_color pc on p.id=pc.product_id ';
            $option['fields'] = 'p.id,p.bianhao,p.kuanhao,pc.skc_id,p.defaultimage,p.name';
            $option['page'] = $p;
            $option['limit'] = $limit ;
            //$option['order'] = 'p.id' ;
            $option['count'] = true ;
            $User = new User();
            //$option['db_debug'] = true ;
            $res = $product->find(' p.status <> 0 AND pc.status = 1 and ( (p.bianhao="'.$q.'") or (pc.skc_id="'.$q.'") or (p.kuanhao like "'.$q.'%" ) ) ',$option);
            foreach($res as &$val){
                if($val['skc_id']==$q){
                    $val['skc_id'] = '<span class="getResult">'.$val['skc_id'].'</span>';
                }
                if($val['bianhao']==$q){
                    $val['bianhao'] = '<span class="getResult">'.$val['bianhao'].'</span>';
                }
                $val['kuanhao'] = preg_replace('/(^'.$q.')/i', '<span class="getResult">$1</span>', $val['kuanhao']);
                if($User->type==1){
                    $val['link'] = '/dealer1/detail/'.$val['id'];
                }elseif($User->type==2){
                    $val['link'] = '/dealer2/detail/'.$val['id'];
                }else{
                    $val['link'] = '/ad/detail/'.$val['id'];
                }             
            }
            
            $total      = $product->get_count_total();
            //echo $total;
            //$data->url = '/product/search';
            $result['pagelist'] = Pager::build(Pager::STYLE_GET, $data, $total, $limit);
            $result['list'] = $res;
            Flight::display("product/search.html", $result);
        }
    }

    public static function Action_search_get_id($r){
        $data   =   $r->query;
        $q = trim($data->q);
        $Product = new Product;
        $option['fields'] = "id";
        $where  = "kuanhao='{$q}' or bianhao='{$q}' or (id in (select product_id from product_color where skc_id='{$q}'))";
        $info   = $Product->findone($where,$option);
        if($info){
            $result['product_id'] = $info['id'];
            $ProductColor = new ProductColor;
            $pc = $ProductColor->findone("product_id={$info['id']} AND skc_id='{$q}'");
            if($pc['color_id']){
                $result['color_id'] = $pc['color_id'];
            }
        }else{
            $result['message'] = "未找到该款";
        }
        Flight::json($result);
    }

    public static function Action_get_pcimage($r){
        $data = $r->query;
        $product_id = $data->product_id;
        $product_color_id   = $data->color_id;
        $ProductColor = new ProductColor;
        $pc = $ProductColor->findone("product_id={$product_id} and color_id={$product_color_id} and status<>0");
        if($pc && $product_id && $product_color_id) {
            $pcinfo     = ProductImage::getProductColorImage($product_id, $product_color_id);
            if($pcinfo && $pcinfo['image']) {
                $result['image'] = $pcinfo['image'];
                $result['valid'] = true;
            }
        }
        if(!$result) $result['valid'] = false;
        Flight::json($result);
    }
}
