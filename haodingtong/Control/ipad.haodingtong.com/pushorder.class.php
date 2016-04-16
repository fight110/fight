<?php

class Control_pushorder {
    public static function _beforeCall($r, $id=0){
        Flight::validateUserHasLogin();
    }

    public static function Action_group_order($r){

        $result         = FrontSetting::build();
        $Company        = new Company;
        $group_order    = $Company->group_order;

        if($group_order){
            $show_group_id      = $Company->show_group_id;
            $ProductGroup       = new ProductGroup($show_group_id);
            $result['group']    = $ProductGroup->getAttribute();
            $result['show_group_id'] = $show_group_id;
        }else{
            $data           = $r->query;
            $group_id       = $data->group_id;
            $ProductGroup   = new ProductGroup($group_id);
            $dp_type_list   = $ProductGroup->find("1",array("limit"=>100,"fields"=>"dp_type","group"=>"dp_type"));
            $dp_type2_list  = $ProductGroup->find("1",array("limit"=>100,"fields"=>"dp_type2","group"=>"dp_type2"));
            $result['group_info']   = $ProductGroup->getAttribute();
            $result['dp_type_list'] = $dp_type_list;
            $result['dp_type2_list']= $dp_type2_list;
        }

        $result['control']  = "group_order";
        Flight::display('pushorder/group_order.html', $result);
    }
    public static function Action_group_order_list($r){
        $data           = $r->query;
        $dp_type        = $data->dp_type;
        $dp_type2       = $data->dp_type2;
        $group_id       = $data->group_id;
        $gid            = $data->gid;
        $change         = $data->change;
        $ProductGroup   = new ProductGroup();
        $Company        = new Company;
        $pushwidth      = $Company->pushwidth;
        $pushheight     = $Company->pushheight;
        if($pushheight && $pushwidth){
            $result['width'] = sprintf("%d",170 * $pushwidth / $pushheight); //根据宽高比计算图片宽度
        }
        if($gid){
            $group_id   = $gid;
        }elseif($group_id&&(!$change)){
            $info       = $ProductGroup->findone("id={$group_id}");
            if($info){
                $dp_type    = $info['dp_type'];
                $dp_type2   = $info['dp_type2'];
            }
        }
        $condition      = array();
        if($dp_type)    
            $condition[]= "dp_type={$dp_type}";
        if($dp_type2)   
            $condition[]= "dp_type2={$dp_type2}";
        $where          = implode(" AND ", $condition);
        $group_list     =   $ProductGroup->find($where,array("limit"=>30,"key"=>"id"));

        $result['group_list']   = $group_list;
        $tmp                    = reset($group_list);
        $result['currentGid']   = $group_id ? $group_id : $tmp['id'];
        Flight::display('pushorder/group_order_list.html', $result);
    }

