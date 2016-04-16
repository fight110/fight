<?php

class Control_product {
    public static function Action_index($r, $conditionString=''){
        Flight::validateEditorHasLogin();

        $Product    = new Product;
        $Company    = new Company;
        $Keywords   = new Keywords;
        $ids        = explode('-', $conditionString);
        $style_id       = $ids[0];
        $wave_id        = $ids[1];
        $category_id    = $ids[2];
        $series_id      = $ids[3];
        $q              = trim($r->query->q);
        $condition      = array();
        $limit          = 20;

        if($style_id){
            $result['style']    = $Keywords->getKeywordName($style_id);
            $condition[]    = "style_id={$style_id}";
        }
        if($wave_id){
            $result['wave']     = $Keywords->getKeywordName($wave_id);
            $condition[]    = "wave_id={$wave_id}";
        }        
        if($category_id){
            $result['category'] = $Keywords->getKeywordName($category_id);
            $condition[]    = "category_id={$category_id}";
        }    
        if($series_id){
            $result['series']     = $Keywords->getKeywordName($series_id);
            $condition[]    = "series_id={$series_id}";
        }
        $ProductColor = new ProductColor;
		if($q){
            $qt     = addslashes($q);
			$pinfo = $ProductColor->get_by_skc_id($q);
			if($product_id = $pinfo['product_id']){
				$condition[] = "(id={$product_id} or kuanhao like '%{$qt}')";	
			}else{
            	$condition[]    = "(bianhao='{$qt}' or kuanhao like '%{$qt}')";
			}
        }

        $where      = implode(' AND ', $condition);
        $list       = $Product->find($where, array("order"=>"id asc", "page"=>$r->query->p, 'limit'=>$limit));
        foreach($list as &$row){
            $row['color_list']  = $ProductColor->get_color_list($row['id']);
            $row['rowspan']     = count($row['color_list']);
        }
        $total      = $Product->getCount($where);
        $pagelist   = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total, $limit);

        $Factory    = new ProductsAttributeFactory('style');
        $result['style_list']       = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('series');
        $result['series_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('wave');
        $result['wave_list']        = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('category');
        $result['category_list']    = $Factory->getAllList();
        

        $result['list'] = $list;
        $result['pagelist'] = $pagelist;
        $result['style_id'] = $style_id;
        $result['wave_id']  = $wave_id;
        $result['category_id']  = $category_id;
        $result['series_id']    = $series_id;
        $result['q']            = $q;
        $result['show_id']      = $Company->show_id;


        Flight::display('product/index.html', $result);
    }

    public static function Action_add($r, $id=0){
        Flight::validateEditorHasLogin();
        
        // $Factory    = new ProductsAttributeFactory('price_band');
        // $result['price_band_list']  = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('style');
        // $result['style_list']       = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('series');
        // $result['series_list']      = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('wave');
        // $result['wave_list']        = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('category');
        // $result['category_list']    = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('classes');
        // $result['classes_list']     = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('size');
        $result['size_list']        = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('color');
        // $result['color_list']       = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('theme');
        // $result['theme_list']       = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('season');
        // $result['season_list']      = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('sxz');
        // $result['sxz_list']         = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('changduankuan');
        // $result['changduankuan_list']   = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('neiwaida');
        // $result['neiwaida_list']        = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('nannvzhuan');
        // $result['nannvzhuan_list']      = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('brand');
        // $result['brand_list']       = $Factory->getAllList();
        // $Factory    = new ProductsAttributeFactory('fabric');
        // $result['fabric_list']      = $Factory->getAllList();
        $Factory    = new ProductsAttributeFactory('size_group');
        $result['size_group_list']      = $Factory->getAllList();

        if($id > 0){
            $Product    = new Product($id);
            $result['product']  = $Product->getAttribute();
            $result['defaultimage'] = $Product->defaultimage;
            $result['product_json'] = json_encode($result['product']);
            $ProductSize    = new ProductSize;
            $result['productsize']  = $ProductSize->get_size_list($id);
            $ProductColor   = new ProductColor;
            $result['productcolor'] = $ProductColor->get_color_list($id);
            $ProductImage   = new ProductImage;
            $result['imagelist'] = $ProductImage->find("product_id={$id}", array("limit"=>100));
        }

        Flight::display('product/add.html', $result);
    }


