<?php

class Control_keyword {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $t          = $r->query->t  ? $r->query->t : "price_band";
        $Factory    = new ProductsAttributeFactory($t);
        if($Factory->getError()){
            exit;
        }
        $list       = $Factory->getAllList(array('cache_time'=>0));
        $result['list'] = $list;
        $result['t']    = $t;

        Flight::display('keyword/index.html', $result);
    }

    public static function Action_area($r){
        Flight::validateEditorHasLogin();

        $Location       = new Location;
        $tree           = $Location->getChildrenTree(0);
        $result['tree'] = $tree;
        $result['t']    = 'area';

        Flight::display('keyword/area.html', $result);
    }

    public static function Action_edit($r){
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $id         = $data->id;
        $name       = $data->name;
        $rank       = $data->order;
        $fields         = $data->t;
        if($id){
            $Keywords   = new Keywords;
            $Keywords->update(array("name"=>$name), "id={$id}");
            $Keywords->delete_file();
            if($fields&&is_numeric($rank)){
                $proatr   = new ProductsAttributeFactory($fields);
                $proatr->update(array("rank"=>$rank), "keyword_id={$id} AND field='".$fields."' ");
            }
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_group($r){
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $t          = $data->t      ? $data->t      : "color_group";
        $Factory    = new ProductsAttributeFactory($t);
        if($Factory->getError()){
            exit;
        }
        $list       = $Factory->getAllList();
        $result['list'] = $list;
        $result['t']    = $t;

        switch ($t) {
            case 'color_group'  :
                $t          = "color";
                break;
            case 'size_group'   :
                $t          = "size";
                break;
            default :
                $t          = "";
        }
        if($t){
            $Factory    = new ProductsAttributeFactory($t);
            $group_list = $Factory->get_group_list();
            $result['group_list']   = $group_list;
        }

        Flight::display("keyword/group.html", $result);
    }

    public static function Action_size_group ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $t          = $data->t      ? $data->t      : "size_group";
        $Factory    = new ProductsAttributeFactory($t);
        if($Factory->getError()){
            exit;
        }
        $list       = $Factory->getAllList();

        $Factory    = new ProductsAttributeFactory('size');
        $size_list  = $Factory->getAllList();

        $ProductsSizeGroup  = new ProductsSizeGroup;
        foreach($list as &$size_group) {
            $size_group_id = $size_group['keyword_id'];
            $size_hash     = $ProductsSizeGroup->get_size_hash($size_group_id);
            foreach($size_list as $size) {
                $size['status'] = $size_hash[$size['keyword_id']] ? 1 : 0;
                $size_group['size_list'][]  = $size; 
            }
        }
        $result['list'] = $list;
        $result['t']    = $t;

        Flight::display("keyword/size_group.html", $result);
    }

    public static function Action_size_group_set ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $size_group_id  = $data->size_group_id;
        $size_id        = $data->size_id;
        $checked        = $data->checked;
        if($size_id && $size_group_id) {
            $ProductsSizeGroup  = new ProductsSizeGroup;
            if($checked) {
                $ProductsSizeGroup->add_size_group_unit($size_group_id, $size_id);
            }else{
                $ProductsSizeGroup->del_size_group_unit($size_group_id, $size_id);
            }
        }
        $result = array();
        Flight::json($result);
    }


    public static function Action_addarea($r){
        Flight::validateEditorHasLogin();

        $name           = trim($r->data->name);
        $pid            = $r->data->pid;
        if($name){
            $Location       = new Location;
            $Location->addNode($pid, $name);
            SESSION::message("添加成功");
        }else{
            SESSION::message("不能为空");
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_deletearea($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $Location   = new Location;
            $Location->removeNode($id);
            SESSION::message("删除成功");
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_add($r){
        Flight::validateEditorHasLogin();
        $name       = $r->data->name;
        $table      = $r->data->t;
        $rank       = $r->data->order;
        if($name && $table){
            $Factory    = new ProductsAttributeFactory($table);
            if($Factory->getError()){
                SESSION::message($Factory->getError());
            }else{
                $aid    = $Factory->createItem($name);
            }
            if(is_numeric($rank)){
                $Factory->update(array("rank"=>$rank), "keyword_id={$aid} AND field='".$table."' ");
            }
            if($aid && ($table == "color_group" || $table == "size_group")){
                if($table=="color_group"){
                    $ProductsColorGroup = new ProductsColorGroup;
                    $ProductsColorGroup->createItem($aid,'000000');
                }
                $color_ids  = $r->data->color_id;
                if(is_array($color_ids)){
                    $ProductsAttrGroup   = new ProductsAttrGroup;
                    foreach($color_ids as $attr_id){
                        $ProductsAttrGroup->add_attr_group($attr_id, $aid);
                    }
                }
            }
        }else{
            SESSION::message("参数错误:{$name}:{$table}");
        }

        Flight::redirect($r->referrer);
    }

    public static function Action_delete_group($r, $id){
        Flight::validateEditorHasLogin();
        if(is_numeric($id)){
            $ProductsAttrGroup  = new ProductsAttrGroup;
            $ProductsAttrGroup->delete("id={$id}");
            SESSION::message("取消成功");
        }
        Flight::redirect($r->referrer);
    }

    public static function Action_delete($r, $id=0){
        Flight::validateEditorHasLogin();
        $table      = $r->query->t;
        if($table && is_numeric($id)){
            $Factory    = new ProductsAttributeFactory($table);
            if($Factory->getError()){
                SESSION::message($Factory->getError());
            }else{
                if($table == "color_group" || $table == "size_group"){
                    $row    = $Factory->findone("id={$id}");
                    if($row['id']){
                        $ProductsAttrGroup  = new ProductsAttrGroup;
                        $ProductsAttrGroup->delete("group_id={$row['keyword_id']}");
                    }
                    if($table=="color_group"){
                        $ProductsColorGroup = new ProductsColorGroup;
                        $ProductsColorGroup->delete("keyword_id={$row['keyword_id']}");
                    }
                }
                $Factory->delete("id={$id}");
                SESSION::message("删除成功");
            }
        }
        Flight::redirect($r->referrer);
    }

    public static function Action_setrank($r){
        Flight::validateEditorHasLogin();
        $table      = $r->data->t;
        $list       = $r->data->list;
        if($table && $list){
            $Factory    = new ProductsAttributeFactory($table);
            if($Factory->getError()){
                $message    = $Factory->getError();
            }else{
                $newlist    = explode(',', $list);
                foreach($newlist as $row){
                    $r  = explode(':', $row);
                    if(is_numeric($r[0]) && is_numeric($r[1])){
                        $Factory->update(array("rank"=>$r[1]), "id={$r[0]}");
                    }
                }
            }
        }

        if($message){
            $result     = array('valid' => false, "message" => $message);
        }else{
            $result     = array("valid" => true);
        }

        Flight::json($result);
    }

    public static function Action_update($r){
        Flight::validateEditorHasLogin();
        $data = $r->query;   
        $field = $data->field;
        $id = $data->id;
        $val = $data->val;
        $t = $data->t;
        if($id){
            if($field=='name'){
                $Keywords   = new Keywords;
                $Keywords->update(array("name"=>$val), "id={$id}");
            }elseif($field=='rank'){
                $proatr   = new ProductsAttributeFactory($t);
                $proatr->update(array("rank"=>$val), "keyword_id={$id} AND field='".$t."' ");
            }   
            $Keywords->delete_file();
        }
        $result     = array("status" => 1);
        Flight::json($result);
    }

    public static function Action_size_group_options ($r, $size_group_id) {
        Flight::validateEditorHasLogin();

        $size_group     = SizeGroup::getInstance($size_group_id);
        $size_group_options     = $size_group->get_options();
        $result['size_group_options']   = $size_group_options;
        $result['size_group_id']        = $size_group_id;
        $result['t']    = 'size_group';

        Flight::display("keyword/size_group_options.html", $result);
    }

    public static function Action_size_group_options_save ($r) {
        Flight::validateEditorHasLogin();

        $data           = $r->data;
        $id             = $data->id;
        $num            = $data->num;
        $restriction    = $data->restriction;
        $size_group_id  = $data->size_group_id;
        $options['num'] = $num;
        $options['restriction'] = $restriction;
        $options['size_group_id'] = $size_group_id;
        $size_group     = SizeGroup::getInstance($size_group_id);
        $size_group->set_options($options);
        SESSION::message("保存成功");

        Flight::redirect($r->referrer);
    }

    public static function Action_color_rgb($r){
        Flight::validateEditorHasLogin();
        
        $data   = $r->data;
        $id     = $data->id;
        $rgb    = $data->rgb;
        
        $ProductsColorGroup  =   new ProductsColorGroup();
        
        $ProductsColorGroup->update(array("rgb"=>$rgb),"id={$id}");
        
        $result['list']     =   $ProductsColorGroup->get_list();
        $result['t']        = 'color_rgb';
        
        Flight::display('keyword/color_rgb.html', $result);
    }
}