    public static function Action_group_order_info($r){
        $data = $r->query;
        $group_id   = $data->group_id;
        if($group_id){
            $User               = new User;
            $Product            = new Product;
            $ProductColor       = new ProductColor;
            $ProductGroup       = new ProductGroup($group_id);
            $ProductGroupMember = new ProductGroupMember;
            $OrderList          = new OrderList;
            $Company            = new Company;
            $list               = $ProductGroupMember->get_member_list($group_id);
            $options['limit']   = 100;
            $options['key']     = "product_color_id";
            $options['fields']  = "product_id,product_color_id,sum(num) as num,sum(amount) as amount";
            $options['group']   = "product_id,product_color_id";
            //$options['db_debug']=true;
            $user_id            = $User->id;
            $total = array();
            $string_num = array();
            $notorder = 0;
            foreach($list as &$row) {
                $product_id     = $row['product_id'];
                $product        = $Product->findone("id={$product_id}");
                $row['product'] = $product;
                $color_list     = $ProductColor->get_color_list($product_id);
                $where          = "user_id={$user_id} AND product_id={$product_id} AND num>0";
                $orderinfo      = $OrderList->find($where, $options);
                foreach($color_list as &$color) {
                    $product_color_id   = $color['color_id'];
                    $num                = $orderinfo[$product_color_id]['num'];
                    $color['num']       = $num;
                }
                if($Company->user_guide){
                    $UserGuide              = new UserGuide;
                    foreach($color_list as &$val) {
                        $val['user_guide']  = $UserGuide->get_guide_num($user_id, $product_id,$val['color_id']);
                    }
                }
                $row['color_list']  =  $color_list;
                $row['num']         =  $orderinfo[$row['color_id']]['num'];
                $total['num']       += $orderinfo[$row['color_id']]['num'];
                $total['amount']    += $orderinfo[$row['color_id']]['amount'];
                $string_num[]       =  $orderinfo[$row['color_id']]['num'] ? $orderinfo[$row['color_id']]['num'] : 0;
                if(!$orderinfo[$row['color_id']]['num']) $notorder = 1;
            }
            $result['string_num']           = implode(" : ", $string_num);
            $result['notorder']             = $notorder;
            $result['group_info']           = $ProductGroup->getAttribute();
            $result['total']                = $total;
            $result['list']                 = $list;
            $result['company']['user_guide']= $Company->user_guide;

            $ProductDisplay         = new ProductDisplay;
            $display                = $ProductDisplay->findone("pd_type2={$result['group_info']['dp_type2']}",array("fields"=>"id"));
            $result['display_id']   = $display['id'];
        }

        Flight::display('pushorder/group_order_info.html', $result);
    }
    public static function Action_get_company_group_id($r){
        $data               = $r->query;
        $current_group_id   = $data->current_group_id;
        $Company            = new Company;
        $group_id           = $Company->show_group_id;
        $result['group_id'] = $group_id;
        if($group_id != $current_group_id){
            $ProductGroup   = new ProductGroup($group_id);
            $result['group']= $ProductGroup->getAttribute();
        }
        Flight::json($result);
    }

    public static function Action_search_group($r){
        $q =   addslashes($r->query->q);

        $ProductGroupMember     = new ProductGroupMember;
        $options['tablename']   = "product_group_member as pgm 
                                    left join product as p on pgm.product_id=p.id 
                                    left join product_group as pg on pg.id=pgm.group_id";
        $options['fields']      = "pg.*";
        
        $where  = "p.kuanhao={$q} or p.id in (select product_id from product_color where skc_id={$q})";
        $info   = $ProductGroupMember->findone($where,$options);
        
        if($info){
            $result['valid'] = true;
            $result['group'] = $info;
        }else{
            $result['message'] = "未找到{$q}所在搭配";
        }
        Flight::json($result);
    }

    public static function Action_display_order($r){

        $result     = FrontSetting::build();

        $data       = $r->query;
        $pd_type    = $data->pd_type;
        $pd_type2   = $data->pd_type2;
        $ProductDisplay = new ProductDisplay();

        $pd_type_list   = $ProductDisplay->find("1",array("limit"=>100,"fields"=>"pd_type","group"=>"pd_type"));
        $pd_type2_list  = $ProductDisplay->find("1",array("limit"=>100,"fields"=>"pd_type2","group"=>"pd_type2"));

        $result['pd_type']      = $pd_type;
        $result['pd_type2']     = $pd_type2;
        $result['pd_type_list'] = $pd_type_list;
        $result['pd_type2_list']= $pd_type2_list;
        $result['list']         = $list;
        $result['control']      = "display_order";

        Flight::display('pushorder/display_order.html', $result);
    }

    public static function Action_displaylist($r){
        $data   = $r->query;
        $p      = $data->p      ? $data->p      : 1;
        $limit  = $data->limit  ? $data->limit  : 9;

        $pd_type  = $data->pd_type;
        $pd_type2 = $data->pd_type2;

        $ProductDisplay = new ProductDisplay;
        $condition      = array();
        if($pd_type)    $condition[] = "pd_type={$pd_type}";
        if($pd_type2)   $condition[] = "pd_type2={$pd_type2}";
        
        $where  = implode(" AND ", $condition);
        $list   = $ProductDisplay->find($where,array("limit"=>$limit,"page"=>$p));
        
        $result['list'] = $list;
        Flight::display('pushorder/displaylist.html', $result);
    }
     public static function Action_display_order_info($r,$display_id=0){

        $result     = FrontSetting::build();
        $User       = new User;
        $type       = $User->type;
        $user_id    = $User->id;
        
        if($type==2){
            $UserSlave = new UserSlave;
            $userlist  = $UserSlave->get_slave_user_list($user_id);   
        }elseif($type==3 && $User->username!="0"){
            $userlist  = $User->find("ad_id={$user_id}",array("limit"=>1000));
        }

        $result['search_f']     = "ad_display_monitor";
        $result['control']      = "display_order";

        $result['userlist']     = $userlist;
        $result['display_id']   = $display_id;
        Flight::display("pushorder/display_order_info.html",$result);
     }

