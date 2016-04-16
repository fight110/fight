<?php

class HDTImportProduct {
	private static $_keywords = array();
	private static $_sizeGroup = array();
    public function __construct($row){
    	$this->init($row);
    }

    public function attr($key, $value=null) {
    	if(null !== $value) {
    		$this->data[$key]	= $value;
    	}
    	return $this->data[$key];
    }

    public function attrs () {
    	return $this->data;
    }

    public function init ($row) {
    	$product    = array();
        $rid        = 1;
        $product['bianhao'] = trim($row[$rid++]);
        $this->skc_id       = trim($row[$rid++]);
        $product['kuanhao'] = trim($row[$rid++]);
        $product['huohao']  = trim($row[$rid++]);
        $product['name']    = trim($row[$rid++]);
        $product['price']   = trim($row[$rid++]);
        $product['price_purchase']  = trim($row[$rid++]);
        $this->color_string 		= trim($row[$rid++]);
        $this->color_code_string 	= trim($row[$rid++]);
        $this->mininum              = trim($row[$rid++]);   //最小款色起订量
        $this->is_need              = trim($row[$rid++]);
        $this->main_push            = trim($row[$rid++]);   //款色主推款
        $this->sizegroup            = trim($row[$rid++]);
        $this->sizestring			= trim($row[$rid++]);
        $product['proportion_list'] = trim($row[$rid++]);
        if($product['proportion_list']){
            $product['is_proportion']   = 1;
        }
        $product['basenum']         = trim($row[$rid++]);
        $product['order_start_num'] = trim($row[$rid++]);
        //$product['mininum'] = trim($row[$rid++]);
        $price_band         = trim($row[$rid++]);
        if($price_band == "默认"){
        $price_yu       = $product['price'] % 100;
        $price_start    = $product['price'] - $price_yu;
        $price_end      = $price_start + 99;
        $price_band     = "{$price_start}-{$price_end}";     
        }
        $product['price_band_id']   = $this->make_keyword($price_band, 'price_band');
        $product['season_id']       = $this->make_keyword(trim($row[$rid++]), 'season');
        $product['series_id']       = $this->make_keyword(trim($row[$rid++]), 'series');
        $product['category_id']     = $this->make_keyword(trim($row[$rid++]), 'category');
        $product['wave_id']         = $this->make_keyword(trim($row[$rid++]), 'wave');
        $product['medium_id']       = $this->make_keyword(trim($row[$rid++]), 'medium');
        $product['classes_id']      = $this->make_keyword(trim($row[$rid++]), 'classes');
        $product['style_id']        = $this->make_keyword(trim($row[$rid++]), 'style');
        //$product['is_need']         = trim($row[$rid++]);
        $product['theme_id']        = $this->make_keyword(trim($row[$rid++]), 'theme');
        $product['brand_id']        = $this->make_keyword(trim($row[$rid++]), 'brand');
        $product['sxz_id']          = $this->make_keyword(trim($row[$rid++]), 'sxz');
        $product['fabric_id']       = $this->make_keyword(trim($row[$rid++]), 'fabric');
        $product['date_market']     = trim($row[$rid++]);
        $product['content']         = trim($row[$rid++]);
        $product['nannvzhuan_id']   = $this->make_keyword(trim($row[$rid++]), 'nannvzhuan');
        $product['neiwaida_id']     = $this->make_keyword(trim($row[$rid++]), 'neiwaida');
        // $product['designer']        = trim($row[$rid++]);
        $product['designer_id']     = $this->make_keyword(trim($row[$rid++]), 'designer');
        $product['changduankuan_id']= $this->make_keyword(trim($row[$rid++]), 'changduankuan');
        $product['edition_id']      = $this->make_keyword(trim($row[$rid++]), 'edition');
        $product['contour_id']      = $this->make_keyword(trim($row[$rid++]), 'contour');
        $product['price_1']         = trim($row[$rid++]);
        $product['price_2']         = trim($row[$rid++]);
        $product['df1_id']          = $this->make_keyword(trim($row[$rid++]), 'df1');
        $product['df2_id']          = $this->make_keyword(trim($row[$rid++]), 'df2');
        $product['df3_id']          = $this->make_keyword(trim($row[$rid++]), 'df3');
        $product['df4_id']          = $this->make_keyword(trim($row[$rid++]), 'df4');
        $product['df5_id']          = $this->make_keyword(trim($row[$rid++]), 'df5');
        $isspot                     = trim($row[$rid++]);
        $product['isspot']          = $isspot ? $isspot : 1;
        $product['hot']             = trim($row[$rid++]);

        $product['size_group_id']   = $this->make_keyword($this->sizegroup, 'size_group');
        $this->data = $product;
    }

