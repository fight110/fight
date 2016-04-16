<?php

abstract class AuthBase {
    public $result = array();

    public function error ($message) {
    	$this->result['error']		= 1;
    	$this->result['message']	= $message;
    	return $this->result;
    }
}