     public static function Action_display_order_info_api($r){
        $data       = $r->query;
        $display_id = $data->display_id;
        if($display_id){
            $ProductDisplay             = new ProductDisplay($display_id);
            $ProductDisplayMemberColor  = new ProductDisplayMemberColor;
            $ProductGroup               = new ProductGroup;
            $ProductGroupMember         = new ProductGroupMember;
            $OrderList                  = new OrderList;
            $User                       = new User;
            $Company                    = new Company;

            $pushwidth      = $Company->pushwidth;
            $pushheight     = $Company->pushheight;
            if($pushheight && $pushwidth){
                $result['width'] = sprintf("%d",170 * $pushwidth / $pushheight); //根据宽高比计算图片宽度
            }

            $display_info   = $ProductDisplay->getAttribute();
            $condition      =array();
            $user_id        = $User->id;

            $area1          = $data->area1;
            $area2          = $data->area2;
            $fliter_uid     = $data->fliter_uid;
            
            switch ($User->type) {
                case 3:
                    if($User->username!="0"){
                        $condition[]    =   "u.ad_id={$user_id}";
                    }
                    if($area1) $condition[]     = "u.area1={$area1}";
                    if($area2) $condition[]     = "u.area2={$area2}";
                    if($fliter_uid) $condition[]= "u.id={$fliter_uid}";
                    break;
                case 2:
                    if($fliter_uid) 
                        $condition[]= "u.id={$fliter_uid}";
                    $condition[]    = "o.zd_user_id={$user_id}"; 
                    break;                
                default:
                    $condition[]    = "u.id={$user_id}";
                    break;
            }
            $options['tablename']   = "orderlist as o left join user as u on o.user_id=u.id";
            $options['fields']      = "o.product_id,o.product_color_id,sum(o.num) as num,sum(o.amount) as amount";
            $options['group']       = "o.product_id,o.product_color_id";
            $options['limit']       = 10000;
            // $options['db_debug']    =true;
            $where = implode(" AND ", $condition);

            $user_orderlist     = $OrderList->find($where,$options);

            $ord_hash       = array();
            foreach ($user_orderlist as $val){
                $ord_hash[$val['product_id']."_".$val['product_color_id']]['num'] = $val['num'];
                $ord_hash[$val['product_id']."_".$val['product_color_id']]['amount'] = $val['amount'];
            }
            $group_list     = $ProductGroup->find("dp_type2={$display_info['pd_type2']}",array("limit"=>1000,"key"=>"id"));
            
            $hash           = array();

            $display_pc_list= $ProductDisplayMemberColor->find("display_id={$display_id}",array("fields"=>"product_id,keyword_id as color_id,image","limit"=>1000));
            $group_pc_list  = $ProductGroupMember->find("1",array("limit"=>10000));
            foreach ($group_pc_list as $key => $group_pc) {
                $hash[$group_pc['group_id']]['listing'][]   = $group_pc + array("num"=>$ord_hash[$group_pc['product_id']."_".$group_pc['color_id']]['num']);
                $hash[$group_pc['group_id']]['num']         += $ord_hash[$group_pc['product_id']."_".$group_pc['color_id']]['num'];
                $hash[$group_pc['group_id']]['string_num'][] = $ord_hash[$group_pc['product_id']."_".$group_pc['color_id']]['num'] ? $ord_hash[$group_pc['product_id']."_".$group_pc['color_id']]['num'] : 0;
                if(!$ord_hash[$group_pc['product_id']."_".$group_pc['color_id']]['num']) 
                    $hash[$group_pc['group_id']]['notorder'] = 1;
            }
            foreach ($group_list as $key => &$group) {
                $group['num']       = $hash[$group['id']]['num'];
                $group['listing']   = $hash[$group['id']]['listing'];
                $group['string_num']= implode(":", $hash[$group['id']]['string_num']);
                $group['notorder']  = $hash[$group['id']]['notorder'];
            }
            foreach ($display_pc_list as $key => &$display_pc) {
                $display_pc['num']  = $ord_hash[$display_pc['product_id']."_".$display_pc['color_id']]['num'];
                $total['num']       += $ord_hash[$display_pc['product_id']."_".$display_pc['color_id']]['num'];
                $total['amount']    += $ord_hash[$display_pc['product_id']."_".$display_pc['color_id']]['amount'];
            }
            //print_r($group_list);exit;
            $result['group_list']       = $group_list;
            $result['display_info']     = $display_info;
            $result['display_pc_list']  = $display_pc_list;
            $result['total']            = $total;
        }
        $result['search_f'] = "ad_display_monitor";
        $result['control']  = "display_order";
        Flight::display('pushorder/display_order_info_api.html',$result);
    }

