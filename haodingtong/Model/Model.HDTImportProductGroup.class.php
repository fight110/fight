<?php

class HDTImportProductGroup {
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
        $data['dp_num']     = trim($row[$rid++]);
        $data['name']       = trim($row[$rid++]);
        $data['dp_type']    = Keywords::cache_get_id(trim($row[$rid++]));
        $data['dp_type2']    = Keywords::cache_get_id(trim($row[$rid++]));
        $unit_list  = array();
        while($unit = trim($row[$rid++])){
            $unit_list[]    = $unit;
        }
        $this->unit_list    = $unit_list;
        $this->data         = $data;
    }

    public function check_unit ($unitkey) {
        $group_id   = $this->attr("id");
        if($group_id){
            $Product        = new Product;
            $ProductColor   = new ProductColor;
            $ProductGroupMember     = new ProductGroupMember;
            $member_list    = $ProductGroupMember->getGroupMember($group_id);
            $hash           = array();
            foreach($member_list as $member){
                $hash[$member['product_id'].'_'.$member['color_id']]    = $member;
            }
            foreach($this->unit_list as $unit) {
                $skc_id_list    = explode(";", $unit);
                foreach($skc_id_list as $SKC_ID) {
                    $SKC_ID     = trim($SKC_ID);
                    if($SKC_ID){
                        if($unitkey == "skc_id"){
                            $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                            $product_id = $pcinfo['product_id'];
                            $color_id   = $pcinfo['color_id'];
                        }else{
                            $product    = $Product->findone("{$unitkey}='$SKC_ID'");
                            $product_id = $product['id'];
                            $color_id   = 0;
                        }
                        if($product_id){
                            if(!$hash[$product_id.'_'.$color_id]){
                                $ProductGroupMember->create_member($group_id, $product_id, $color_id);
                            }
                        }else{
                            $dp_num     = $this->attr('dp_num');
                            $this->error("搭配[{$dp_num}]里{$SKC_ID}对应商品资料未找到");
                        }
                    }
                }
            }
        }
    }

    public function check_image ($unitkey) {
        $IMAGE_EXTENDS      = "jpg,jpeg,png,JPG,JPEG,PNG";
        $group_id           = $this->attr('id');
        $val                = $this->attr('dp_num');
        $ProductGroupImage  = new ProductGroupImage;
        $image_list         = $ProductGroupImage->get_image_list($group_id);
        $hash               = array();
        foreach($image_list as $image){
            $hash[$image['image']]  = $image;
        }
        $n = 0;
        $new_image_list     = array();
        while($n < 20){
            $image_name_format = $n ? "{$val}-{$n}" : "{$val}";
            if($image_path  = $this->get_image_filename("tmpl/images/dapei", $image_name_format, $IMAGE_EXTENDS)){
                if(!$hash[$image_path]){
                    $ProductGroupImage->create_image($group_id, $image_path);
                    $new_image_list[]   = $image_path;
                }
            }
            $n++;
        }
        if($unitkey=="skc_id"){
            $ProductGroupMember = new ProductGroupMember;
            $ProductColor       = new ProductColor;
            $ProductGroup       = new ProductGroup;
            $dp_num     = $this->attr('dp_num');
            foreach($this->unit_list as $unit) {
                $skc_id_list    = explode(";", $unit);
                foreach($skc_id_list as $SKC_ID) {
                    $SKC_ID     = trim($SKC_ID);
                    if($SKC_ID){
                        $pcinfo = $ProductColor->get_by_skc_id($SKC_ID);
                        $product_id = $pcinfo['product_id'];
                        $color_id   = $pcinfo['color_id'];
                        $image_name_format = "{$dp_num}-{$SKC_ID}";
                        if($image_path  = $this->get_image_filename("tmpl/images/dapei/new", $image_name_format, "png,PNG")){
                            $ProductGroupMember->update(array("image"=>$image_path),"group_id={$group_id} and product_id={$product_id} and color_id={$color_id}");
                        }
                    }
                }
            }
            //对比图
            $image_name_format = "{$dp_num}";
            if($image_path  = $this->get_image_filename("tmpl/images/dapei/new", $image_name_format, "png,PNG")){
                $ProductGroup->update(array("contrast_image"=>$image_path),"id={$group_id}");
            }
            //底图
            $image_name_format = "{$dp_num}-0";
            if($image_path  = $this->get_image_filename("tmpl/images/dapei/new", $image_name_format, "png,PNG")){
                $ProductGroup->update(array("background_image"=>$image_path),"id={$group_id}");
            }
        }
        return $new_image_list;
    }

    public function set_default_image ($image_path) {
        $group_id           = $this->attr('id');
        if($group_id){
            $ProductGroup   = new ProductGroup;
            $ProductGroup->update(array("defaultimage"=>$image_path), "id={$group_id}");
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




