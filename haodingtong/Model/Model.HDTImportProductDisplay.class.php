<?php

class HDTImportProductDisplay {
	private static $_keywords = array();
    public function __construct($row){
        $this->error_list   = array();
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
        $data       = array();
        $rid        = 1;
        $data['bianhao']    = trim($row[$rid++]);
        $data['name']       = trim($row[$rid++]);
        $data['pd_type']    = Keywords::cache_get_id(trim($row[$rid++]));
        $data['pd_type2']    = Keywords::cache_get_id(trim($row[$rid++]));
        $unit_list  = array();
        while($unit = trim($row[$rid++])){
            $skc_id_list    = explode(";", $unit);
            foreach($skc_id_list as $skc_id){
                if($skc_id){
                    $unit_list[]    = $skc_id;
                }
            }
        }
        $this->unit_list    = array_unique($unit_list);
        $this->data         = $data;
    }

    public function check_unit ($unitkey) {
        $display_id   = $this->attr("id");
        if($display_id){
            $Product        = new Product;
            $ProductColor   = new ProductColor;
            $ProductDisplayMember       = new ProductDisplayMember;
            $ProductDisplayMemberColor  = new ProductDisplayMemberColor;
            $ProductGroupMember = new ProductGroupMember();
            $GroupToDisplay = new GroupToDisplay();
            $ProductGroup = new ProductGroup();
            $member_list    = $ProductDisplayMember->getDisplayMember($display_id);
            $hash           = array();
            foreach($member_list as $member){
                $hash[$member['product_id']]    = $member;
            }
            foreach($this->unit_list as $unit) {
                $SKC_ID     = trim($unit);
                if($SKC_ID){
                    if($unitkey == "skc_id"){
                        $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                        $product_id = $pcinfo['product_id'];
                        $color_id   = $pcinfo['color_id'];
                        if($product_id){
                            $ProductDisplayMember->create_member($display_id, $product_id);
                            $ProductDisplayMemberColor->create_color($display_id, $product_id, $color_id);
                        }else{
                            $bianhao    = $this->attr('bianhao');
                            $this->error("陈列[{$bianhao}]里{$SKC_ID}对应商品资料未找到");
                        }
                    }elseif($unitkey == "group_id"){
                        $pgroup = $ProductGroup->findone('dp_num="'.$SKC_ID.'"');
                        $ginfo = $ProductGroupMember->getGroupMember($pgroup['id']);
                        if(sizeof($ginfo)){
                            $gid = $pgroup['id'];
                            foreach($ginfo as $gval){
                                $product_id = $gval['product_id'];
                                $color_id   = $gval['color_id'];
                                if($product_id){
                                    $ProductDisplayMember->create_member($display_id, $product_id);
                                    $ProductDisplayMemberColor->create_color($display_id, $product_id, $color_id);
                                }
                            }
                            $GroupToDisplay->createGroupToDisplay($gid,$display_id);
                        }else{
                            $bianhao    = $this->attr('bianhao');
                            $this->error("陈列[{$bianhao}]里{$SKC_ID}对应搭配资料未找到");
                        }
                    }else{
                        $product    = $Product->findone("{$unitkey}='{$SKC_ID}'");
                        $product_id = $product['id'];

                        if($product_id){
                            $ProductDisplayMember->create_member($display_id, $product_id);
                            $color_list = $ProductColor->get_color_list($product_id);
                            foreach($color_list as $color){
                                $color_id = $color['color_id'];
                                $ProductDisplayMemberColor->create_color($display_id, $product_id, $color_id);
                            }
                        }else{
                            $bianhao    = $this->attr('bianhao');
                            $this->error("陈列[{$bianhao}]里{$SKC_ID}对应商品资料未找到");
                        }
                    }
                }
            }
        }
    }

