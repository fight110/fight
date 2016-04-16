<?php 
 
 class Pager {
    const   DEFAULTSTYLE        = 1;   //动态地址
    const   STYLE2              = 2;    //前台分页
    const   STYLE3              = 3;    //伪静态 request => {p => 1, rule => '/action-[p]/'}
    const   STYLE_GET           = 'get';
    public function __construct($style, $request, $total, $eachPageNum=10, $showPage=5){
        $methodname     = "init_{$style}";
        $params         = array($request, $total, $eachPageNum, $showPage);
        call_user_func_array(array($this, $methodname), $params);
    }

    public static function build($style, $request, $total, $eachPageNum=10, $showPage=5){
        $pager  = new Pager($style, $request, $total, $eachPageNum, $showPage);
        return $pager->getPageList();
    }

    public function init_1($request, $total, $eachPageNum, $showPage){
        $p      = $request->p;
        foreach($request as $key => $val){
            if($key != 'p')
                $queryArray[] = urlencode($key) ."=". urlencode($val);
        }
        $queryArray[]   = "p=";
        $query      = implode('&', $queryArray);
        $page       = new SubPage($eachPageNum, $total, $p, $showPage, "?$query"); 
        $this->pagelist     = $page->show_SubPages(2);
    }

    public function init_2($request, $total, $eachPageNum, $showPage){
        $p      = $request->p;
        foreach($request as $key => $val){
            if($key != 'p')
                $queryArray[] = urlencode($key) ."=". urlencode($val);
        }
        $queryArray[]   = "p=";
        $query      = implode('&', $queryArray);
        $page       = new SubPage($eachPageNum, $total, $p, $showPage, "?$query"); 
        $this->pagelist     = $page->show_SubPages(3);
    }

    public function init_3($request, $total, $eachPageNum, $showPage){
        $p      = $request['p'];
        $query  = $request['rule'];
        $page   = new SubPage($eachPageNum, $total, $p, $showPage, $query);
        $this->pagelist     = $page->show_SubPages(4);
    }

    public function getPageList(){
        return $this->pagelist;
    }
    
    public function init_get($request, $total, $eachPageNum, $showPage){
        $p      = $request->p;
        $queryArray = array();
        foreach($request as $key => $val){
            if($key != 'p')
                $queryArray[] = " data-".$key."='".$val."' ";
        }
        $query      = implode(' ', $queryArray);
        $page       = new SubPage($eachPageNum, $total, $p, $showPage, $query);
        $this->pagelist     = $page->show_SubPages('Get');
        
    }
}


