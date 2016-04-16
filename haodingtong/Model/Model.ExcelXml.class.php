<?php

class ExcelXml
{

    public $header = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">', $footer = '</Workbook>';

    public $DocumentProperties = array(
        'Title' => '好订通',
        'Subject' => '好订通数据报表',
        'Author' => '好秀科技',
        'Keywords' => '好订通数据报表',
        'Description' => '好订通数据报表',
        'LastAuthor' => '好秀科技',
        'Category' => 'Reader',
        'Company' => '好秀',
        'Version' => '12.00'
    );

    public $styles = array(
        '1' => array(
            'FontName' => 'Arial',
            'Size' => '11',
            'Color' => '#ff0000',
            'Bold' => '1',
            'Italic' => '1',
            'Underline' => 'Single',
            'Horizontal' => 'Center',
            'Vertical' => 'Center',
            'WrapText' => '1'
        ),
        '2' => array('WrapText'=>'1')
    );

    public $tables = array();

    public $current_table = 0;

    public $current_row = 0;

    public $row_para = array(
        'AutoFitHeight', // int
        'Height' // int
    );

    public $cell_para = array(
        'MergeAcross', // int
        'MergeDown', // int
        'StyleID'
    );

    public $font_para = array(
        'FontName',
        'Size',
        'Color',
        'Bold',
        'Italic',
        'Underline'
    );

    public $align_para = array(
        'Horizontal',
        'Vertical',
        'WrapText'
    );

    public function getXmlHeader()
    {
        return $this->header;
    }

    public function setXmlHeader($str)
    {
        $this->header = $str;
    }

    public function getXmlFooter()
    {
        return $this->footer;
    }

    public function setXmlFooter($str)
    {
        $this->footer = $str;
    }

    /**
     *
     * @param array $array
     *            , key of $array : Title , Subject , Author , Keywords , Description , LastAuthor , Category , Company , Version
     */
    public function setDocumentPro($array = array())
    {
        $this->DocumentProperties = $array + $this->DocumentProperties;
    }

    public function getXmlDocumentPro()
    {
        $array = $this->DocumentProperties;
        return '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"><Title>' . $array['Title'] . '</Title><Subject>' . $array['Subject'] . '</Subject><Author>' . $array['Author'] . '</Author><Keywords>' . $array['Keywords'] . '</Keywords><Description>' . $array['Description'] . '</Description><LastAuthor>' . $array['LastAuthor'] . '</LastAuthor><Category>' . $array['Category'] . '</Category><Company>' . $array['Company'] . '</Company><Version>' . $array['Version'] . '</Version></DocumentProperties>';
    }

    public function createStyle($id, $option = array())
    {
        $this->styles[$id] = $option;
        return $id;
    }

    public function getStyles()
    {
        if (sizeof($this->styles)) {
            $style_str = '';
            $font_para = $this->font_para;
            $align_para = $this->align_para;
            foreach ($this->styles as $skey => $sval) {
                $font = $alignment = '';
                $font_array = $align_array = array();
                foreach ($sval as $okey => $oval) {
                    if (in_array($okey, $font_para)) {
                        $font_array[] = 'ss:' . $okey . '="' . $sval[$okey] . '"';
                    }
                    if (in_array($okey, $align_para)) {
                        $align_array[] = 'ss:' . $okey . '="' . $sval[$okey] . '"';
                    }
                }
                $font = implode(' ', $font_array);
                $alignment = implode(' ', $align_array);
                if ($font) {
                    $font = '<Font ' . $font . '/>';
                }
                if ($alignment) {
                    $alignment = '<Alignment ' . $alignment . '/>';
                }
                $style_str .= '<Style ss:ID="' . $skey . '">' . $alignment . $font . '</Style>';
            }
            $styles = '<Styles>' . $style_str . '</Styles>';
            return $styles;
        }
        return;
    }

    public function createTable($name)
    {
        $this->tables[] = array(
            'name' => $name
        );
        $this->current_table = (sizeof($this->tables) - 1);
    }

    public function createRow($option = array())
    {
        $tid = $this->current_table;
        $para = $this->row_para;
        $str = '';
        if (sizeof($option)) {
            foreach ($para as $pval) {
                if (isset($option[$pval])) {
                    $str .= ' ss:' . $pval . '="' . $option[$pval] . '" ';
                }
            }
        }
        if (isset($option['Index'])) {
            $key = ($option['Index'] - 1);
            $this->tables[$tid]['rows'][$key] = array(
                'option' => $str
            );
        } else {
            $this->tables[$tid]['rows'][] = array(
                'option' => $str
            );
            end($this->tables[$tid]['rows']);
            $key = key($this->tables[$tid]['rows']);
        }
        $this->current_row = $key;
    }