    public static function Action_display_next($r){
        $display_id     = $r->query->display_id;
        $f              = $r->query->f;
        switch ($f) {
            case 'pre':
                $condition[]      = "id < {$display_id}";
                $options['order'] = "id DESC";
                $message          = "已经是第一组了";
                break;
            case 'next':
                $condition[]      = "id > {$display_id}";
                $options['order'] = "id ASC";
                $message          = "已经是最后一组了";
                break;
            default:
                $condition[]      = "id={$display_id}";
                $message          = "未找到该款";
                break;
        }
        $ProductDisplay = new ProductDisplay;
        $where          = implode(" AND ", $condition);
        $product_display= $ProductDisplay->findone($where,$options);
        if($product_display['id']){
            $valid   = true;
        }else{
            $valid   = false;
        }
        $result['valid']    = $product_display['id'] ? true : false;
        $result['message']  = $message;
        $result['id']       = $product_display['id'];

        Flight::json($result);
    }
    
    public static function Action_display_by_bianhao($r){
        $bianhao                = $r->query->bianhao;
        $ProductDisplay         = new ProductDisplay;
        $product_display        = $ProductDisplay->findone("bianhao='{$bianhao}'");
        $result['display_id']   = $product_display['id'];
        Flight::json($result);
    }

    public static function Action_ad_group_order($r){
        $result         = FrontSetting::build();
        $ProductGroup   = new ProductGroup;
        $dp_type_list   = $ProductGroup->find("1",array("limit"=>100,"fields"=>"dp_type","group"=>"dp_type"));
        $dp_type2_list  = $ProductGroup->find("1",array("limit"=>100,"fields"=>"dp_type2","group"=>"dp_type2"));
        $result['dp_type_list']     = $dp_type_list;
        $result['dp_type2_list']    = $dp_type2_list;

        $Company        = new Company;
        $show_group_id  = $Company->show_group_id;
        if($show_group_id){
            $ProductGroup           = new ProductGroup($show_group_id);
            $result['group_info']   = $ProductGroup->getAttribute();
        }

        $result['control'] = "group_order";
        Flight::display("pushorder/ad_group_order.html",$result);
    }

    public static function Action_ad_group_order_info($r){
        $data       = $r->query;
        $group_id   = $data->group_id;
        if($group_id){
            $User                   = new User;
            $Product                = new Product;
            $ProductColor           = new ProductColor;
            $ProductGroup           = new ProductGroup($group_id);
            $ProductGroupMember     = new ProductGroupMember;
            $OrderList  = new OrderList;
            $list   = $ProductGroupMember->get_member_list($group_id);
            $options['limit']   = 100;
            $options['key']     = "product_color_id";
            $options['fields']  = "product_id,product_color_id,sum(num) as num,sum(amount) as amount";
            $options['group']   = "product_id,product_color_id";
            //$options['db_debug']=true;
            $user_id            = $User->id;
            $total              = array();
            foreach($list as &$row) {
                $product_id         = $row['product_id'];
                $product            = $Product->findone("id={$product_id}");
                $row['product']     = $product;
                $row['color_info']  = $ProductColor->findone("product_id={$product_id} and color_id={$row['color_id']}");
            }
            $result['group_info']   = $ProductGroup->getAttribute();
            $result['display_id']   = $display_id;
            $result['total']        = $total;
            $result['list']         = $list;
        }

        Flight::display('pushorder/ad_group_order_info.html', $result);
    }

