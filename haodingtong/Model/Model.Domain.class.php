<?php

class Domain Extends BaseClass {
    public function __construct(){
        $this->setFactory('domain');
    }

    public function create_domain($domain, $name){
        return $this->create(array('domain'=>$domain,'name'=>$name))->insert();
    }

    public function edit_domain($id,$domain,$name){
        return $this->update(array('domain'=>$domain,'name'=>$name),"id={$id}");
    }
    public function set_status($id,$status){
        return $this->update(array("status"=>$status ? 0 : 1),"id={$id}");
    }
    public function get_list($where="1"){
        return $this->find($where);
    }
}




