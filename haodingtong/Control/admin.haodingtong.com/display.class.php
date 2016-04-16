<?php

class Control_display {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $ProductDisplay   = new ProductDisplay;
        $ProductDisplayMember     = new ProductDisplayMember;
        $limit          = 20;
        $list           = $ProductDisplay->find($where, array("page"=>$r->query->p,"limit"=>$limit));
        $total          = $ProductDisplay->getCount($where);
        foreach($list as &$row){
            $row['member']  = $ProductDisplayMember->find("display_id={$row['id']}", array("limit"=>100));
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
        Flight::display('display/index.html', $result);
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
            $ProductDisplay       = new ProductDisplay($id);
            $result['display']    = $ProductDisplay->getAttribute();
            $ProductDisplayImage  = new ProductDisplayImage;
            $imagelist          = $ProductDisplayImage->find("display_id={$id}", array("limit"=>100));
            $result['imagelist']   = $imagelist;
        }else{
            $ProductDisplay       = new ProductDisplay();
        }

        $result['pd_type_list'] =   $ProductDisplay->find("1",array("fields"=>"pd_type","group"=>"pd_type","limit"=>100));
        $result['pd_type2_list'] =   $ProductDisplay->find("1",array("fields"=>"pd_type2","group"=>"pd_type2","limit"=>100));
        $result['control'] = 'product';