    public static function Action_adding($r){
        Flight::validateEditorHasLogin();

        $product_id = $r->data->id;
        if($product_id){
            $form       = new Form($r->data, 'product');
            $result     = $form->run('update');
            if($result['valid']){
                $Product    = new Product;
                $Product->update($result['target'], "id={$product_id}");

                $size       = $r->data->size;
                if($size){
                    $ProductSize    = new ProductSize;
                    foreach($size as $s){
                        $ProductSize->create_size($product_id, $s);
                    }
                }

                $color      = $r->data->color;
                if($color){
                    $ProductColor   = new ProductColor;
                    foreach($color as $c){
                        $ProductColor->add_color($product_id, $c);
                    }
                }

                $image      = $r->data->image;
                if($image){
                    $ProductImage   = new ProductImage;
                    foreach($image as $i){
                        $ProductImage->create_image($product_id, $i);
                    }
                }

                SESSION::message("编辑成功");
                ProductOrder::refresh_product_price_change();
            }
        }else{
            $form       = new Form($r->data, 'product');
            $result     = $form->run('insert');
            if($result['valid']){
                $Product    = new Product;
                $product    = $Product->create($result['target']);
                $product_id = $product->insert();

                $size       = $r->data->size;
                if($size){
                    $ProductSize    = new ProductSize;
                    foreach($size as $s){
                        $ProductSize->create_size($product_id, $s);
                    }
                }

                $color      = $r->data->color;
                if($color){
                    $ProductColor   = new ProductColor;
                    foreach($color as $c){
                        $ProductColor->create_color($product_id, $c);
                    }
                }

                $image      = $r->data->image;
                if($image){
                    $ProductImage   = new ProductImage;
                    foreach($image as $i){
                        $ProductImage->create_image($product_id, $i);
                    }
                }

                SESSION::message("添加成功");
            }
        }

        Flight::redirect("/product/add/{$product_id}");

    }

    public static function Action_delete($r, $id){
        Flight::validateEditorHasLogin();

        $Product        = new Product;
        $ProductImage   = new ProductImage;
        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $Product->delete("id=$id");
        $ProductImage->delete("product_id=$id");
        $ProductColor->delete("product_id=$id");
        $ProductSize->delete("product_id=$id");

        $returl     = $r->query->returl     ? $r->query->returl     : $r->referrer;

        Flight::redirect($returl);
    }


    public static function Action_remove_image($r){
        Flight::validateEditorHasLogin();

        $id     = $r->data->id;
        if(is_numeric($id)){
            $ProductImage   = new ProductImage;
            $info   = $ProductImage->findone("id={$id}");
            $ProductImage->delete("id={$id}");
            $ProductImage->add_clear_product_ids($info['product_id']);
        }
        
        Flight::json(array('valid'=>true));
    }

    public static function Action_add_image ($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $image      = $data->image;
        $product_id = $data->product_id;
        $color_id   = $data->color_id ? $data->color_id : 0;
        if($image && $product_id){
            $ProductImage   = new ProductImage;
            $id     = $ProductImage->create_image($product_id, $image, $color_id);
            $result['error']    = 0;
            $result['id']       = $id;
        }

        Flight::json($result);
    }

    public static function Action_remove_size($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $OrderList      = new OrderList;
            $ProductSize    = new ProductSize;
            $info           = $ProductSize->findone("id={$id}");
            $product_id     = $info['product_id'];
            $size_id        = $info['size_id'];
            $list           = $OrderList->find("product_id={$product_id} AND product_size_id={$size_id}", array("limit"=>100000));
            foreach($list as $row) {
                ProductOrder::add($row['user_id'], $row['product_id'], $row['product_color_id'], $row['product_size_id'], 0);
            }
            ProductOrder::run();
            $ProductSize->delete("id={$id}");
        }
        
