<?php




class SESSION {
    const DESTROYNOW    = true;
    public static function get($key, $destory=false){
        $result         = $_SESSION[$key];
        if($destory)    $_SESSION[$key] = null;
        return $result;
    }
    public static function set($key, $val){
        return $_SESSION[$key] = $val;
    }
    public static function start($time=0){
        $lifetime   = $time ? time() + $time : 0;
        $path       = '/';
        $domain     = $_SERVER['HTTP_HOST'];
        $SID1  = Flight::request()->query->SID;
        $SID2  = Flight::request()->data->SID;
        if($SID1){
            session_id($SID1);
        }elseif ($SID2){
            session_id($SID2);
        }
        session_name('SID');
        session_set_cookie_params($lifetime, $path, $domain);
        session_cache_expire($lifetime);
        session_start();
        if(!$_COOKIE['SID']){
            setcookie(session_name(),session_id(),$lifetime, $path, $domain); 
        }
    }
    public static function setSID($name, $sid, $lifetime, $path, $domain){
        setcookie(session_name(),session_id(),$lifetime, $path, $domain);
    }
    public static function getSID(){
        return session_id();
    }
    public static function destory(){
        session_destroy();
    }
    public static function setCache($key, $val, $time){
        if(!is_numeric($time)) throw new Exception("SESSION CACHE TIMEOUT WRONG");
        $cache  = array($val, time() + $time);
        STATIC::set($key, $cache);
    }
    public static function cache($key, $callback="", $timeout=60, $clearCondition=false){
        $cache_key  = "cache_{$key}";
        $cache  = STATIC::get($cache_key);
        if(isset($cache)){
            if(is_array($cache) && $cache[1] > time()){
                return $cache[0];
            }
            STATIC::delete($cache_key);
        }
        if(is_callable($callback) && is_numeric($timeout) && $timeout > 0){
            $val    = call_user_func($callback);
            if($clearCondition === false ||
                ($clearCondition !== false && is_callable($clearCondition) && false !== call_user_func($clearCondition, $val))
            ){
                STATIC::setCache($cache_key, $val, $timeout);
            }
            return $val;
        }
        return null;
    }
    public static function delete($key){
        unset($_SESSION[$key]);
    }
    public static function message($message){
        STATIC::set(SESSION_MESSAGE_KEY, $message);
    }
    public static function getmessage(){
        $message    = STATIC::get(SESSION_MESSAGE_KEY);
        STATIC::delete(SESSION_MESSAGE_KEY);
        return $message;
    }
}


