<?php 
 
 class ConfigReader {
    private $path;
    public function __construct(){
        $this->path = DOCUMENT_ROOT . PROJECT_NAME . "/Config/";
    }

    public function getConfig($filename){
        $fullname   = $this->getFileFullName($filename);
        if(is_readable($fullname)){
            $result = json_decode(file_get_contents($fullname), true);
            return $result;
        }
        throw new Exception("Config File [{$filename}] is not found");
    }

    public function is_file_exists($filename){
        $fullname   = $this->getFileFullName($filename);
        return is_readable($fullname) ? true : false;
    }

    public function getFileFullName($filename){
        return $this->path . $filename;
    }

    public function save($filename, $data){
        $fullname   = $this->getFileFullName($filename);
        file_put_contents($fullname, json_encode($data));
    }

    public function delete_file($filename){
        $fullname   = $this->getFileFullName($filename);
        if(file_exists($fullname)){
            unlink($fullname);
        }
    }

}


