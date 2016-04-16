<?php

class HDTImportUserDiscount
{

    private static $_keywords = array();
    private static $_userinfo = array();
    private static $_Titles   = array(
        "客户账号*" => "username",
        "客户名称"  => "name",
        "属性*"    => "field",
        "属性名称*" => "fieldname",
        "折扣"     => "discount"
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
        $fieldname  = $this->attr('fieldname');
        if(!$field){
            return $this->error("属性不能为空");
        }
        if(!$fieldname){
            return $this->error("属性名称不能为空");
        }
        $keyword    = include DOCUMENT_ROOT . "haodingtong/Config/keyword.conf.php";
        $field_key = array_search($field, $keyword);
        if (! $field_key) {
            return $this->error("属性[{$field}]不对");
        }
        $field = substr($field_key, 0, strlen($field_key) - 3);
        
        $keyword_id = Keywords::cache_get_id($this->attr('fieldname'));
        if ($this->check_field($field_key, $keyword_id)) {
            return $this->error("[{$field}]中不存在[{$this->attr('fieldname')}]");
        }
        
        $UserDiscount    = new UserDiscount();

        $UserDiscount->set_discount($user_id, $field, $keyword_id,$this->attr("discount"));
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
    
    public function check_field($field_key,$keyword_id){
        $field_name     = substr($field_key,0,strlen($field_key)-3);
        $ProductsAttr   = new ProductsAttributeFactory($field_name);
        $tmp    =   $ProductsAttr->find("field='{$field_name}' and keyword_id={$keyword_id}",array("fields"=>"id"));
        return empty($tmp);
    }

    public function error($message)
    {
        $this->error_list[] = $message;
        return $this->error_list;
    }
}