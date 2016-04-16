<?php

abstract class BaseClassFile {
    protected $filename, $File;

    abstract public function setFilename();

    public function __construct(){
        $this->setFilename();
        if(!$this->filename){
            throw new Exception("BaseClassFile abstract function setFilename undefined");
        }
        $this->File     = new ConfigData($this->filename);
    }

    public function getData(){
        return $this->File->getData();
    }

    public function __call($name, $argument){
        $callback   = array($this->File, $name);
        if(is_callable($callback)){
            return call_user_func_array($callback, $argument);
        }
        return null;
    }

    public function __get($name){
        return $this->File->get($name);
    }

    public function __set($name, $val){
        return $this->File->set($name, $val);
    }

}

