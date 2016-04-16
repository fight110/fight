<?php

class FabricMoq Extends BaseClass {
    public function __construct(){
        $this->setFactory('fabric_moq');
    }

    public function setMoq($fabric_id, $color_id, $minimum){
        $moq    = $this->create(array('fabric_id'=>$fabric_id, 'color_id'=>$color_id, 'minimum'=>$minimum));
        return $moq->insert(true);
    }
    
}




