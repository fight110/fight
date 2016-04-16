<?php

define('HDT_CACHE_DATA_PATH', DOCUMENT_ROOT . 'cache/cache_data/');
if(!is_dir(HDT_CACHE_DATA_PATH)) {
    mkdir(HDT_CACHE_DATA_PATH, 0755, true);
}
class Cache {
    private $callback, $hash = array();
    public function __construct($callback, $timeout=0){
        if(!is_callable($callback)){
            throw new Exception("Cache Params Error");
        }

        $this->callback     = $callback;
        $this->timeout      = $timeout;
        if($timeout > 0){
            $this->Cache2File   = true;
        }
    }

    public function get($key, $params=array()){
        $result = $this->hash[$key];
        if(!isset($result)){
            if($this->Cache2File){
                $file   = $this->getCacheFile($key);
                if(is_file($file)) $data   = require $file;
                if(is_array($data) && $data['time'] + $this->timeout > time()){
                    $result     = $data['result'];
                    $this->hash[$key]   = $result;
                }
            }
            
            if(!isset($result)){
                $result     = $this->set($key, $params);
            }
        }
        return $result;
    }

    public function getCacheFile($key){
        return HDT_CACHE_DATA_PATH . $key . '.php';
    }

    public static function getCacheFileStatic($key){
        return HDT_CACHE_DATA_PATH . $key . '.php';
    }

    public function set($key, $params){
        $result     = call_user_func_array($this->callback, $params);
        if($this->Cache2File){
            $file       = $this->getCacheFile($key);
            $data       = array('time'=>time(), 'result'=>$result);
            file_put_contents($file, '<?php return unserialize(\'' . addslashes( serialize($data) ) . '\');');
        }
        $this->hash[$key]   = $result;
        return $result;
    }

    public static function getLocation(){
        return new Cache(function($area1, $area2){
            $Location   = new Location;
            $areaid     = $area2 ? $area2 : $area1;
            if($areaid){
                return $Location->getCurrent($areaid);
            }else{
                return "";
            }
        });
    }

    public static function setCacheCallback($func, $timeout=0){
        $cache      = new Cache($func, $timeout);
        $callback   = function($params) use ($cache){
            if(!is_array($params)){
                $string     = $params; 
                $params     = array($params);
            }else{
                $string     = implode('-', $params);
            }
            return $cache->get($string, $params);
        };
        return $callback;
    }

    public static function clearCache($CacheFileName=null) {
        if(null !== $CacheFileName) {
            $filename   = HDT_CACHE_DATA_PATH . $CacheFileName . ".php";
            if(is_file($filename)) {
                unlink($filename);
            }
        }
    }
}




