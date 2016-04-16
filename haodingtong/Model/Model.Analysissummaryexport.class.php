<?php

class Analysissummaryexport  {

    public function __construct ($name, $titles, $data){
        $this->data     = $data;
        $ExcelWriter    = new ExcelWriter($name, $options=array("titles"=>$titles));
        $this->ExcelWriter  = $ExcelWriter;
        $this->init();
    }

    public function init (){
        $list   = $this->data['list'];
        foreach($list as $row){
            $data   = array();
            $data[] = $row['group1_name'];
            $data[] = $row['group2_name'];
            $data[] = $row['pnum'];
            $data[] = $row['sku'];
            $data[] = $row['num'];
            $data[] = $row['price'];
            $data[] = $row['percent_num'];
            $this->ExcelWriter->row($data);
        }
    }
}




