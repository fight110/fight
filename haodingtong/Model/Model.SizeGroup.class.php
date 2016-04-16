<?php 

class SizeGroup {
    public static $_instance = array();
    public static function getInstance ($size_group_id) {
        $instance = STATIC::$_instance[$size_group_id];
        if(!$instance) {
            $instance   = new SizeGroup($size_group_id);
            STATIC::$_instance[$size_group_id]   = $instance;
        }
        return $instance;
    }
    public static function getAllInstance () {
        $ProductsAttributeFactory   = new ProductsAttributeFactory('size_group');
        $size_group_list    = $ProductsAttributeFactory->getAllList();
        foreach($size_group_list as $size_group) {
            STATIC::getInstance($size_group['keyword_id']);
        }
        return STATIC::$_instance;
    }
    public static function firstInstance () {
        // $configure  = STATIC::getConfigure();
        // foreach($configure as $type => $size_configure) {
        //     return STATIC::getInstance($type);
        // }
    }
    public function __construct($size_group_id){
        $this->size_group_id    = $size_group_id;
        // $this->size_list    = $size_list;
        // $this->options      = $options;
    }

    public function option($key) {
        if(!$this->options) {
            $this->get_options();
        }
        return $this->options[$key];
    }
    public function get_options () {
        $ProductsSizeGroupOptions   = new ProductsSizeGroupOptions;
        $this->options = $ProductsSizeGroupOptions->findone("size_group_id={$this->size_group_id}");
        return $this->options;
    }
    public function set_options ($new_options=array()) {
        $options    = $this->get_options();
        $options_id = $options['id'];
        $ProductsSizeGroupOptions   = new ProductsSizeGroupOptions;
        if($options_id) {
            $ProductsSizeGroupOptions->update($new_options, "id={$options['id']}");
        }else{
            $ProductsSizeGroupOptions->create($new_options)->insert();
        }
    }

    public function check_size ($size_id) {
        $size_hash  = $this->get_size_hash();
        if(!array_key_exists($size_id, $size_hash)) {
            $ProductsSizeGroup  = new ProductsSizeGroup;
            $ProductsSizeGroup->add_size_group_unit($this->size_group_id, $size_id);
            $this->size_hash[$size_id]  = array('size_id'=>$size_id);
        }
    }

    public function get_size_hash () {
        $hash   = $this->size_hash;
        if(!$hash) {
            $size_list  = $this->get_size_list();
            $hash       = array();
            foreach($size_list as $size) {
                $hash[$size['size_id']] = $size;
            }
            $this->size_hash = $hash;
        }
        return $hash;
    }

    public function get_size_list () {
        $size_list  = $this->size_list;
        if(!$size_list) {
            $ProductsSizeGroup  = new ProductsSizeGroup;
            $size_list          = $ProductsSizeGroup->get_size_list($this->size_group_id);
            $this->size_list    = $size_list;
        }
        return $size_list;
    }
}


