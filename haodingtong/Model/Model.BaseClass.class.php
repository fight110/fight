<?php

abstract class BaseClass Extends Collection {
    private $_children      = array();
    private $_attributes    = array();

    public function register($name, $classname, $join){
        $this->_children[$name] = array($classname, $join);
    }
    public function __get($name){
        if(array_key_exists($name, $this->_children)){
            list($classname, $join) = $this->_children[$name];
            $instance = new $classname($join);
            return $this->$name    = $instance;
        }
        if(array_key_exists($name, $this->_attributes)){
            return $this->_attributes[$name];
        }
        return null;
    }
    public function save(){
        if(!$this->id)  return false;
        $result = array();
        foreach($this->_attributes as $key => $val){
            if(property_exists($this, $key) && $val != $this->$key){
                $result[$key]   = $this->$key;
            }
        }
        if(count($result))  $this->update($result, "id={$this->id}");
    }
    public function setAttribute($data, $isProperty=false){
        if($isProperty){
            foreach($data as $key => $val){
                if(!array_key_exists($key, $this->_attributes)){
                    $this->_attributes[$key]    = '';
                }
                $this->$key                 = $val;
            }
        }else{
            foreach($data as $key => $val){
                $this->_attributes[$key]    = $val;
            }
        }
        
    }
    public function getAttribute(){
        return $this->_attributes;
    }

    public function get($whereString="", $options=array()){
        $newOptions = array('limit' => 1) + $options;
        $list       = $this->find($whereString, $newOptions);
        if($list[0]){
            return $this->cloneAttr($list[0]);
        }
        return $this;
    }
    public function cloneAttr($attributes){
        $that   = clone $this;
        $that->setAttribute($attributes);
        if(method_exists($that, 'init')){
            $that->init();
        }
        return $that;
    }
}



