<?php

class HDTImportProductSizeMininum
{

    private static $_keywords = array();
    private static $_products = array();
    public function __construct($row)
    {
        $this->row      = $row;
        $this->error_list = array();
        $this->init($row);
    }

    public function attr($key, $value = null)
    {
        if (null !== $value) {
            $this->data[$key] = $value;
        }
        return $this->data[$key];
    }

    public function attrs()
    {
        return $this->data;
    }

    public function init($row)
    {
        $data = array();
        $rid   = 1;
        $data['kuanhao']= trim($row[$rid++]);
        $data['size']   = trim($row[$rid++]);
        $data['mininum']= trim($row[$rid++]);

        $this->data = $data;
    }

    public function run () {
        $kuanhao   = $this->attr('kuanhao');
        if(!$kuanhao) {
            return $this->error("款号不能为空");
        }
        $product_id   = $this->get_product_id($kuanhao);
        if(!$product_id) {
            return $this->error("款号[{$kuanhao}]不存在");
        }
        $size = $this->attr('size');
        if(!$size){
            return $this->error("尺码不能为空");
        }
        $size_id = Keywords::cache_get_id($size);
        if($this->check_size($product_id,$size_id)){
            return $this->error("[{$kuanhao}]中不存在[{$size}]");
        }
        $ProductSize    = new ProductSize();

        $ProductSize->set_size_mininum($product_id, $size_id, $this->attr('mininum'));
        return $this->error_list;
    }

    public function get_product_id ($kuanhao) {
        $product_id   = STATIC::$_products[$kuanhao];
        if(!$product_id) {
            $Product   = new Product;
            $productinfo = $Product->findone("kuanhao='{$kuanhao}'",array("fields"=>"id"));
            STATIC::$_products[$kuanhao]   = $productinfo['id'];
            $product_id = $productinfo['id'];
        }
        return $product_id;
    }
    
    public function check_size($product_id,$size_id){
        $ProductSize   = new ProductSize();
        $tmp    =   $ProductSize->find("product_id={$product_id} and size_id={$size_id}",array("fields"=>"id"));
        return empty($tmp);
    }

    public function get_title ($title) {
        $title_info = STATIC::$_Titles[$title]; 
        return $title_info;
    }



    public function error($message)
    {
        $this->error_list[] = $message;
        return $this->error_list;
    }
}




