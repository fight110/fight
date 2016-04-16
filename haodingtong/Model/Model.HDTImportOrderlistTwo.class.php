<?php

class HDTImportOrderlistTwo
{

    private static $_keywords = array();
    private static $_userinfo = array();
    private static $_sizeinfo = array();

    public function __construct($row, $titles)
    {
        $this->titles   = $titles;
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
        $rid  = 1;
        $this->kuanhao      = trim($row[$rid++]);
        $this->color        = trim($row[$rid++]);
        $this->size         = trim($row[$rid++]);
        foreach($this->titles as $rid => $title) {
            if($rid<4)
                continue;
            $userinfo               = $this->get_userinfo(trim($title));
            $data['user_id_list'][] = $userinfo['id'];
            $data['num_list'][]     = trim($row[$rid]);
        }
        $this->data = $data;
    }

    public function run () {
        $Product = new Product;
        $pinfo   = $Product->findone("kuanhao='{$this->kuanhao}'",array("fields"=>"id"));
        if(!$pinfo['id']){
            return $this->error("款号[{$this->kuanhao}]不存在");
        }
        $color_info=$this->get_product_colorinfo($pinfo,$this->color);
        if(!$color_info['color_id']){
            return $this->error("款号[{$this->kuanhao}]不存在颜色[{$this->color}]");
        }

        $ProductSize = new ProductSize;
        $size_id = $this->get_size_id($this->size);
        if(!$size_id){
            return $this->error("尺码[{$this->size}]不存在");
        }
        $size_status = $ProductSize->findone("product_id={$pinfo['id']} and size_id={$size_id}");
        if(!$size_status){
            return $this->error("款号[{$this->kuanhao}]中不存在尺码[{$this->size}]");
        }

        $this->data['product_id'] = $pinfo['id'];
        $this->data['color_id']   = $color_info['color_id'];
        $this->data['size_id']    = $size_id;
        return $this->error_list;
    }

    public function get_userinfo ($username) {
        $userinfo   = STATIC::$_userinfo[$username];
        if(!$userinfo) {
            $User   = new User;
            $userinfo = $User->findone("username='{$username}' AND type=1");
            STATIC::$_userinfo[$username]   = $userinfo;
        }
        return $userinfo;
    }

    public function get_size_id ($size){
        $size_id  = STATIC::$_sizeinfo[$size];
        if(!$size_id) {
            $Factory    = new ProductsAttributeFactory('size');
            $size_list  = $Factory->getNameHash();
            STATIC::$_sizeinfo = $size_list;
            $size_id  = STATIC::$_sizeinfo[$size];
        }
        return $size_id['keyword_id'];
    }

    public function get_title ($title) {
        $title_info = STATIC::$_Titles[$title]; 
        return $title_info;
    }

    public function get_product_colorinfo ($product, $color) {
        $product_id     = $product['id'];
        $color_id       = Keywords::cache_get_id($color);
        $ProductColor   = new ProductColor;
        $pcinfo         = $ProductColor->findone("product_id={$product_id} AND color_id={$color_id} AND status=1");
        return $pcinfo;
    }

    public function error($message)
    {
        $this->error_list[] = $message;
        return $this->error_list;
    }
}




