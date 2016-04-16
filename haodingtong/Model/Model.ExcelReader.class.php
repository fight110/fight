<?php 
 
 require_once DOCUMENT_ROOT . "lib/Excel/oleread.inc";
 require_once DOCUMENT_ROOT . "lib/Excel/reader.php";

 class ExcelReader {
    public function __construct($file=false){
        $this->data = new Spreadsheet_Excel_Reader();
        $this->data->setOutputEncoding('UTF-8');
        if($file){
            $this->read($file);
        }
    }

    public function read($file){
        $this->data->read($file);
        $this->current_row_num  = 1;
        $this->sheets   = $this->data->sheets;
        $this->sheet    = $this->sheets[0];
    }

    public function numRows(){
        return $this->sheet['numRows'];
    }

    public function numCols(){
        return $this->sheet['numCols'];
    }

    public function cell($row, $col){
        return $this->sheet['cells'][$row][$col];
    }

    public function nextRow(){
        $current_row_num = $this->current_row_num++;
        if($current_row_num > $this->numRows()){
            $this->current_row_num = 1;
            return false;
        }
              
        return array_map(function($val){return trim($val);},$this->sheet['cells'][$current_row_num]);
        //return $this->sheet['cells'][$current_row_num];
    }
    
}


