<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: Page.class.php 2712 2012-02-06 10:12:49Z liu21st $

class Util_Page {
	public $prefix_url ;
    // 分页栏每页显示的页数
    public $rollPage = 5;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 默认列表每页显示行数
    public $listRows = 10;
    // 起始行数
    public $firstRow	;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =	array('header'=>'','prev'=>'<','next'=>'>','theme'=>'%header% %upPage% %linkPage%  %downPage%');
    // 默认分页变量名
    protected $varPage;

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows='',$url ='',$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->varPage = getConfig('page', 'page_name') ? getConfig('page', 'page_name')  : 'p' ;
        if(!empty($listRows)) {
            $this->listRows = intval($listRows);
        }
        $this->prefix_url = $url;
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function show()
    {
    	if(0 == $this->totalRows) return '';
    	$p = $this->prefix_url.$this->varPage;
    	$nowCoolPage      = ceil($this->nowPage/$this->rollPage);
    	//上下翻页字符串
    	$upRow   = $this->nowPage-1;
    	$downRow = $this->nowPage+1;
    	if ($upRow>0){
    		$upPage = '<li class="prev"><a href="'.$p.'/1" title="First">首页</a></li>';
    		$upPage .= '<li class="prev"><a href="'.$p.'/'.$upRow.'" title="First">上页</a></li>';
    	}else{
    		$upPage = '<li class="prev"><a href="'.$p.'/1" title="First">首页</a></li>';
    	}
    	if ($downRow <= $this->totalPages){
    		$downPage = '<li class="next disabled"><a href="'.$p.'/'.$downRow.'" title="Next">下页</a></li>';
    		$downPage .= '<li class="next disabled"><a href="'.$p.'/'.$this->totalPages.'" title="Last">尾页</a></li>';
    	}else{
    		$downPage = '<li class="next disabled"><a href="javascript:void(0);" title="Last">尾页</a></li>';
    	}
    
    	if($nowCoolPage == 1){
    		$theFirst = "";
    		$prePage = "";
    	}else{
    		$preRow =  $this->nowPage-$this->rollPage;
    		$prePage = '<li class="prev"><a href="'.$p.'/'.$preRow.'" title="First">上'.$this->rollPage.'页</a></li>';
    	}
    	if($nowCoolPage == $this->coolPages){
    		$nextPage = "";
    		$theEnd="";
    	}else{
    		$nextRow = $this->nowPage+$this->rollPage;
    		$theEndRow = $this->totalPages;
    		$nextPage = '<li class="next disabled"><a href="'.$p.'/'.$nextRow.'" title="Next">下'.$this->rollPage.'页</a></li>';
    	}
    	// 1 2 3 4 5
    	$linkPage = "";
    	for($i=1;$i<=$this->rollPage;$i++){
    		$page=($nowCoolPage-1)*$this->rollPage+$i;
    		if($page!=$this->nowPage){
    			if($page<=$this->totalPages){
    				$linkPage .= '<li><a href="'.$p.'/'.$page.'">'.$page.'</a></li>';
    			}else{
    				break;
    			}
    		}else{
    			if($this->totalPages != 1){
    				$linkPage .= '<li class="active"><a href="'.$p.'/'.$page.'">'.$page.'</a></li>';
    			}
    		}
    	}
    	$pageStr	 =	 str_replace(
    			array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%prePage%','%linkPage%','%nextPage%'),
    			array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$prePage,$linkPage,$nextPage),$this->config['theme']);
    	// 不足2页时，返回空
    	if ($this->totalRows <= $this->listRows) {
    		return '';
    	}
    
    	return $pageStr;
    }
}