    public function check_change ($info) {
    	foreach($this->data as $key => $val) {
    		if($info[$key] != $val && $key != "bianhao"){
    			return true;
    		}
    	}
    	return false;
    }

    public function check_size () {
    	$product_id 	= $this->attr("id");
    	if($product_id && (string)$this->sizestring !== ''){
	    	$ProductSize 	= new ProductSize;
	    	$OrderList 		= new OrderList;
	    	// $product_arr_group = new ProductsAttrGroup();
	    	// $new_size_list 	= explode(";", $this->sizestring);
	    	// $size_group     = $this->sizegroup;
	    	// if($size_group){
	    	//   $size_group_id = $this->make_keyword($size_group, 'size_group');
	    	// }else{
	    	//   $size_group_id = 0;
	    	// }

            $size_group_id  = $this->attr('size_group_id');
            $SizeGroup      = SizeGroup::getInstance($size_group_id);

	    	$size_list 		= $ProductSize->get_size_list($product_id);
	    	$hash 			= array();
	    	foreach($size_list as $size){
	    		$hash[$size['name']]	= $size;
	    	}
            $new_size_list  = explode(";", $this->sizestring);
	    	foreach($new_size_list as $sizename){
	    		$sizename 	= trim($sizename);
	    		if((string)$sizename === '') continue;
	    		if(!$hash[$sizename]){
	    			$size_id    = $this->make_keyword($sizename, 'size');
                    $ProductSize->create_size($product_id, $size_id);
	    		}else{
	    		    $size_id = $hash[$sizename]['size_id'];
	    			unset($hash[$sizename]);
	    		}
	    		$SizeGroup->check_size($size_id);
	    	}
	    	foreach($hash as $sizename => $size){
	    		$size_id    = $size['size_id'];
                $OrderList->remove_order(0, $product_id, 0, $size_id);
                $ProductSize->remove_size($product_id, $size_id);
	    	}
    	}
    }

    public function check_color () {
    	$product_id 		= $this->attr("id");
    	if($product_id){
			$ProductColor 		= new ProductColor;
			$OrderList 			= new OrderList;
	    	$color_list 		= $ProductColor->get_color_list($product_id);
	        $color_string 		= $this->color_string;
	        $color_code 		= $this->color_code_string;
            $is_need            = $this->is_need;
            $main_push          = $this->main_push;
            $mininum            = $this->mininum;
	        $skc_id 			= $this->skc_id;
	        /*支持颜色分号分隔*/
	        $color_string_list  = explode(";", $color_string);
	        $color_code_list    = explode(";", $color_code);
            $is_need_list       = explode(";", $is_need);
            $mininum_list       = explode(";", $mininum);
            $main_push_list     = explode(";", $main_push);
	        $color_num          = 0;
	        $insert_cnum        = 0;
	        $modify_cnum        = 0;
            foreach ($color_string_list as $color_string){
    	        $color_id   		= $this->make_keyword($color_string, 'color');
                $main_push_id       = $main_push_list[$color_num] ? $this->make_keyword($main_push_list[$color_num],"main_push") : 0;
        
                $hash 				= array();
                $create             = 1;
    	        foreach($color_list as $color){
    	        	if($color['color_code'] == $color_code_list[$color_num] ){
    	        		if($color['color_id'] == $color_id){
    	        			$data 	= array("skc_id"=>$skc_id,"is_need"=>$is_need_list[$color_num],"mininum"=>$mininum_list[$color_num],"main_push_id"=>$main_push_id);
    	        			$ProductColor->update($data, "product_id={$product_id} AND color_id='{$color_id}'");
    	        		}else{
    	        			$new_color_id   = $color_id;
    	        			$old_color_id 	= $color['color_id'];
    	        			$data 	= array("color_id"=>$new_color_id, "skc_id"=>$skc_id, "is_need"=>$is_need_list[$color_num],"mininum"=>$mininum_list[$color_num],"main_push_id"=>$main_push_id);
                            $ret 	= $ProductColor->update($data, "product_id={$product_id} and color_id={$old_color_id}");
                            if($ret){
                            	$OrderList->change_color($product_id, $old_color_id, $new_color_id);
                            }
    	        		}
    	        		/* return "modify_cnum"; */
    	        		$modify_cnum++;
                        $create = 0;
    	        	}
    	        }
                if($create){
        	        /* $ProductColor->create_color($product_id, $color_id, $skc_id, $color_code); */
        	        $ProductColor->create_color($product_id, $color_id, $skc_id, $color_code_list[$color_num],$is_need_list[$color_num],$mininum_list[$color_num],$main_push_id);
                    $insert_cnum++;
                }
                $color_num++;
    	   }
	        //return "insert_cnum";
	        return array("insert_cnum"=>$insert_cnum,"modify_cnum"=>$modify_cnum);
    	}
    }

