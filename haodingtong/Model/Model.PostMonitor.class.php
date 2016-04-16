<?php 
 
 class PostMonitor {
    public function __construct($namespace, $options=array()){
        $this->namespace    = 'monitor_'.$namespace;
        $this->options      = $options;
        $this->result       = array('valid'=>true, 'message'=>'');
        $this->monitor      = SESSION::get($this->namespace);
        if(!is_array($this->monitor))   $this->monitor  = array();
    }

    public function run(){
        if($this->options['intval'] > 0){
            if($this->options['intval'] > time() - $this->monitor['last_post_time']){
                return $this->setError(sprintf("间隔时间为%d秒", $this->options['intval']));
            }
        }
        if($this->options['diff']){
            if($this->options['diff'] == $this->monitor['options']['diff']){
                return $this->setError("和上次发布信息的内容相同");
            }
        }

        return $this->result;
    }

    public function save(){
        $monitor['last_post_time']  = time();
        $monitor['options']         = $this->options;
        SESSION::set($this->namespace, $monitor);
    }

    private function setError($error){
        $this->result['valid']      = false;
        $this->result['message']    = $error;
        return $this->result;
    }
}


