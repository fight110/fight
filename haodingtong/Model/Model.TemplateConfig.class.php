<?php

class TemplateConfig Extends BaseClass {
    public function __construct(){
        $this->setFactory('template_config');
    }

    public function get_template_config(){
        $options = array();
        $options['order'] = "rank asc";
        $options['limit'] = 30;
        $where = "status=1" ;
        return $this->find($where,$options);
    }
}