    public function check_image ($key) {
    	$IMAGE_EXTENDS 		= "jpg,jpeg,png,JPG,JPEG,PNG";
    	$product_id 		= $this->attr('id');
        $ProductImage       = new ProductImage;
        $ProductColor       = new ProductColor;
        $image_list         = $ProductImage->get_image_list($product_id);
        $hash               = array();
        foreach($image_list as $image){
            $hash[$image['image']]  = $image;
        }
        if($key == "product_color") {
            $kuanhao        = $this->attr('kuanhao');
            $color_list     = $ProductColor->get_color_list($product_id);
            foreach($color_list as $color) {
                $color_code     = $color['color_code'];
                $color_id       = $color['color_id'];
                $image_name_format = "{$kuanhao}-{$color_code}";
                if($image_path  = $this->get_image_filename("tmpl/images/chanpin", $image_name_format, $IMAGE_EXTENDS)){
                    if(!$hash[$image_path]){
                        $ProductImage->create_image($product_id, $image_path, $color_id);
                        $new_image_list[]   = $image_path;
                        // $ProductColor->update(array("image"=>$image_path), "product_id={$product_id} AND color_code='{$color_code}'");
                    }
                }
            }
        }else{
            if($key == "skc_id"){
                $val            = $this->skc_id;
            }else{
                $val            = $this->attr($key);
            }
            $n = 0;
            $new_image_list     = array();
            while($n < 20){
                $image_name_format = $n ? "{$val}-{$n}" : "{$val}";
                if($image_path  = $this->get_image_filename("tmpl/images/chanpin", $image_name_format, $IMAGE_EXTENDS)){
                    if(!$hash[$image_path]){
                        if($key == "skc_id") {
                            $pc     = $ProductColor->get_by_skc_id($val);
                            $color_id   = $pc['color_id'];
                        }else{
                            $color_id   = 0;
                        }
                        $ProductImage->create_image($product_id, $image_path, $color_id);
                        $new_image_list[]   = $image_path;
                        // if($key == "skc_id") {
                        //     $ProductColor->update(array("image"=>$image_path), "product_id={$product_id} AND skc_id='{$this->skc_id}'");
                        // }
                    }
                }
                $n++;
            }
        }
    	return $new_image_list;
    }

    public function set_default_image ($image_path) {
    	$product_id 	= $this->attr('id');
    	if($product_id){
	    	$Product 		= new Product;
	    	$Product->update(array("defaultimage"=>$image_path), "id={$product_id}");
    	}
    }

    private function get_image_filename($dir, $skc_id, $extends){
        if(is_string($extends)){
            $extends    = explode(',', $extends);
        }

        foreach($extends as $extend){
            $filename   = "{$dir}/{$skc_id}.{$extend}";
            if(file_exists(DOCUMENT_ROOT . $filename)){
                return $filename;
            }
        }
        return false;
    }

    private function make_keyword ($keyword, $field) {
        $kid    = STATIC::$_keywords[$keyword];

        if(!$kid){
            $kid    = Keywords::cache_get_id(array($keyword));
            STATIC::$_keywords[$keyword]  = $kid;
        }
        if($kid){
            $Factory    = new ProductsAttributeFactory($field);
            $Factory->createItemByKid($kid);
        }
        return $kid;
    }

}




