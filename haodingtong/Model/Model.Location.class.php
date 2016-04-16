<?php

class Location Extends Category {
    public function __construct(){
        $this->factory  = DataFactory::getInstance('location');
    }
    public function getLocationChain($pid){
        return $this->getCategoryChain($pid);
    }
    public function getLocationList($where,$para){
        $chain              = $this->factory->saver->getList($where,$para);
        if(isset($para['key'])&&$para['key']){
            $result=array();
            foreach($chain as $value){
                $result[$value[$para['key']]]=$value;
            }
            return $result;
        }
        return $chain;
    }
}