    public function createCell($val, $option = array(), $type = 'String')
    {
        $para = $this->cell_para;
        $str = '';
        if (sizeof($option)) {
            foreach ($para as $pval) {
                if (isset($option[$pval])) {
                    $str .= ' ss:' . $pval . '="' . $option[$pval] . '" ';
                }
            }
        }
        if (isset($option['Index'])) {
            $this->tables[$this->current_table]['rows'][$this->current_row]['cells'][($option['Index'] - 1)] = array(
                'value' => $val,
                'type' => $type,
                'option' => $str
            );
        } else {
            $this->tables[$this->current_table]['rows'][$this->current_row]['cells'][] = array(
                'value' => $val,
                'type' => $type,
                'option' => $str
            );
        }
    }

    public function getTables()
    {
        $str = '';
        foreach ($this->tables as $tkey => $tval) {
            $str .= '<Worksheet ss:Name="' . $tval['name'] . '"><Table>';
            $rowline = 0;
            foreach ($tval['rows'] as $rkey => $rval) {
                $row_extra_option = '';
                if (($rkey - $rowline) > 0) {
                    $row_extra_option = ' ss:Index="' . ($rkey + 1) . '" ';
                }
                $rowline = ($rkey + 1);
                
                $str .= '<Row' . $row_extra_option . $rval['option'] . '>';
                $cellline = 0;
                foreach ($rval['cells'] as $ckey => $cval) {
                    $cell_extra_option = '';
                    if (($ckey - $cellline) > 0) {
                        $cell_extra_option = ' ss:Index="' . ($ckey + 1) . '" ';
                    }
                    $cellline = ($ckey + 1);
                    
                    $str .= '<Cell ' . $cell_extra_option . $cval['option'] . '><Data ss:Type="' . $cval['type'] . '">' . $cval['value'] . '</Data></Cell>';
                }
                $str .= '</Row>';
            }
            $str .= '</Table></Worksheet>';
        }
        return $str;
    }

    public function setCell($row, $col, $val, $option = array(), $type = 'String')
    {
        $para = $this->cell_para;
        $str = '';
        if (sizeof($option)) {
            foreach ($para as $pval) {
                if (isset($option[$pval])) {
                    $str .= ' ss:' . $pval . '="' . $option[$pval] . '" ';
                }
            }
        }
        $this->tables[$this->current_table]['rows'][$row - 1]['cells'][$col - 1] = array(
            'value' => $val,
            'type' => $type,
            'option' => $str
        );
    }

    public function getTableXml($table_str = '', $name = '',$download=true ,$table_name='table')
    {   
        
        $str = $this->getXmlHeader() . $this->getXmlDocumentPro() . $this->getStyles() . ($table_str ? $table_str : $this->getTables()) . $this->getXmlFooter();
        if ($name) {
            file_put_contents($name, $str);
            if($download){   
                header("Content-Type: application/force-download");
                header("Content-Disposition:attachment;filename=".$table_name.".xml");
                if(readfile($name)){                    
                    unlink($name);
                }else{
                    return 'link';
                }              
            }
        }
        return $str;
    }

    public function setRowOption($rid, $option = array(), $tid = '')
    {
        if (! is_numeric($tid)) {
            $tid = $this->current_table;
        }
        $str = '';
        $para = $this->row_para;
        if (sizeof($option)) {
            foreach ($para as $pval) {
                if (isset($option[$pval])) {
                    $str .= ' ss:' . $pval . '="' . $option[$pval] . '" ';
                }
            }
        }
        $this->tables[$tid]['rows'][$rid]['option'] = $str;
    }

    public function setTableName($tid, $name)
    {
        $this->tables[$tid]['name'] = $name;
    }

    public function createTableToStr($str, $name)
    {
        return '<Worksheet ss:Name="' . $name . '"><Table>' . $str . '</Table></Worksheet>';
    }

    public function initTableRows()
    {
        $this->tables[$this->current_table]['rows'] = array();
    }

    public function createRowsToStr()
    {
        $str = '';
        foreach ($this->tables[$this->current_table]['rows'] as $rkey => $rval) {
            $row_extra_option = ' ss:Index="' . ($rkey + 1) . '" ';
            $str .= '<Row' . $row_extra_option . $rval['option'] . '>';
            foreach ($rval['cells'] as $ckey => $cval) {
                $cell_extra_option = ' ss:Index="' . ($ckey + 1) . '" ';
                
                $str .= '<Cell ' . $cell_extra_option . $cval['option'] . '><Data ss:Type="' . $cval['type'] . '">' . $cval['value'] . '</Data></Cell>';
            }
            $str .= '</Row>';
        }
        return $str;
    }

    public function createRowByArray($row = 1, $data = array(), $type_array = array(), $option_array = array())
    {
        foreach ($data as $akey => $aval) {
            $this->tables[$this->current_table]['rows'][$row - 1]['cells'][$akey] = array(
                'value' => $aval,
                'type' => ($type_array[$akey] ? $type_array[$akey] : 'String'),
                'option' => ($option_array[$akey] ? $option_array[$akey] : '')
            );
        }
    }
    
}
