<?php

class Message Extends BaseClass {
    public function __construct($id=null){
        $this->setFactory('message');
        if(is_numeric($id)){
            $this->setAttribute($this->findone("id={$id}"));
        }
    }

    public function create_message($authorid, $author, $title,$message){
        return $this->create(array('authorid'=>$authorid, 'author'=>$author, 'title'=>$title,'message'=>$message))->insert();
    }



}




