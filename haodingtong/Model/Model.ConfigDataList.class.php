<?php 
 
 class ConfigDataList Extends ConfigData {
    
    public function add($element, $key=null){
        $this->set_is_change();
        if($key !== null){
            $this->data[$key]   = $element;
        }else{
            $this->data[]   = $element;
        }
    }

    public function remove($id){
        $this->set_is_change();
        $newlist    = array();
        foreach($this->data as $key => $val){
            if($key == $id){
                continue;
            }
            $newlist[$key]  = $val;
        }
        $this->data     = $newlist;
    }



}