    public static function Action_set_current_show_group_id($r){
        $data           = $r->query;
        $group_id       = $data->group_id;
        if($group_id){
            $Company = new Company;
            $Company->show_group_id = $group_id;
            $result['valid'] = true;
            $result['message'] = "推送成功";
        }else{
            $result['valid'] = false;
            $result['message'] = "设置失败";
        }
        Flight::json($result);
    }

    public static function Action_group_order_monitor($r){

        $result         = FrontSetting::build();
        $data           = $r->query;
        $pd_type        = $data->pd_type;
        $pd_type2       = $data->pd_type2;
        $ProductDisplay = new ProductDisplay();

        $pd_type_list   = $ProductDisplay->find("1",array("limit"=>100,"fields"=>"pd_type","group"=>"pd_type"));
        $pd_type2_list  = $ProductDisplay->find("1",array("limit"=>100,"fields"=>"pd_type2","group"=>"pd_type2"));

        $result['pd_type']  =   $pd_type;
        $result['pd_type2']  =   $pd_type2;
        $result['pd_type_list'] = $pd_type_list;
        $result['pd_type2_list'] = $pd_type2_list;
        
        $result['list'] = $list;

        $result['control']  = "group_order_monitor";

        Flight::display('pushorder/group_order_monitor.html', $result);
    }

    public static function Action_displaylist_monitor($r){
        $data   = $r->query;
        $p      = $data->p      ? $data->p      : 1;
        $limit  = $data->limit  ? $data->limit  : 9;

        $pd_type  = $data->pd_type;
        $pd_type2 = $data->pd_type2;

        $ProductDisplay = new ProductDisplay;
        $condition      = array();
        if($pd_type)  
            $condition[] = "pd_type={$pd_type}";
        if($pd_type2) 
            $condition[] = "pd_type2={$pd_type2}";

        $where = implode(" AND ", $condition);
        $list  = $ProductDisplay->find($where,array("limit"=>$limit,"page"=>$p));
        $result['list'] = $list;
        Flight::display('pushorder/displaylist_monitor.html', $result);
    }

    public static function Action_group_order_monitor_info($r,$id){
        $result         = FrontSetting::build();
        
        if($id){
            $ProductDisplay     = new ProductDisplay($id);
            $result["display"]  = $ProductDisplay->getAttribute();
        }

        $result['search_f'] = "ad_display_monitor";
        $result['control']  = "group_order_monitor";
        Flight::display('pushorder/group_order_monitor_info.html', $result);
    }

    public static function Action_group_order_monitor_list($r){
        $data           = $r->query;
        $display_id     = $data->display_id;

        $ProductDisplay = new ProductDisplay();
        $product_display= $ProductDisplay->findone("id={$display_id}",array("fields"=>"pd_type,pd_type2"));
        $pd_type2       = $product_display['pd_type2']; 

        $ProductGroup   = new ProductGroup;

        $options                = array();
        $options['tablename']   = "product_group as pg left join product_group_member as pgm on pg.id=pgm.group_id 
                                left join orderlistproductcolor as opc on pgm.product_id=opc.product_id and pgm.color_id=opc.product_color_id 
                                left join orderlistuserproductcolor as o on pgm.product_id=o.product_id and pgm.color_id=o.product_color_id ";
        $options['fields']      = "pg.*,opc.num,count(DISTINCT o.user_id) as usernum";
        $options['group']       = "pg.id";
        $options['order']       = "opc.num DESC,pg.id";
        $options['limit']       = 30;
        // $options['db_debug']=true;
        $condition  = array();
        if($pd_type2) 
            $condition[]        = "pg.dp_type2={$pd_type2}";
        $where  = implode(" AND ", $condition);
        $list   = $ProductGroup->find($where,$options);
        
        $result['list']         = $list;
        Flight::display("pushorder/group_order_monitor_list.html",$result);
    }

