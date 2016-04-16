<?php

class Control_group {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $ProductGroup   = new ProductGroup;
        $ProductGroupMember     = new ProductGroupMember;
        $limit          = 20;
        $list           = $ProductGroup->find($where, array("page"=>$r->query->p,"limit"=>$limit));
        $total          = $ProductGroup->getCount($where);
        foreach($list as &$row){
            $row['member']  = $ProductGroupMember->find("group_id={$row['id']}", array("limit"=>100));
            $row['member']  = Flight::listFetch($row['member'], 'product', 'product_id', 'id');
        }

        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total,$limit);

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();
        $result['control'] = 'product';
        Flight::display('group/index.html', $result);
    }

    public static function Action_add($r, $id=0){
        Flight::validateEditorHasLogin();

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();
        if($id){
            $ProductGroup       = new ProductGroup($id);
            $result['group']    = $ProductGroup->getAttribute();
            $ProductGroupImage  = new ProductGroupImage;
            $imagelist          = $ProductGroupImage->find("group_id={$id}", array("limit"=>100));
            $result['imagelist']   = $imagelist;
        }else{
            $ProductGroup       = new ProductGroup();
        }

        $result['dp_type_list'] =   $ProductGroup->find("1",array("fields"=>"dp_type","group"=>"dp_type","limit"=>100));
        $result['dp_type2_list'] =   $ProductGroup->find("1",array("fields"=>"dp_type2","group"=>"dp_type2","limit"=>100));
        $result['control'] = 'product';

        Flight::display("group/add.html", $result);
    }

    public static function Action_memberlist ($r, $group_id) {
        Flight::validateEditorHasLogin();

        $ProductGroupMember = new ProductGroupMember;
        $ProductColor = new ProductColor;
        $memberlist         = $ProductGroupMember->find("group_id={$group_id}", array("limit"=>100));
        $memberlist         = Flight::listFetch($memberlist, 'product', 'product_id', 'id');
        foreach ($memberlist as &$value) {
            if($value['color_id']){
                $info = $ProductColor->get_product_color_info($value['product_id'],$value['color_id']);
                $value['skc_string'] = $info['skc_id'];
            }else{
                $skc_list   = $ProductColor->get_distinct_skc_ids($value['product_id']);
                $value['skc_string']   = implode(";", $skc_list);
            }
        }
        $result['groupmember']  = $memberlist;

        Flight::display("group/memberlist.html", $result);
    }

    public static function Action_member_image($r,$group_id){
        Flight::validateEditorHasLogin();

        $ProductGroupMember = new ProductGroupMember;
        $newimagelist       = $ProductGroupMember->get_member_image_list($group_id);
        $result['newimagelist']=$newimagelist;
        Flight::display("group/memberimage.html", $result);
    }

    public static function Action_adding($r){
        Flight::validateEditorHasLogin();

        $dp_num         = $r->data->dp_num;
        $name           = $r->data->name;
        $dp_type        = $r->data->dp_type;
        $dp_type2       = $r->data->dp_type2;
        $defaultimage   = $r->data->defaultimage;
        $contrast_image = $r->data->contrast_image;
        $background_image = $r->data->background_image;
        $group_id       = $r->data->id;
        if($dp_num && $name){
            $ProductGroup   = new ProductGroup;
            if($group_id){
                $ProductGroup->update(array("dp_num"=>$dp_num, "name"=>$name,"dp_type"=>$dp_type, "dp_type2"=>$dp_type2,
                    "defaultimage"=>$defaultimage,"contrast_image"=>$contrast_image,"background_image"=>$background_image), 
                    "id=$group_id");
            }else{
                $group_id   = $ProductGroup->create_group($dp_num, $name,$dp_type,$dp_type2, $defaultimage);
            }
        }

        if($group_id){
            $image      = $r->data->image;
            if($image){
                $ProductGroupImage  = new ProductGroupImage;
                foreach($image as $img){
                    $ProductGroupImage->create_image($group_id, $img);
                }
            }
        }

        Flight::redirect("/group/add/{$group_id}");
    }

    public static function Action_delete($r, $id){
        Flight::validateEditorHasLogin();

        $ProductGroup   = new ProductGroup;
        $ProductGroup->delete("id={$id}");

        Flight::redirect($r->referrer);
    }
    public static function Action_remove_image($r){
        Flight::validateEditorHasLogin();

        $id     = $r->data->id;
        if(is_numeric($id)){
            $ProductGroupImage = new ProductGroupImage;
            $ProductGroupImage->delete("id={$id}");
        }

        Flight::json(array('valid'=>true));
    }

    public static function Action_list($r){
        Flight::validateEditorHasLogin();

        $style_id       = $r->data->style_id;
        $category_id    = $r->data->category_id;
        $classes_id     = $r->data->classes_id;
        $series_id      = $r->data->series_id;
        $wave_id        = $r->data->wave_id;
        $q              = $r->data->q;
        $filter         = $r->data->filter;
        $limit          = $r->data->limit   ? $r->data->limit   : 15;
        $condition      = array();
        if($q){
            $qt     = addslashes(trim($q));
            if(is_numeric($qt)){
                $condition[]    = "(name LIKE '%{$qt}%' or bianhao='{$qt}' or id in (select product_id from product_color where skc_id='{$qt}'))";
            }else{
                $condition[]    = "name LIKE '%{$qt}%'";
            }
        }
        if($style_id){
            $condition[]    = "style_id={$style_id}";
        }
        if($category_id){
            $condition[]    = "category_id={$category_id}";
        }
        if($classes_id){
            $condition[]    = "classes_id={$classes_id}";
        }
        if($series_id){
            $condition[]    = "series_id={$series_id}";
        }
        if($wave_id){
            $condition[]    = "wave_id={$wave_id}";
        }
        if($filter){
            $condition[]    = "id not in($filter)";
        }
        $Product        = new Product;
        $ProductColor   = new ProductColor;
        $where      = implode(' AND ', $condition);
        $list       = $Product->find($where, array("order"=>"id desc", "page"=>$r->query->p, 'limit'=>$limit));
        foreach($list as &$row) {
            $row['color_list']  = $ProductColor->get_color_list($row['id']);
        }

        $result['list'] = $list;

        Flight::display('group/list.html', $result);
    }

    public static function Action_check($r){
        Flight::validateEditorHasLogin();
        $ci = new Control_install();
        $checkResult = $ci->check_product_group();
        $result['error'] = $checkResult;
        $result['control'] = 'product';
        Flight::display('group/check.html', $result);
    }

    public static function Action_add_member ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;

        $create['group_id']   = $data->group_id;
        $create['product_id'] = $data->product_id;
        $create['color_id']   = $data->color_id ? $data->color_id : 0;

        $ProductGroupMember     = new ProductGroupMember;
        $ProductGroupMember->create($create)->insert(true);

        $result = array();

        Flight::json($result); 
    }

    public static function Action_remove_member ($r) {
        Flight::validateEditorHasLogin();

        $data   = $r->data;
        $id     = $data->id;
        if($id) {
            $ProductGroupMember     = new ProductGroupMember;
            $ProductGroupMember->delete("id={$id}");
        }
        $result     = array();

        Flight::json($result);
    }

    public static function Action_set_image($r){
        Flight::validateEditorHasLogin();

        $data = $r->data;
        $id   = $data->id;
        $image= $data->image;
        if($id){
            $ProductGroupMember = new ProductGroupMember;
            $ProductGroupMember->update(array("image"=>$image),"id={$id}");
        }
        $result     = array();

        Flight::json($result);
    }
}