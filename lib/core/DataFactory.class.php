<?php

class DataFactory {
    private static $_objects = array();
    public static function getInstance($tablename){
        $instance   = STATIC::$_objects[$tablename];
        if($instance)
            return $instance;

        return STATIC::$_objects[$tablename] = new DataFactory($tablename);
    } 
    private function __construct($tablename){
        $this->tablename    = $tablename;
        $this->saver        = new SaverMysql($tablename);
    }
    public function create($data=array()){
        return DataObject::createInstance($data, array('parent' => $this));
    }
    public function select($param){
        return DataObject::selectInstance($param, array('parent' => $this));
    }
}

class DataObject {
    private static $_instances = array();
    private $_parent, $_saver, $_data;
    private function __construct($data, $options){
        $data               = (array) $data;
        $this->_parent      = $options['parent'];
        $this->_saver       = $this->_parent->saver;
        $this->_data        = $data;
    }
    public static function selectInstance($param, $options){
        // if(is_numeric($param)){
        //     $id     = $param;
        //     if(STATIC::$_instances[$id]){
        //         return STATIC::$_instances[$id];
        //     }
        // }
        $saver  = $options['parent']->saver;
        $data   = $saver->select($param);

        return STATIC::createInstance($data, $options);
    }
    public static function createInstance($data, $options){
        $instance   = new DataObject($data, $options);
        if($instance->id){
            STATIC::$_instances[$instance->id]  = $instance;
        }
        return $instance;
    }
    public function insert($isDuplicate=false){
        $data   = $this->_data;
        $id     = $this->_saver->insert($data, $isDuplicate);
        if($id){
            $this->id   = $id;
            STATIC::$_instances[$this->id]  = $this;
        }
        return $id;
    }
    public function update(){
        if(!$this->id)  return false;
        return $this->_saver->update($this->id, $this->_data);
    }
    public function delete(){
        if(!$this->id)  return false;
        return $this->_saver->delete($this->id);
    }
    public function __get($name){
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        return null;
    }
    public function __set($name, $value){
        return $this->_data[$name] = $value;
    }
    public function getData(){
        return $this->_data;
    }
}