    public static function Action_group_order_monitor_pc_list($r){
        $data           = $r->query;
        $display_id     = $data->display_id;
        
        $ProductDisplay = new ProductDisplay();
        $product_display= $ProductDisplay->findone("id={$display_id}",array("fields"=>"pd_type,pd_type2"));
        $pd_type2       = $product_display['pd_type2']; 

        $ProductGroup   = new ProductGroup;
        $User           = new User;
        $usernum        = $User->findone("type=1",array("fields"=>"count(*) as usernum"));
        
        $result['usernum']  = $usernum['usernum'];

        $options                = array();
        $options['tablename']   = "product_group as pg left join product_group_member as pgm on pg.id=pgm.group_id 
                                left join orderlistproductcolor as opc on pgm.product_id=opc.product_id and pgm.color_id=opc.product_color_id 
                                left join orderlistuserproductcolor as o on pgm.product_id=o.product_id and pgm.color_id=o.product_color_id 
                                left join product as p on pgm.product_id=p.id
                                left join product_color as pc on p.id=pc.product_id and pc.color_id=pgm.color_id";
        $options['fields']      = "p.kuanhao,p.name,p.price,p.defaultimage,pc.product_id,pc.skc_id,pc.color_id,opc.num,count(DISTINCT o.user_id) as usernum";
        $options['group']       = "pc.product_id,pc.color_id";
        $options['order']       = "opc.num DESC,p.id";
        $options['limit']       = 100;
        // $options['db_debug']=true;
        
        $condition      = array();
        if($pd_type2) 
            $condition[]= "pg.dp_type2={$pd_type2}";
        
        $where  = implode(" AND ", $condition);
        $list   = $ProductGroup->find($where,$options);

        $options                = array();
        $options['tablename']   = "user as u left join orderlist as o on u.id = o.user_id left join location as l on u.area1=l.id";
        $options['fields']      = "COUNT(DISTINCT u.id) as areausernum,COUNT(DISTINCT o.user_id) as usernum,u.area1,l.name,o.product_id,o.product_color_id,sum(num) as num";
        $options['group']       = "u.area1,o.product_id,o.product_color_id";
        $options['limit']       = 10000;
        // $options['db_debug']=true;
        
        $where      = "u.type=1";
        $arealist   = $ProductGroup->find($where,$options);

        $areahash   = array();
        foreach ($arealist as $val) {
            $area1  = $val['area1'];
            $index  = $val['product_id']."_".$val['product_color_id'];
            $areahash[$area1][$index] = $val;
        }
        // print_r($areahash);exit();
        foreach ($list as &$row) {
            $index  = $row['product_id']."_".$row['color_id'];
            foreach ($areahash as $key=>$val){
                $row['arealist'][$key] = $val[$index];
            }
        }

        $result['list']     = $list;
        Flight::display("pushorder/group_order_monitor_pc_list.html",$result);
    }

    public static function Action_get_display_type_list($r){
        $data       = $r->query;
        $pd_type    = $data->pd_type;

        $ProductDisplay = new ProductDisplay;
        $list           = $ProductDisplay->find("pd_type={$pd_type}",array("fields"=>"pd_type2","group"=>"pd_type2"));
        $result['list'] = $list;
    
        Flight::display("pushorder/display_type_list.html", $result);
    }

    public static function Action_get_group_type_list($r){
        $data       = $r->query;
        $dp_type    = $data->dp_type;

        $ProductGroup   = new ProductGroup;
        $list           = $ProductGroup->find("dp_type={$dp_type}",array("fields"=>"dp_type2","group"=>"dp_type2"));
        $result['list'] = $list;
    
        Flight::display("pushorder/group_type_list.html", $result);
    }

    public static function Action_push_monitor ($r){
        $result         = FrontSetting::build();
        $data           = $r->query;
        $pd_type        = $data->pd_type;
        $pd_type2       = $data->pd_type2;
        $ProductDisplay = new ProductDisplay();

        $pd_type_list   = $ProductDisplay->find("1",array("limit"=>100,"fields"=>"pd_type","group"=>"pd_type"));
        $pd_type2_list  = $ProductDisplay->find("1",array("limit"=>100,"fields"=>"pd_type2","group"=>"pd_type2"));
        $pd_type_first  = reset($pd_type_list);
        $pd_type        = $pd_type_first['pd_type'];
        $result['pd_type']      =   $pd_type;
        $result['pd_type2']     =   $pd_type2;
        $result['pd_type_list'] =   $pd_type_list;
        $result['pd_type2_list']=   $pd_type2_list;
        
        $result['list'] = $list;

        $result['control']  = "push_monitor";

        Flight::display('pushorder/push_monitor.html', $result);
    }

