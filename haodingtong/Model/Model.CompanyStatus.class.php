<?php

class CompanyStatus {
    public function __construct(){
        $Company        = new Company;
        $this->company  = $Company;
        $this->data     = $Company->statuslist;
        if(!is_array($this->data)){
            $this->data     = array();
        }
    }

    public function add($status){
        $this->data[]   = array("content"=>$status);
    }

    public function edit($id, $status){
        $this->data[$id]['content'] = $status;
    }

    public function delete($id){
        $list       = $this->data;
        $newlist    = array();
        for($i = 0, $len = count($list); $i < $len; $i++){
            if($i == $id){
                if($list[$i]['content']==$this->company->status){
                    STATIC::setStatus('');
                }
                continue;
            }
            $newlist[]  = $list[$i];
        }
        $this->data     = $newlist;
    }

    public function setStatus($status){
        $this->company->status  = $status;
    }

    public function save(){
        $this->company->statuslist  = $this->data;
    }

    public function getList(){
        return $this->data;
    }

    public function getStatus(){
        return $this->company->status;
    }

    public function __destruct(){
        $this->save();
    }

    
}