        Flight::json(array('valid'=>true));
    }
    public static function Action_remove_color($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $ProductColor   = new ProductColor;
            $ProductColor->delete("id={$id}");
        }
        
        Flight::json(array('valid'=>true));
    }

    public static function Action_status($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $status     = $r->query->status;
            $Product    = new Product;
            $Product->update(array('status'=>$status), "id={$id}");
            $OrderList  = new OrderList;
            $OrderList->refresh_product_id($id);
        }
        Flight::redirect($r->referrer);
    }

    public static function Action_hot($r, $id){
        Flight::validateEditorHasLogin();

        if(is_numeric($id)){
            $hot        = $r->query->hot ? 1 : 0;
            $Product    = new Product;
            $Product->update(array('hot'=>$hot), "id={$id}");
        }
        // Flight::json(array('valid'=>true));
        Flight::redirect($r->referrer);
    }

    public static function Action_set_skc_id($r, $id=0) {
        Flight::validateEditorHasLogin();

        if(is_numeric($id)) {
            $ProductColor   = new ProductColor;
            $ProductColorMoq= new ProductColorMoq();
            $color_list     = $ProductColor->get_color_list($id);
            foreach($color_list as &$c) {
                $c['skc_id_num']    = $ProductColor->getCount("skc_id={$c['skc_id']}");
                $c['moq_num']       = $ProductColorMoq->get_num($id, $c['color_id']);
            }
            $result['color_list']   = $color_list;
        }
        $result['id']   = $id;
        $Company    =   new Company();
        $result['company']  =   $Company->getData();
        Flight::display("product/set_skc_id.html", $result);
    }

    public static function Action_set_skc_id_val($r, $id) {
        Flight::validateEditorHasLogin();
       
        if(is_numeric($id)) {
            $data   = $r->data;
            $ProductColor   = new ProductColor;
            $ProductColorMoq= new ProductColorMoq();
            foreach($data as $key => $val) {
                if(preg_match("/^skc_id_(\d+)_(\d+)$/", $key, $match)) {
                    $product_id     = $match[1];
                    $color_id       = $match[2];
                    $ProductColor->set_skc_id($product_id, $color_id, $val);
                }elseif(preg_match("/^color_code_(\d+)_(\d+)$/", $key, $match)){
                    $product_id     = $match[1];
                    $color_id       = $match[2];
                    $ProductColor->set_color_code($product_id, $color_id, $val);
                }elseif(preg_match("/^moq_(\d+)_(\d+)$/", $key, $match)){
                    $product_id     = $match[1];
                    $color_id       = $match[2];
                    $ProductColorMoq->set_num($product_id, $color_id, $val);
                }elseif(preg_match("/^is_need_(\d+)_(\d+)$/", $key, $match)){
                    $product_id     = $match[1];
                    $color_id       = $match[2];
                    $ProductColor->set_need($product_id, $color_id, $val);
                }elseif(preg_match("/^mininum_(\d+)_(\d+)$/", $key, $match)){
                    $product_id     = $match[1];
                    $color_id       = $match[2];
                    $ProductColor->set_mininum($product_id, $color_id, $val);
                }elseif(preg_match("/^main_push_id_(\d+)_(\d+)$/", $key, $match)){
                    $product_id     = $match[1];
                    $color_id       = $match[2];
                    $ProductColor->set_main_push_id($product_id, $color_id, $val);
                }
            }
            SESSION::message("修改成功");
        }
        Flight::redirect($r->referrer);
    }

    public static function Action_set_product_color_status($r, $product_id){
        Flight::validateEditorHasLogin();
        
        if(is_numeric($product_id)){
            $color_id   = $r->query->color_id;
            $status     = $r->query->status;
            $ProductColor   = new ProductColor;
            $ProductColor->set_status($product_id, $color_id, $status);
            // SESSION::message("颜色编辑成功");
        } 

        Flight::redirect($r->referrer);
    }
    
    public static function Action_check($r){
        Flight::validateEditorHasLogin();
        $ci = new Control_install();
        $checkResult = $ci->check_product();
        $result['error'] = $checkResult;
        $result['control'] = 'product';
        Flight::display('product/check.html', $result);
    }
    // public static function Action_add_color ($r) {
    //     Flight::validateEditorHasLogin();

    //     $data       = $r->query;
    //     $product_id = $data->product_id;

    //     $result['product_id']   = $product_id;

    //     Flight::display("product/add_color.html", $result);
    // }
    
    public static function Action_set_stock($r, $id=0){
        Flight::validateEditorHasLogin();

        $ProductColor   = new ProductColor;
        $ProductSize    = new ProductSize;
        $ProductStock   = new ProductStock;

        if($id > 0){
            $Product    = new Product($id);
            $result['product']  = $Product->getAttribute();
            $color_list     = $ProductColor->get_color_list($id);
            $size_list      = $ProductSize->get_size_list($id);
            $stock_list     = $ProductStock->get_product_stock_list($id);
            $result['color_list']   = $color_list;
            $result['size_list']    = $size_list;
            $result['stock_list']   = $stock_list;
        }

        Flight::display('product/set_stock.html', $result);
    }

    public static function Action_set_stocking($r) {
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $ProductStock   = new ProductStock;
        $modify_num = 0;
        foreach($data as $key => $val){
            list($stock, $product_id, $color_id, $size_id)  = explode('-', $key);
            if($stock === "stock" && $product_id && $color_id && $size_id){
                if($ProductStock->create_stock($product_id, $color_id, $size_id, $val)){
                    $modify_num += 1;
                }
            }
        }
        SESSION::message("保存成功");
        Flight::redirect($r->referrer);
    }

    public static function Action_perferential ($r) {
        Flight::validateEditorHasLogin();

        $limit      = 20;
        $ProductPerferential    = new ProductPerferential;
        $condition  = array();
        $where      = implode(' AND ', $condition);
        $list       = $ProductPerferential->find($where, array("order"=>"id desc", "page"=>$r->query->p, 'limit'=>$limit, 'count'=>true));
        $total      = $ProductPerferential->get_count_total();
        $pagelist   = Pager::build(Pager::DEFAULTSTYLE, $r->query, $total, $limit);
        $result['list']     = $list;
        $result['pagelist'] = $pagelist;
        $result['control']  = 'perferential';

        Flight::display('product/perferential.html', $result);
    }

    public static function Action_perferential_json ($r, $id) {
        Flight::validateEditorHasLogin();

        $ProductPerferential    = new ProductPerferential($id);
        $result['data']         = $ProductPerferential->getAttribute();

        Flight::json($result);
    }

    public static function Action_perferential_add ($r) {
        Flight::validateEditorHasLogin();
        $data       = $r->data;
        $id         = $data->id;
        $kuanhao    = $data->kuanhao;
        $start_num  = $data->start_num;
        $price      = $data->price;


        $ProductPerferential    = new ProductPerferential;
        if($id) $perf['id']     = $id;
        $perf['kuanhao']        = $kuanhao;
        $perf['start_num']      = $start_num;
        $perf['price']          = $price;
        $ProductPerferential->create_perferential($perf);

        Flight::redirect($r->referrer);
    }

    public static function Action_perferential_status($r, $id) {
        Flight::validateEditorHasLogin();

        $data       = $r->query;
        $status     = $data->status;
        $ProductPerferential    = new ProductPerferential;
        $ProductPerferential->update(array("status"=>$status), "id={$id}");

        Flight::redirect($r->referrer);
    }

    public static function Action_select_color($r) {
        Flight::validateEditorHasLogin();
        
        $list   =   $r->query->list;

        $Factory= new ProductsAttributeFactory('color');
        $color  = $Factory->getAllList();
        if($list){
            $result['replace_color']    =   true;
            foreach($color as $key=>$row){
                foreach ($list as $l){
                    if($row['keyword_id']==$l){
                        unset($color[$key]);
                    } 
                }
            }
        }
        foreach($color as $key=>$row){
            $color_list[]   =   $row;
        }
        $result['color_list']   =   $color_list;
        Flight::display("product/select_color.html", $result);
    }

    public static function Action_check_size_orderlist ($r, $id) {
        Flight::validateEditorHasLogin();

        $ProductSize    = new ProductSize;
        $OrderList      = new OrderList;
        $size           = $ProductSize->findone("id={$id}");
        $product_id     = $size['product_id'];
        $size_id        = $size['size_id'];
        $options['fields']  = "sum(num) as num";
        $info           = $OrderList->findone("product_id={$product_id} AND product_size_id={$size_id}", $options);
        $result['num']  = $info['num'];

        Flight::json($result);
    }

    public static function Action_size_group_list ($r, $size_group_id) {
        Flight::validateEditorHasLogin();

        $size_group     = SizeGroup::getInstance($size_group_id);
        $size_list      = $size_group->get_size_list();
        $result['size_list']    = $size_list;

        Flight::json($result);
    }
    
    public static function Action_replace_color($r){
        Flight::validateEditorHasLogin();
        $data           =   $r->data;
        $new_color_id   =   $data->new_color_id;
        $color_id       =   $data->color_id;
        $product_id     =   $data->product_id;
        
        $ProductColor   =   new ProductColor();
        $ProductColor->replace_color($product_id, $color_id,$new_color_id);
        //SESSION::message("替换颜色成功");
        $result['message']  =   "颜色变更操作成功，对应订单已成功转移";
        Flight::json($result);
    }

}