        Flight::display("display/add.html", $result);
    }

    public static function Action_memberlist ($r, $display_id) {
        Flight::validateEditorHasLogin();
        $ProductDisplayMemberColor = new ProductDisplayMemberColor;
        $ProductColor       = new ProductColor;
        $memberlist         = $ProductDisplayMemberColor->find("display_id={$display_id}", array("limit"=>100));
        $memberlist         = Flight::listFetch($memberlist, 'product', 'product_id', 'id');
        foreach ($memberlist as &$value) {
            if($value['keyword_id']){
                $info = $ProductColor->get_product_color_info($value['product_id'],$value['keyword_id']);
                $value['skc_string'] = $info['skc_id'];
            }else{
                $skc_list   = $ProductColor->get_distinct_skc_ids($value['product_id']);
                $value['skc_string']   = implode(";", $skc_list);
            }
        }
        $result['displaymember']  = $memberlist;
        Flight::display("display/memberlist.html", $result);
    }

    public static function Action_member_image($r,$display_id){
        Flight::validateEditorHasLogin();

        $ProductDisplayMemberColor = new ProductDisplayMemberColor;
        $newimagelist       = $ProductDisplayMemberColor->get_member_image_list($display_id);
        $result['newimagelist']=$newimagelist;
        Flight::display("group/memberimage.html", $result);
    }

    public static function Action_adding($r){
        Flight::validateEditorHasLogin();

        $bianhao        = $r->data->bianhao;
        $name           = $r->data->name;
        $pd_type        = $r->data->pd_type;
        $pd_type2       = $r->data->pd_type2;
        $defaultimage   = $r->data->defaultimage;
        $contrast_image = $r->data->contrast_image;
        $background_image = $r->data->background_image;
        //print_r($contrast_image);exit;
        $display_id       = $r->data->id;
        if($bianhao && $name){
            $ProductDisplay   = new ProductDisplay;
            if($display_id){
                $ProductDisplay->update(array("bianhao"=>$bianhao, "name"=>$name,"pd_type"=>$pd_type, "pd_type2"=>$pd_type2,
                    "defaultimage"=>$defaultimage,"contrast_image"=>$contrast_image,"background_image"=>$background_image), 
                    "id=$display_id");
            }else{
                $display_id   = $ProductDisplay->create_display($bianhao, $name,$pd_type,$pd_type2, $defaultimage);
            }
        }
        if($display_id){
            $image      = $r->data->image;
            if($image){
                $ProductDisplayImage  = new ProductDisplayImage;
                foreach($image as $i){
                    $ProductDisplayImage->create_image($display_id, $i);
                }
            }
        }
        Flight::redirect("/display/add/{$display_id}");
    }

    public static function Action_add_member($r){
        Flight::validateEditorHasLogin();

        $data       = $r->data;

        $display_id = $data->display_id;
        $product_id = $data->product_id;
        $color_id   = $data->color_id;

        $ProductDisplayMember       = new ProductDisplayMember;
        $ProductDisplayMember->create_member($display_id,$product_id);

        $ProductDisplayMemberColor  = new ProductDisplayMemberColor;
        if(!$color_id){
            $ProductColor   = new ProductColor;
            $color_list     = $ProductColor->get_color_list($product_id);
            foreach ($color_list as $key => $value) {
                $ProductDisplayMemberColor->create_color($display_id,$product_id,$value['color_id']);
            }
        }else{
            $ProductDisplayMemberColor->create_color($display_id,$product_id,$color_id);
        }
        $result = array();

        Flight::json($result); 
    }

    public static function Action_remove_member ($r) {
        Flight::validateEditorHasLogin();

        $data   = $r->data;
        $id     = $data->id;
        if($id) {
            $ProductDisplayMemberColor     = new ProductDisplayMemberColor;
            $info = $ProductDisplayMemberColor->findone("id={$id}");
            $ProductDisplayMemberColor->delete("id={$id}");
            if(! $ProductDisplayMemberColor->find("product_id={$info['product_id']}")){
                $ProductDisplayMember = new ProductDisplayMember;
                $ProductDisplayMember->delete("display_id={$info['display_id']} AND product_id={$info['product_id']}");
            }
        }
        $result     = array();

        Flight::json($result);
    }

    public static function Action_delete($r, $id){
        Flight::validateEditorHasLogin();

        $ProductDisplay   = new ProductDisplay;
        $ProductDisplay->delete("id={$id}");

        Flight::redirect($r->referrer);
    }
    public static function Action_remove_image($r){
        Flight::validateEditorHasLogin();

        $id     = $r->data->id;
        if(is_numeric($id)){
            $ProductDisplayImage = new ProductDisplayImage;
            $ProductDisplayImage->delete("id={$id}");
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
        $limit          = $r->data->limit   ? $r->data->limit   : 100;
        $condition      = array();
        if($q){
            $qt     = addslashes(trim($q));
            if(is_numeric($qt)){
                $condition[]    = "(name LIKE '%{$qt}%' or bianhao like '%{$qt}%' or id in (select product_id from product_color where skc_id='{$qt}'))";
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
        foreach($list as &$row){
            $row['color_list']  = $ProductColor->get_color_list($row['id']);
        }

        $result['list'] = $list;

        Flight::display('display/list.html', $result);
    }
    
    public static function Action_check($r){
        Flight::validateEditorHasLogin();
        $ci = new Control_install();
        $checkResult = $ci->check_product_display();
        $result['error'] = $checkResult;
        $result['control'] = 'product';
        Flight::display('display/check.html', $result);
    }
    
    public static function Action_update_display_status($r){
        Flight::validateEditorHasLogin();
        $data = $r->data;
        $select = $data->sel;
        $notSelect = $data->notSel;
        $pd = new ProductDisplay();
        if(sizeof($select)){
            $pd->update(array('status'=>1), ' id in('.implode(',', $select).') ');
        }
        if(sizeof($notSelect)){
            $pd->update(array('status'=>0), ' id in('.implode(',', $notSelect).') ');
        }
        $result['message'] = '更新成功';
        $result['code'] = 1;
        Flight::json($result);
    }
    public static function Action_set_image($r){
        Flight::validateEditorHasLogin();

        $data = $r->data;
        $id   = $data->id;
        $image= $data->image;
        if($id){
            $ProductDisplayMemberColor = new ProductDisplayMemberColor;
            $ProductDisplayMemberColor->update(array("image"=>$image),"id={$id}");
        }
        $result     = array();

        Flight::json($result);
    }
}
