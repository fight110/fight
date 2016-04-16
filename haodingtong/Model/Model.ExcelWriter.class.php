<?php 

/*
$ExcelWriter    = new ExcelWriter("Excel文件名");
$ExcelWriter->row(array("one", 2, 3, 4));
$ExcelWriter->row(array(array("data"=>"one", "type"=>"String"), 2, 3, 4));
*/
require_once DOCUMENT_ROOT . 'lib/PHPExcel/PHPExcel.php';
require_once DOCUMENT_ROOT . 'lib/PHPExcel/PHPExcel/Writer/Excel5.php';
require_once DOCUMENT_ROOT . 'lib/PHPExcel/PHPExcel/Writer/Excel2007.php';
 
 class ExcelWriter {
    private  $option_default_config = array(
        'color'=>'PHPExcel_Style_Color::COLOR_BLACK',
        'align'=>'PHPExcel_Style_Alignment::HORIZONTAL_LEFT',
        'valign'=>'PHPExcel_Style_Alignment::VERTICAL_CENTER',
        'fill'=>'PHPExcel_Style_Color::COLOR_WHITE',
    );
    public function __construct($filename, $options=array()){
        $this->response = Flight::response();
        $this->filename = $filename;
        $this->options  = $options + array("PageLimit" => 60000,'excelType'=>5);
        $this->currentPage  = 1;
        $this->currentRow   = 0;
        $this->excel        = new PHPExcel();
        $this->start();
    }

    private function header(){
        $Response       = $this->response;
        $Response->header("Content-type","application/vnd.ms-excel");
        $Response->header("Content-Disposition", "attachment; filename={$this->filename}.".($this->options['excelType']==5?'xls':'xlsx'));
    }

    private function start(){
        if(true !== $this->options['ignore_header']){
            $this->header();
        }
        $this->excel->setActiveSheetIndex(0);
        if($height  = $this->options['height']){
            $this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($height);
        }
        $this->setTitles();
    }

    private function setTitles(){
        $titles     = $this->options['titles'];
        if(is_array($titles)){
            if($title_height = $this->options['title_height']){
                $options['height']  = $title_height;
            }
            $this->row($titles, $options);
        }
    }

    private function end(){
        if($this->currentPage > 1){
            $this->excel->setActiveSheetIndex(0);
        }
        if($this->options['excelType']==5){
            $objWriter = new PHPExcel_Writer_Excel5($this->excel);
        }else{
            $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
        }
        
        $objWriter->save('php://output');
    }

    public function row($row, $options=array()){
        if(!is_array($row)){
            throw new Exception("Model.ExcelWriter添加行格式不正确:$row");
        }

        $n = ++$this->currentRow;
        foreach($row as $key => $unit) {
            $this->unit($unit, $key, $options);
        }
        $height     = $options['height'];
        if($height){
            $this->excel->getActiveSheet()->getRowDimension($this->currentRow)->setRowHeight($height);
        }

        if($n >= $this->options['PageLimit']&&$this->options['PageLimit']!=0){
            $this->nextPage();
        }
    }
    public function nextPage($name=false){
        if(!$name){
            $name   = $this->options['name']    ? $this->options['name']    : 'Worksheet';
            $name   = $name . ++$this->currentPage;
        }
        $this->currentRow = 0;
        $this->excel->createSheet();
        $this->excel->setActiveSheetIndex($this->currentPage - 1);
        $this->excel->getActiveSheet()->setTitle($name);
        $this->setTitles();
    }

    public function setTitle($name){
        $this->excel->getActiveSheet()->setTitle($name?$name:'Worksheet');
    }
    
    public function newSheet($index,$name){
        $this->currentRow = 0;
        $this->excel->createSheet();
        $this->excel->setActiveSheetIndex($index);
        $this->excel->getActiveSheet()->setTitle($name?$name:'Worksheet');
    }
    
    public function refreshIndex(){
        $this->excel->setActiveSheetIndex(0);
    }
    private function unit($unit, $row, $options=array()){
        if(is_array($unit)){
            $data   = $unit['data'];
            $type   = $unit['type'];
        }else{
            $data   = $unit;
            $type   = is_numeric($data)     ? 'Number'  : 'String';
        }

        $num    = $this->currentRow;
        $key    = $this->get_Excel_line($row) . $num;
        if($type == "Image"){
            $this->setCellImage($key, $data);
        }else{
            $this->excel->getActiveSheet()->SetCellValue($key, $data);
        }
    }

    public function get_Excel_line ($i) {
        $n      = intval($i / 26);
        $l      = $i % 26;
        if($n){
            $k1 = sprintf("%c", 65 + $n - 1);
        }
        $key    = $k1 . sprintf("%c", 65 + $l);
        return $key;
    }

    public function __destruct(){
        $this->end();
    }

    public function getActiveSheet(){
        return $this->excel->getActiveSheet();
    }

    public function setCellImage($key, $image){
        if(is_file($image) && file_exists($image)){
            $objDrawing = new PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath($image);
            $objDrawing->setCoordinates($key);
            $objDrawing->setWorksheet($this->excel->getActiveSheet());
        }else{
            $this->excel->getActiveSheet()->SetCellValue($key, '');
        }
    }

    public function setCellVaule($col,$row,$val,$istring=true){
        if($istring){
            $this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow($col,$row,$val,PHPExcel_Cell_DataType::TYPE_STRING);
        }else{
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$val);
        }        
    }
    public function merge($str){
        $this->excel->getActiveSheet()->mergeCells($str);
    }

    /**
     * 设置单元格的值和属性
     * @author zhongjiangliang
     * @param string|array $key 单元格的位置
     * @param string $data 单元格的值    
     * @param array $option 单元格的属性  : 字体颜色 color 填充颜色 fill 水平对齐 align 垂直对其 valign
     * @param boolean $isexplicit 是否原样输出
     */
    public function setval($key, $data,$option=array(),$isexplicit=true){
        if(is_array($key)){
            $col = $key[0];
            $row = $key[1];
            $pCoordinate = PHPExcel_Cell::stringFromColumnIndex($col) . $row;
        }else{
            $pCoordinate = $key;
        }
        if($isexplicit){
            $this->excel->getActiveSheet()->setCellValueExplicit($pCoordinate, $data,PHPExcel_Cell_DataType::TYPE_STRING);
        }else{
            $this->excel->getActiveSheet()->SetCellValue($pCoordinate,$data);
        }
        if(sizeof($option)){
            if(isset($option['color'])){
                //red black white blue darkred darkblue green darkgreen yellow darkyellow 
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getFont()->getColor()->setARGB($this->getOptionValue($option['color'],'color'));
            };
            //general left right center centerContinuous justify 
            if(isset($option['align'])){
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getAlignment()->setHorizontal($this->getOptionValue($option['align'],'align'));
            };
            //填充色 //red black white blue darkred darkblue green darkgreen yellow darkyellow 
            if(isset($option['fill'])){
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getFill()->getStartColor()->setARGB($this->getOptionValue($option['fill'],'fill'));
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            };
            //bottom top center justify
            if(isset($option['valign'])){
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getAlignment()->setVertical($this->getOptionValue($option['valign'],'valign'));
            };
            
            if(isset($option['borderColor'])){
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getLeft()->getColor()->setARGB($this->getOptionValue($option['borderColor'],'borderColor'));
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getTop()->getColor()->setARGB($this->getOptionValue($option['borderColor'],'borderColor'));
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getBottom()->getColor()->setARGB($this->getOptionValue($option['borderColor'],'borderColor'));
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getRight()->getColor()->setARGB($this->getOptionValue($option['borderColor'],'borderColor'));
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $this->excel->getActiveSheet()->getStyle($pCoordinate)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            };
        }
    }
    
    public function getOptionValue($val,$filed){
        switch($filed){
            case 'color':
               $res =  constant('PHPExcel_Style_Color::COLOR_'.strtoupper($val));
            break; 
            case 'valign':
                $res =  constant('PHPExcel_Style_Alignment::VERTICAL_'.strtoupper($val));
            break;
            case 'align':
               $res =  constant('PHPExcel_Style_Alignment::HORIZONTAL_'.strtoupper($val));
            break;
            case 'fill':
                $res =  constant('PHPExcel_Style_Color::COLOR_'.strtoupper($val));
            break;
            case 'borderColor':             
                $res =  constant('PHPExcel_Style_Color::COLOR_'.strtoupper($val));
                $filed = 'color';
            break;
        }
        $result = $res ? $res : constant($this->option_default_config[$filed]);
        return $result;
    }
}


