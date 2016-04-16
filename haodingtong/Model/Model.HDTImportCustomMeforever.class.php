<?php

class HDTImportCustomMeforever
{
    private static $_keywords = array();
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
        $data               = array();
        $rid                = 1;
        $this->username     = trim($row[$rid++]);
        $data['area']       = trim($row[$rid++]);
        $data['category_id']= $this->make_keyword(trim($row[$rid++]), 'category');
        $data['medium_id']  = $this->make_keyword(trim($row[$rid++]), 'medium');
        $data['wave_id']    = $this->make_keyword(trim($row[$rid++]), 'wave');
        $data['ship_num']   = trim($row[$rid++]);
        $data['ship_price'] = trim($row[$rid++]);
        $data['ship_skc']   = trim($row[$rid++]);
        $data['sales_num']  = trim($row[$rid++]);
        $data['sales_price']= trim($row[$rid++]);
        $data['sales_skc']  = trim($row[$rid++]);
        $data['order_num']  = trim($row[$rid++]);
        $data['order_price']= trim($row[$rid++]);
        $data['order_skc']  = trim($row[$rid++]);
        $data['stock_num']  = trim($row[$rid++]);
        $data['stock_price']= trim($row[$rid++]);
        $this->data         = $data;
    }

    public function run () {
        $username   = $this->username;
        if(!strlen($username)) {
            return $this->error("客户账户不能为空");
        }
        $User       = new User;
        $userinfo   = $User->findone("username='{$username}'",array("fields"=>"id"));
        $user_id    = $userinfo['id'];
        if(!$user_id) {
            return $this->error("客户账户[{$username}]找不到");
        }
        $data           = $this->data;
        $data['user_id']= $user_id;
        $CustomMeforeverHistory = new CustomMeforeverHistory;
        $CustomMeforeverHistory->create($data)->insert(true);
        return $this->error_list;
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

    public function error($message)
    {
        $this->error_list[] = $message;
        return $this->error_list;
    }
}




