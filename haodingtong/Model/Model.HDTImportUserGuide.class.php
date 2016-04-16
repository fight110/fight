<?php

class HDTImportUserGuide
{
    private static $_userinfo = array();
    private static $_products = array();

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
        $rid = 1;
        $data['username']   =   $row[$rid++];
        $data['kuanhao']    =   $row[$rid++];
        $data['color']      =   $row[$rid++];
        $data['num']        =   $row[$rid++];
        $this->data = $data;
    }

    public function run () {
        $username   = $this->attr('username');
        if(!$username) {
            return $this->error("客户账户不能为空");
        }
        $userinfo   = $this->get_userinfo($username);
        $user_id    = $userinfo['id'];
        if(!$user_id) {
            return $this->error("客户账户[{$username}]不存在");
        }
        $kuanhao    =   $this->attr('kuanhao');
        if(!$kuanhao) {
            return $this->error("款号不能为空");
        }
        $product_id = $this->get_product_id($kuanhao);
        if(!$product_id) {
            return $this->error("款号{$kuanhao}不存在");
        }
        $color      = $this->attr('color');
        if(!$color) {
            return $this->error("颜色不能为空");
        }
        $color_id   = Keywords::cache_get_id($color);
        $num        = $this->attr("num");
        $UserGuide  = new UserGuide();

        $UserGuide->create_guide($user_id, $product_id,$color_id, $num);
        return $this->error_list;
    }

    public function get_userinfo ($username) {
        $userinfo   = STATIC::$_userinfo[$username];
        if(!$userinfo) {
            $User   = new User;
            $userinfo = $User->findone("username='{$username}' and type=1");
            STATIC::$_userinfo[$username]   = $userinfo;
        }
        return $userinfo;
    }

    public function get_product_id ($kuanhao) {
        $product_id = STATIC::$_products[$kuanhao];
        if(!$product_id) {
            $Product    = new Product;
            $productinfo= $Product->findone("kuanhao='{$kuanhao}'",array("fields"=>"id","db_debug"=>true));
            $product_id = $productinfo['id'];
            STATIC::$_products[$kuanhao]    = $productinfo['id'];
        }
        return $product_id;
    }

    public function error($message)
    {
        $this->error_list[] = $message;
        return $this->error_list;
    }
}




