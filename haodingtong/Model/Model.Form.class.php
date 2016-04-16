<?php

class Form {
    public function __construct($source, $name){
        $this->source       = (object) $source;
        $configreader       = new ConfigReader;
        $this->validation   = $configreader->getConfig("Form/{$name}.json");
        if($this->validation === NULL) throw new Exception("Form Configure File explain error:Form/{$name}.json");
        $this->xss          = new RemoveXss;
    }
    public function run($TYPE=""){
        $result     = array('valid'=>true);
        $target     = array();
        foreach($this->validation as $validation){
            $name           = $validation['name'];
            $type           = $validation['type'];
            $configure      = $validation['method'];
            $this->value    = $this->source->$name;
            if($type && $TYPE && false === strpos($type, $TYPE)) continue;

            foreach($configure as $method => $option){
                $method_name    = "_{$method}";
                if(!method_exists($this, $method_name)){
                    throw new Exception("Form Validation Method [{$method}] is undefined");
                }
                $param  = $option['param'] ? $option['param'] : array();
                if(false === call_user_func_array(array($this, $method_name), $param)){
                    $result['valid']    = false;
                    $result['message']  = $option['message'];
                    break 2;
                }
            }
            $target[$name]  = $this->value;
        }
        $result['target']   = $target;
        return $result;
    }
    private function _required(){
        return strlen($this->value) ? true : false;
    }
    private function _min_length($n){
        return strlen($this->value) >= $n ? true : false;
    }
    private function _max_length($n){
        return strlen($this->value) <= $n ? true : false;
    }
    private function _url(){
        if(!$this->value)   return true;
        return preg_match("/^https?:\/\/.+/i", $this->value) ? true : false;
    }
    private function _email(){
        if(!$this->value)   return true;
        return preg_match("/^[\w.\-]+@[\w.]+/", $this->value) ? true : false;
    }
    private function _phone(){
        if(!$this->value)   return true;
        return preg_match("/^\d{11}$/", $this->value) ? true : false;
    }
    private function _integer(){
        if(!$this->value)   return true;
        return is_numeric($this->value);
    }
    private function _xss(){
        $this->value    = $this->xss->remove($this->value);
        return true;
    }
    private function _NOW(){
        $this->value    = date('Y-m-d H:i:s');
        return true;
    }
    private function _IP(){
        $this->value    = Flight::IP();
        return true;
    }
    private function _html(){
        $this->value    = htmlspecialchars($this->value);
        return true;
    }
    private function _referrer(){
        $this->value    = Flight::request()->referrer;
        return true;
    }
}




