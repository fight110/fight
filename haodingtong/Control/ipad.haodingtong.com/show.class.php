<?php

class Control_show {
    public static function Action_get_show_id($r){
        Flight::validateUserHasLogin();

        $data               = $r->query;
        $current_show_id    = $data->current_show_id;
        $room_id            = $data->room_id;
        $Company            = new Company;
        $show               = $Company->show;
        $show_id            = $show[$room_id];
        $result['show_id']  = $show_id;
        if($show_id != $current_show_id){
            $Product        = new Product;
            $ProductShow    = new ProductShow;
            $show           = $ProductShow->findone("id={$show_id}");
            $product_ids    = $show['product_ids'];
            $list           = $Product->find("id in ({$product_ids})");
            $result['list'] = $list;
        }

        Flight::json($result);
    }

    //-----------------edit function start------------

    public static function Action_index($r){
        $User       = new User;
        if($User->type == 3){
            $result     = FrontSetting::build();
            $Company    = new Company;
            $result['list'] = $Company->room_list;
            Flight::display('ad/show/index.html', $result);
        }else{
            Flight::redirect("/dealer1/show");
        }
    }

    public static function Action_room($r, $room_id){
        $result     = FrontSetting::build();
        $data           = $r->query;
        $p              = $data->p      ? $data->p      : 1;
        $limit          = $data->limit  ? $data->limit  : 10;
        $Product        = new Product;
        $ProductShow    = new ProductShow;
        $Company        = new Company;
        $options['limit']   = $limit;
        $options['page']    = $p;
        $options['order']   = "id DESC";
        $options['count']   = true;
        $condition[]    =   "room_id={$room_id}";
        $where  = implode(' AND ', $condition);
        $list   = $ProductShow->find($where, $options);
        $total  = $ProductShow->get_count_total();
        foreach($list as &$row){
            $row['plist']   = $Product->find("id in ({$row['product_ids']})");
        }
        $result['list']     = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['company']  = $Company->getData();
        $result['room_id']  = $room_id;
        $result['room']     = $Company->get_room_info($room_id);
        $result['show_id']  = $Company->get_show_id($room_id);

        Flight::display('ad/show/room.html', $result);
    }

    public static function Action_room_delete($r, $id){
        $Company    = new Company;
        $room_list  = $Company->room_list;
        $new_room_list  = array();
        foreach($room_list as $room){
            if($room['id'] != $id){
                $new_room_list[]    = $room;
            }
        }
        $Company->room_list     = $new_room_list;
        Flight::redirect($r->referrer);
    }

    public static function Action_room_add($r){

        $data       = $r->data;
        $id         = $data->id;
        $name       = $data->name;
        $Company    = new Company;
        $room_list  = $Company->room_list;
        if(!is_array($room_list)){
            $room_list  = array();
        }
        $room_len   = count($room_list);
        if($id){
            foreach($room_list as &$room){
                if($room['id'] == $id){
                    $room['name']   = $name;
                    break;
                }
            }
        }else{
            $lastid             = $room_len ? $room_list[$room_len - 1]['id']   : 0;
            $newroom['id']      = $lastid + 1;
            $newroom['name']    = $name;
            $room_list[]        = $newroom;
        }
        $Company->room_list     = $room_list;

        Flight::redirect($r->referrer);
    }

    public static function Action_add($r){

        $data       = $r->data;
        $dp_num     = $data->dp_num;
        $bianhaos   = $data->bianhaos;
        $id         = $data->id;
        $room_id    = $data->room_id;
        $products   = array();
        $Product    = new Product;
        $ProductColor   = new ProductColor;

        if($dp_num){
            $ProductGroupMember     = new ProductGroupMember;
            $plist  = $ProductGroupMember->getGroupMemberByDpnum($dp_num);
            foreach($plist as $pm){
                $products[]         = $pm['product_id'];
            }
        }

        $string_bianhaos    = "";
        $array_bianhaos     = array();
        if(is_array($bianhaos)){
            foreach($bianhaos as $bianhao){
                $product_id     = '';
                if($bianhao > 0){
                    $pcinfo     = $ProductColor->get_by_skc_id($bianhao);
                    if($pcinfo['id']){
                        $product_id     = $pcinfo['product_id'];
                    }else{
                        $product    = $Product->findone("bianhao={$bianhao}");
                        $product_id  = $product['id'];
                    }
                    if($product_id){
                        $products[] = $product_id;
                    }
                    $array_bianhaos[]   = $bianhao;
                }
            }
            $string_bianhaos = implode(',', $array_bianhaos);
        }

        if(count($products)){
            $product_ids    = implode(',', $products);
            $ProductShow    = new ProductShow;
            if($id){
                $ProductShow->update_show($id, $room_id, $dp_num, $string_bianhaos, $product_ids);
                SESSION::message("编辑成功");
            }else{
                $ProductShow->create_show($room_id, $dp_num, $string_bianhaos, $product_ids);
                SESSION::message("添加成功");
            }
        }else{
            SESSION::message("添加或编辑失败");
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_delete($r, $id){

        if(is_numeric($id)){
            $ProductShow    = new ProductShow;
            $ProductShow->delete("id={$id}");
            SESSION::message("删除成功");
        }else{
            SESSION::message("删除失败");
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_set_current_show($r){

        $data           = $r->data;
        $id             = $data->id;
        $room_id        = $data->room_id;
        if($id && $room_id){
            $Company            = new Company;
            $show               = $Company->show;
            if(!is_array($show)){
                $show   = array();
            }
            $show[$room_id]     = $id;
            $Company->show      = $show;
            $result['valid']    = true;
        }else{
            $result['valid']    = false;
            $result['message']  = "设置失败";
        }

        Flight::json($result);
    }

}