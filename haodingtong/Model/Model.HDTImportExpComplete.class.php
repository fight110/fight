<?php

class HDTImportExpComplete
{

    private static $_keywords = array();
    private static $_userinfo = array();
    private static $_Titles   = array(
        "客户账号*"     => "username",
        "客户名称"      => "name",
        "属性*"         => "field",
        "属性名称*"     => "fieldname",
        "款数"          => "exp_pnum",
        "款色数"        => "exp_skc",
        "订量"          => "exp_num",
        "金额"          => "exp_price"
    );
    private static $_fields = array(
        "大类"    => "category",
        "小类"    => "classes",
        "波段"    => "wave",
        "系列"    => "series",
        "主题"    => "theme",
        "品牌"    => "brand",
        "性别"    => "nannvzhuan"
    );

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
        foreach($this->titles as $rid => $title) {
            $title  = $this->get_title($title);
            if(!$title) {
                continue;
            }
            $data[$title]   = trim($row[$rid]);
        }

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
            return $this->error("客户账户[{$username}]找不到");
        }
        $field      = $this->attr('field');
        if(!$field) {
            return $this->error("属性不能为空");
        }
        $field_key  = STATIC::$_fields[$field];
        if(!$field_key) {
            return $this->error("属性[{$field}]不对");
        }
        $keyword_id     = Keywords::cache_get_id($this->attr('fieldname'));
        $UserExpComplete    = new UserExpComplete;

        $data   = $this->data;
        $keys   = array("exp_pnum", "exp_skc", "exp_num", "exp_price");
        foreach($keys as $key) {
            if(array_key_exists($key, $data)) {
                $value  = $this->attr($key);
                $UserExpComplete->set_exp($user_id, $field_key, $keyword_id, $key, $value);
            }
        }
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




