<?php 
 
 require DOCUMENT_ROOT . "lib/htmlpurifier/HTMLPurifier.auto.php";

 class RemoveXss {
    public function __construct(){
        $config             = HTMLPurifier_Config::createDefault();
        $this->purifier     = new HTMLPurifier($config);
    }
    public function remove($value){
        return $this->purifier->purify($value);
    }
}


