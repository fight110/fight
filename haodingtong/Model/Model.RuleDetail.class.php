<?php

class RuleDetail Extends BaseClass {
    public function __construct(){
        $this->setFactory('rule_detail');
    }

    public function getRuleDetailList($rule_id, $options=array()){
        if(!$options['limit']){
        	$options['limit']	= 1000;
        }
        return $this->find("rule_id={$rule_id}", $options);
    }

    public function createRuleDetail($rule_id, $data=array()){
    	foreach($data as $keyword_id => $percent){
    		$this->create(array('rule_id'=>$rule_id, 'keyword_id'=>$keyword_id, 'percent'=>$percent))->insert(true);
    	}
    }
}




