<?php

class Budget Extends BaseClass {
    public function __construct(){
        $this->setFactory('budget');
    }

    public function addBudget($user_id, $field, $keyword_id, $percent){
    	return $this->create(array('user_id'=>$user_id, 'field'=>$field, 'keyword_id'=>$keyword_id, 'percent'=>$percent))->insert(true);
    }



}