    public function check_image ($unitkey) {
        $IMAGE_EXTENDS      = "jpg,jpeg,png,JPG,JPEG,PNG";
        $display_id         = $this->attr('id');
        $val                = $this->attr('bianhao');
        $ProductDisplayImage= new ProductDisplayImage;
        $image_list         = $ProductDisplayImage->get_image_list_by_did($display_id);
        $hash               = array();
        foreach($image_list as $image){
            $hash[$image['image']]  = $image;
        }
        $n = 0;
        $new_image_list     = array();
        while($n < 20){
            $image_name_format = $n ? "{$val}-{$n}" : "{$val}";
            if($image_path  = $this->get_image_filename("tmpl/images/chenlie", $image_name_format, $IMAGE_EXTENDS)){
                if(!$hash[$image_path]){
                    $ProductDisplayImage->create_image($display_id, $image_path);
                    $new_image_list[]   = $image_path;
                }
            }
            $n++;
        }
        if($unitkey=="skc_id"){
            $ProductDisplayMemberColor = new ProductDisplayMemberColor;
            $ProductColor       = new ProductColor;
            $ProductDisplay     = new ProductDisplay;
            $bianhao     = $this->attr('bianhao');
            foreach($this->unit_list as $unit) {
                $SKC_ID     = trim($unit);
                if($SKC_ID){
                    $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                    $product_id = $pcinfo['product_id'];
                    $color_id   = $pcinfo['color_id'];
                    $image_name_format = "{$bianhao}-{$SKC_ID}";
                    if($image_path  = $this->get_image_filename("tmpl/images/chenlie/new", $image_name_format, "png,PNG")){
                        $ProductDisplayMemberColor->update(array("image"=>$image_path),"display_id={$display_id} and product_id={$product_id} and keyword_id={$color_id}");
                    }
                }
            }
            //对比图
            $image_name_format = "{$bianhao}";
            if($image_path  = $this->get_image_filename("tmpl/images/chenlie/new", $image_name_format, "png,PNG")){
                $ProductDisplay->update(array("contrast_image"=>$image_path),"id={$display_id}");
            }
            //底图
            $image_name_format = "{$bianhao}-0";
            if($image_path  = $this->get_image_filename("tmpl/images/chenlie/new", $image_name_format, "png,PNG")){
                $ProductDisplay->update(array("background_image"=>$image_path),"id={$display_id}");
            }
        }elseif($unitkey == "group_id"){
            $ProductDisplayMemberColor = new ProductDisplayMemberColor;
            $ProductColor       = new ProductColor;
            $ProductDisplay     = new ProductDisplay;
            $ProductGroup       = new ProductGroup;
            $bianhao     = $this->attr('bianhao');
            foreach($this->unit_list as $unit) {
                $dp_num     = trim($unit);
                if($dp_num){
                    $skc_info = $ProductGroup->get_skc_info($dp_num);
                    if(sizeof($skc_info)){
                        foreach($skc_info as $val){
                            $product_id = $val['product_id'];
                            $color_id   = $val['color_id'];
                            $skc_id     = $val['skc_id'];
                            $image_name_format = "{$bianhao}-{$skc_id}";
                            if($image_path  = $this->get_image_filename("tmpl/images/chenlie/new", $image_name_format, "png,PNG")){
                                $ProductDisplayMemberColor->update(array("image"=>$image_path),"display_id={$display_id} and product_id={$product_id} and keyword_id={$color_id}");
                            }
                        }
                    }
                }
            }
            //对比图
            $image_name_format = "{$bianhao}";
            if($image_path  = $this->get_image_filename("tmpl/images/chenlie/new", $image_name_format, "png,PNG")){
                $ProductDisplay->update(array("contrast_image"=>$image_path),"id={$display_id}");
            }
            //底图
            $image_name_format = "{$bianhao}-0";
            if($image_path  = $this->get_image_filename("tmpl/images/chenlie/new", $image_name_format, "png,PNG")){
                $ProductDisplay->update(array("background_image"=>$image_path),"id={$display_id}");
            }
        }
        return $new_image_list;
    }

    public function set_default_image ($image_path) {
        $display_id         = $this->attr('id');
        if($display_id){
            $ProductDisplay = new ProductDisplay;
            $ProductDisplay->update(array("defaultimage"=>$image_path), "id={$display_id}");
        }
    }

    public function error ($message) {
        $this->error_list[] = $message; 
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

}




