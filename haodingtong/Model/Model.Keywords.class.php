<?php

class Keywords Extends BaseClass {
    private $_keyword_cache = array();
    private static $_data;
    private static $_cache_obj;
    private static $_cache_id_obj;

    public function __construct(){
        $this->setFactory('keywords');
        $this->data     = $this->get_data();
    }
    
    public function getKeywordId($name){
        $name   = trim($name);
        $k  = $this->findone("name='".addslashes($name)."'");
        if(!$k['id']){
            $k  = $this->create(array('name'=>$name));
            $k->insert();
            $k  = $k->getData(); 
        }
        return $k['id'];
    }

    public function getKeywordName($id){
        $name   = $this->_keyword_cache[$id];
        if(!$name){
            $k  = $this->findone("id={$id}");
            $k['name']  = preg_replace("/\#.*$/", "", $k['name']);
            $this->_keyword_cache[$id]  = $k['name'];
            $name   = $k['name'];
        }
        return $name;
    }

    public function getName_File($id){
        $name   = $this->data->get($id);
        if(!$name){
            $name   = $this->getKeywordName($id);
            $this->data->set($id, $name);
        }
        return $name; 
    }

    public function delete_file(){
        $this->data->delete_file();
    }

    private function get_data(){
        if(!STATIC::$_data){
            STATIC::$_data = new ConfigData('Keywords');
        }
        return STATIC::$_data;
    }


    public static function cache_get($string){
        if(!STATIC::$_cache_obj){
            $Keywords   = new Keywords;
            STATIC::$_cache_obj = Cache::setCacheCallback(function($kid) use ($Keywords){
                return $Keywords->getKeywordName($kid);
            });
        }
        $cache  = STATIC::$_cache_obj;
        return $cache($string);
    }

    public static function cache_get_id($string){
        if(!STATIC::$_cache_id_obj){
            $Keywords   = new Keywords;
            STATIC::$_cache_id_obj = Cache::setCacheCallback(function($kid) use ($Keywords){
                return $Keywords->getKeywordId($kid);
            });
        }
        $cache  = STATIC::$_cache_id_obj;
        return $cache($string);
    }

}




