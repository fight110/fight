<?php

class AuthUser extends AuthBase {
    private static $instance = null;
    public static function getInstance () {
        if(!STATIC::$instance) {
            $filename   = DOCUMENT_ROOT . 'config/auth.conf.php';
            if(file_exists($filename)){
                $options    = require $filename;
            }else{
                $options    = array();
            }
            STATIC::$instance = new AuthUser($options);
        }
        return STATIC::$instance;
    }

    public function __construct ($options=array()) {
        $this->options  = $options;
        $this->limit    = $this->options['USER_LIMIT'];
        if($this->limit) {
            $User   = new User;
            $this->current_num = $User->getCount("type=1");
        }
    }

    public function auth () {
        if($this->limit && $this->limit <= $this->current_num) {
            return $this->error("限制用户数{$this->limit}");
        }else{
            $this->current_num++;
        }
    }

}