    public static function Action_push_monitor_display_list ($r){
        $data           = $r->query;
        $pd_type        = $data->pd_type;
        $pd_type2       = $data->pd_type2;
        $ProductDisplay = new ProductDisplay;

        $cond           = array();
        if($pd_type){
            $cond[]     = "pd.pd_type={$pd_type}";
        }
        if($pd_type2){
            $cond[]     = "pd.pd_type2={$pd_type2}";
        }
        $where          = implode(" AND ", $cond);

        $options                = array();
        $options['tablename']   = "product_display as pd 
                                    left join product_display_member_color as pdmc on pd.id=pdmc.display_id
                                    left join orderlistproductcolor as opc on pdmc.product_id = opc.product_id and pdmc.keyword_id=opc.product_color_id";
        $options['fields']      ="pd.*,sum(opc.num) as num";
        $options['group']       ="pd.id";
        $options['order']       ="pd.id";
        $options['limit']       =30;
        // $options['db_debug']    =true;

        $display_list   = $ProductDisplay->find($where,$options);

        $result['list'] = $display_list;

        Flight::display("pushorder/push_monitor_display_list.html",$result);
    }

    public static function Action_push_monitor_group_list ($r){
        $data           = $r->query;
        $pd_type        = $data->pd_type;
        $pd_type2       = $data->pd_type2;

        $ProductDisplay = new ProductDisplay();
        $cond           = array();
        if($pd_type){
            $cond[]     = "pd_type={$pd_type}";
        }
        if($pd_type2){
            $cond[]     = "pd_type2={$pd_type2}";
        }
        $where          = implode(" AND ", $cond);

        $product_display= $ProductDisplay->find($where,array("fields"=>"pd_type2"));

        $condition      = array();
        foreach ($product_display as $value) {
            $condition[]= $value['pd_type2'];
        }
        $pd_type2_list  = implode(",", $condition); 

        $ProductGroup   = new ProductGroup;

        $options                = array();
        $options['tablename']   = "product_group as pg left join product_group_member as pgm on pg.id=pgm.group_id 
                                    left join orderlistproductcolor as opc on pgm.product_id=opc.product_id and pgm.color_id=opc.product_color_id 
                                    left join orderlist as o on pgm.product_id=o.product_id and pgm.color_id=o.product_color_id ";
        $options['fields']      = "pg.*,sum(DISTINCT opc.num) as num,count(DISTINCT o.user_id) as usernum,user_id,count(DISTINCT pgm.product_id,pgm.color_id) as pc_num";
        $options['group']       = "pg.id";
        $options['order']       = "num DESC,pg.id";
        $options['limit']       = 35;
        // $options['db_debug']    = true;
        $condition              = array();
        if(count($pd_type2_list)) 
            $condition[]        = "pg.dp_type2 in ({$pd_type2_list})";
        
        $where          = implode(" AND ", $condition);
        $list           = $ProductGroup->find($where,$options);
        $result['list'] = $list;

        Flight::display("pushorder/push_monitor_group_list.html",$result);
    }

