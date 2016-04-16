<?php

class HDTImportProductStock {
	private static $_keywords = array();
    public function __construct($row){
        $this->error_list   = array();
    	$this->init($row);
    }

    public function attr($key, $value=null) {
    	if(null !== $value) {
    		$this->data[$key]	= $value;
    	}
    	return $this->data[$key];
    }

    public function attrs () {
    	return $this->data;
    }

    public function init ($row) {
        $data       = array();
        $rid        = 1;
        $this->kuanhao = trim($row[$rid++]);//款号
        $this->color   = trim($row[$rid++]);//颜色
        $this->size    = trim($row[$rid++]);//尺码
        $this->totalnum= trim($row[$rid++]);//数量
    }
    
    public function error ($message) {
        $this->error_list[] = $message;
    }
    
}




