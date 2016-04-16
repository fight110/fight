<?php

class HDTImportUserSizeHistory
{

    private static $_keywords = array();
    private static $_userinfo = array();
    private static $_Titles   = array(
        "客户账号*"     => "username",
        "客户名称"      => "name",
        "大类*"        => "fieldname",
        "尺码*"       => "sizename",
        "数量"         => "num"
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
            return $this->error("客户账户[{$username}]不存在");
        }
        $keyword_id     = Keywords::cache_get_id($this->attr('fieldname'));
        if($this->check_field($keyword_id)){
            return $this->error("大类中不存在[{$this->attr('fieldname')}]");
        }
        $size_id    =   Keywords::cache_get_id($this->attr('sizename'));
        $UserSizeHistory    = new UserSizeHistory();

        $UserSizeHistory->set_size_num($user_id, $keyword_id,$size_id, $this->attr('num'));
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
    
    public function check_field($keyword_id){
        $ProductsAttr   = new ProductsAttributeFactory('category');
        $tmp    =   $ProductsAttr->find("field='category' and keyword_id={$keyword_id}",array("fields"=>"id"));
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