    public static function Action_push_monitor_product_list ($r) {
        $data           = $r->query;
        $pd_type        = $data->pd_type;
        $pd_type2       = $data->pd_type2;
        
        $ProductDisplay = new ProductDisplay();
        $cond           = array();
        if($pd_type){
            $cond[]     = "pd_type={$pd_type}";
        }
        if($pd_type2){
            $cond[]     = "pd_type2={$pd_type2}";
        }
        $where          = implode(" AND ", $cond);

        $product_display= $ProductDisplay->find($where,array("fields"=>"pd_type2"));

        $condition      = array();
        foreach ($product_display as $value) {
            $condition[]= $value['pd_type2'];
        }
        $pd_type2_list  = implode(",", $condition);

        $ProductGroup   = new ProductGroup;
        $User           = new User;
        $usernum        = $User->findone("type=1",array("fields"=>"count(*) as usernum"));
        
        $result['usernum']      = $usernum['usernum'];

        $options                = array();
        $options['tablename']   =   "product_group as pg left join product_group_member as pgm on pg.id=pgm.group_id
                                    left join product as p on pgm.product_id=p.id
                                    left join product_color as pc on p.id=pc.product_id and pc.color_id=pgm.color_id";
        $options['fields']      = "p.kuanhao,p.name,p.price,p.defaultimage,pc.product_id,pc.skc_id,pc.color_id";
        $options['group']       = "pc.product_id,pc.color_id";
        $options['limit']       = 100;
        $condition              = array();
        if(count($pd_type2_list))
            $condition[]        = "pg.dp_type2 in ({$pd_type2_list})";
        
        $where  = implode(" AND ", $condition);
        $list   = $ProductGroup->find($where,$options);

        $options                = array();
        $options['tablename']   = "user as u left join orderlist as o on u.id = o.user_id left join location as l on u.area1=l.id";
        $options['fields']      = "COUNT(DISTINCT o.user_id) as usernum,u.area1,o.product_id,o.product_color_id,sum(num) as num";
        $options['group']       = "u.area1,o.product_id,o.product_color_id";
        $options['limit']       = 10000;
        // $options['db_debug']    =true;
        
        $where          = "u.type=1 and u.area1<>0";
        $areaorderlist  = $ProductGroup->find($where,$options);

        $options                = array();
        $options['tablename']   = "user as u left join location as l on u.area1=l.id";
        $options['fields']      = "COUNT(DISTINCT u.id) as areausernum,u.area1,l.name";
        $options['group']       = "u.area1";
        $options['limit']       = 100;
        // $options['db_debug']=true;
        $where      = "u.type=1 and u.area1<>0";
        $arealist   = $ProductGroup->find($where,$options);

        $areahash   = array();
        foreach ($areaorderlist as $val) {
            $area1  = $val['area1'];
            $index  = $val['product_id']."_".$val['product_color_id'];
            $areahash[$area1][$index] = $val;
        }

        $options    = array();
        $options['tablename']   =   "product_color as pc 
                                    left join product_display_member_color as pdmc on pc.product_id=pdmc.product_id and pc.color_id = pdmc.keyword_id 
                                    left join product_display as pd on pd.id=pdmc.display_id ";
        $options['fields']      =   "pc.product_id,pc.color_id,GROUP_CONCAT(pd.bianhao) as pdbianhao";
        $options['group']       =   "pc.product_id,pc.color_id";
        $options['limit']       =   10000;

        $product_display_list   =   $ProductGroup->find("1",$options);

        foreach ($product_display_list as $val) {
            $product_display_hash[$val['product_id'].'_'.$val['color_id']]   =   $val['pdbianhao'];
        }

        // print_r($areahash);exit();
        foreach ($list as &$row) {
            $index      = $row['product_id']."_".$row['color_id'];
            $total      = array();
            foreach ($arealist as $val){
                $key    =   $val['area1'];
                $row['arealist'][$key]['area1']         = $val;
                $row['arealist'][$key]['order']         = $areahash[$key][$index];
                $row['arealist'][$key]['user_percent']  = sprintf("%.1f%%",$areahash[$key][$index]['usernum']/$val["areausernum"]*100);
                
                $total['num']           +=  $areahash[$key][$index]['num'];
                $total['usernum']       +=  $areahash[$key][$index]['usernum'];
                $total['areausernum']   +=  $val['areausernum'];
            }
            $row['total']                   = $total;
            $row['total']['user_percent']   = sprintf("%.1f%%",$total['usernum']/$total['areausernum']*100);
            $row['pdbianhao']       =   $product_display_hash[$row['product_id'].'_'.$row['color_id']];
        }
        usort($list, function($a,$b){
            if($a['total']['num'] == $b['total']['num'])
                return 0;
            return $a['total']['num'] > $b['total']['num'] ? -1 : 1;
        });
        $result['list']     = $list;
        Flight::display("pushorder/push_monitor_product_list.html",$result);   
    }
}