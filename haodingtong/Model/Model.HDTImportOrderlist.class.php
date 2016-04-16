<?php

class HDTImportOrderlist
{

    private static $_keywords = array();
    private static $_userinfo = array();
    private static $_product  = array();

    public function __construct($row)
    {
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
        $rid = 1;
        $username = trim($row[$rid ++]);
        $kuanhao  = trim($row[$rid ++]);
        $color    = trim($row[$rid ++]);
        $User       = new User;
        $Product    = new Product;
        $Keywords   = new Keywords;
        $user       = $this->get_userinfo($username);
        $product    = $this->get_product($kuanhao);
        $size_group_id  = $product['size_group_id'];
        $size_group     = SizeGroup::getInstance($size_group_id);
        $size_list      = $size_group->get_size_list();
        $size_data      = array();
        foreach($size_list as $size) {
            $size_data[]    = array('size'=>$size, 'num'=>trim($row[$rid++]));
        }
        $data['user']       = $user;
        $data['product']    = $product;
        $colorinfo  = $this->get_product_colorinfo($product, $color);
        $data['product_color_id']   = $colorinfo['color_id'];
        $data['size_list']  = $size_data;

        $this->data = $data;
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

    public function get_product ($kuanhao) {
        $product    = STATIC::$_product[$kuanhao];
        if(!$product) {
            $Product    = new Product;
            $product    = $Product->findone("kuanhao='{$kuanhao}'");
            STATIC::$_product[$kuanhao] = $product;
        }
        return $product;
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
    }
}




