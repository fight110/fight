<?php 

/*
$ExcelCsv    = new ExcelCsv("Excel文件名");
$ExcelCsv->row(array("one", 2, 3, 4));
*/

 class ExcelCsv {
    public function __construct($filename, $options=array()){
        $this->response = Flight::response();
        $this->filename = $filename;
        $this->options  = $options;
        $this->start();
    }

    private function header(){
        $Response       = $this->response;
        $Response->header("Content-type","application/vnd.ms-excel");
        $Response->header("Content-Disposition", "attachment; filename={$this->filename}.csv");
    }

    private function start(){
        if(true !== $this->options['ignore_header']){
            $this->header();
        }
        $this->setTitles();
    }

    private function setTitles(){
        $titles     = $this->options['titles'];
        if(is_array($titles)){
            $this->row($titles, $options);
        }
    }

    private function end(){
    }

    public function row($row, $options=array()){
        echo iconv("utf-8",'gbk', implode(",", $row)), "\n";
    }
    
    public function __destruct(){
        $this->end();
    }

}


