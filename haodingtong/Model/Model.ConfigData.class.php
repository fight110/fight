<?php 
 
 class ConfigData {
    protected $data, $is_change = false;
    public function __construct($filename){
        $this->config   = new ConfigReader;
        $this->fullname = "Data/{$filename}";
        if($this->config->is_file_exists($this->fullname)){
            $this->data = $this->config->getConfig($this->fullname);
        }else{
            $this->data = array();
        }
    }

    public function getData(){
        return $this->data;
    }

    public function set($key, $val){
        $this->set_is_change();
        return $this->data[$key]    = $val;
    }

    public function get($key){
        return $this->data[$key];
    }

    protected function set_is_change(){
        $this->is_change = true;
    }

    public function setData($data){
        $this->set_is_change();
        return $this->data = $data;
    }

    public function save(){
        $this->config->save($this->fullname, $this->data);
        $this->is_change = false;
    }

    public function __destruct(){
        if($this->is_change === true){
            $this->save();
        }
    }

    public function delete_file(){
        $this->config->delete_file($this->fullname);
    }



}


