<?php

class HDTImportProductColorGroup {
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
        $data['color_group']    = trim($row[$rid++]);
        $data['rgb']            = trim($row[$rid++]);
        $unit_list              = array();
        while($unit = trim($row[$rid++])){
            $color_list    = explode(";", $unit);
        }
        $data['color_list']     = array_unique($color_list);
        $this->data             = $data;
    }

    public function error ($message) {
        $this->error_list[] = $message; 
    }

}




