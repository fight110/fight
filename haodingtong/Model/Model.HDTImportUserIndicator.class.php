<?php

class HDTImportUserIndicator
{

    private static $_keywords = array();
    private static $_userinfo = array();
    private static $_Titles   = array(
        "客户账号*"     => "username",
        "客户名称"      => "name",
        "属性*"        => "field",
        "属性名称*"     => "fieldname",
        "属性2"       => "field2",
        "属性名称2"    => "fieldname2",
        "指标数量"      => "exp_pnum",
        "指标款色"      => "exp_skc",
        "指标订量"      => "exp_num",
        "指标金额"      => "exp_amount",
        "指标款色深度"  => "exp_skc_depth"
    );
    private static $_fields = array(
        "大类"    => "category_id",
        "小类"    => "classes_id",
        "波段"    => "wave_id",
        "系列"    => "series_id",
        "主题"    => "theme_id",
        "品牌"    => "brand_id",
        "性别"    => "nannvzhuan_id"
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
        if(!strlen($username)) {
            return $this->error("客户账户不能为空");
        }
        $userinfo   = $this->get_userinfo($username);
        $user_id    = $userinfo['id'];
        if(!$user_id) {
            return $this->error("客户账户[{$username}]找不到");
        }
        $field      = $this->attr('field');
        $keyword    = include DOCUMENT_ROOT . "haodingtong/Config/keyword.conf.php";
        if($field) {
            $field_key  = array_search($field,$keyword);
            if(!$field_key) {
                return $this->error("属性[{$field}]不对");
            }
            /* $keys   =   array('category_id','brand_id','season_id');
            if(!in_array($field_key, $keys)){
                return $this->error("属性[{$field}]不支持导入");
            } */
            $keyword_id     = Keywords::cache_get_id($this->attr('fieldname'));
            if($this->check_field($field_key,$keyword_id)){
                return $this->error("[{$field}]中不存在[{$this->attr('fieldname')}]");
            }
        }else{
            $keyword_id =   0;
        }
        $field2 =   $this->attr('field2');
        if($field2){
            $field_key2  = array_search($field2,$keyword);
            if(!$field_key2) {
                return $this->error("属性[{$field2}]不对");
            }
            $keyword_id2     = Keywords::cache_get_id($this->attr('fieldname2'));
            if($this->check_field($field_key2,$keyword_id2)){
                return $this->error("[{$field2}]中不存在[{$this->attr('fieldname2')}]");
            }
        }
        $UserIndicator    = new UserIndicator();

        $data   = $this->data;
        $info   = array("exp_pnum"=>$this->attr("exp_pnum"),"exp_skc"=>$this->attr("exp_skc"),"exp_num"=>$this->attr("exp_num"),"exp_amount"=>$this->attr("exp_amount"),"exp_skc_depth"=>$this->attr("exp_skc_depth"));
        $UserIndicator->set_indicator($user_id, $field_key, $keyword_id,$field_key2,$keyword_id2, $info);
        return $this->error_list;
    }

    public function get_userinfo ($username) {
        $userinfo   = STATIC::$_userinfo[$username];
        if(!$userinfo) {
            $User   = new User;
            $userinfo = $User->findone("username='{$username}'");
            STATIC::$_userinfo[$username]   = $userinfo;
        }
        return $userinfo;
    }
    
    public function check_field($field_key,$keyword_id){
        $field_name     = substr($field_key,0,strlen($field_key)-3);
        $ProductsAttr   = new ProductsAttributeFactory($field_name);
        $tmp    =   $ProductsAttr->find("field='{$field_name}' and keyword_id={$keyword_id}",array("fields"=>"id"));
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




