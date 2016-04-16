<?php

class BudgetCount Extends BaseClass {
    public function __construct(){
        $this->setFactory('budget_count');
    }

    public function getBudget($user_id){
        $list = $this->find("user_id={$user_id}", array());
        return $list[0]['budget'];
    }

    public function setBudget($user_id, $budget){
    	return $this->create(array('user_id'=>$user_id, 'budget'=>$budget))->insert(true);
    }

}




