<?php
/**
 * 分页类
 * @author jokechat
 * @2016年6月12日
 * @下午1:32:41
 * @email jokechat@qq.com
 */
class Page 
{
	/**
	 * 总记  
	 * 
	 @var string */
	private $total; 
	
	/**
	 * @var number
	 */
	public  $pagesize = 20; // 每页显示多少条
	private $limit; // limit
	public  $page; // 当前页码
	public  $pagenum; // 总页码
	private $url; // 地址
	private $bothnum; // 两边保持数字分页的量
	
	public  $start = 0;//数据库查询起始行数
	                  
	/**
	 * 
	 * @param number $_total 数据总条数
	 * @param number $_pagesize 每页显示条数 默认20
	 */
	public function __construct($_total, $_pagesize = 20) 
	{
		$this->total 	= $_total ? $_total : 1;
		$this->pagesize = $_pagesize;
		$this->pagenum 	= ceil ( $this->total / $this->pagesize );
		$this->page 	= $this->setPage ();
		$this->limit 	= "LIMIT " . ($this->page - 1) * $this->pagesize . ",$this->pagesize";
		$this->url 		= $this->setUrl ();
		$this->bothnum 	= 2;
		
		$this->start 	= ($this->page-1)*($this->pagesize);
	}
	
	
	// 获取当前页码
	private function setPage() 
	{
		if (! empty ( $_REQUEST ['page'] )) {
			if ($_REQUEST ['page'] > 0) {
				if ($_REQUEST ['page'] > $this->pagenum) {
					return $this->pagenum;
				} else {
					return $_REQUEST ['page'];
				}
			} else {
				return 1;
			}
		} else {
			return 1;
		}
	}
	
	// 获取地址
	private function setUrl() 
	{
		$_url = $_SERVER ["REQUEST_URI"];
		$_par = parse_url ( $_url );
		if (isset ( $_par ['query'] )) {
			parse_str ( $_par ['query'], $_query );
			unset ( $_query ['page'] );
			$_url = $_par ['path'] . '?' . http_build_query ( $_query );
		}
		return $_url;
	} 
	
	// 数字目录
	private function pageList() 
	{
		$_pagelist 	= "";
		for($i = $this->bothnum; $i >= 1; $i --) {
			$_page = $this->page - $i;
			if ($_page < 1)
				continue;
			$_pagelist .= ' <a href="' . $this->url . '&page=' . $_page . '">' . $_page . '</a> ';
		}
		$_pagelist .= ' <span class="me">' . $this->page . '</span> ';
		for($i = 1; $i <= $this->bothnum; $i ++) {
			$_page = $this->page + $i;
			if ($_page > $this->pagenum)
				break;
			$_pagelist .= ' <a href="' . $this->url . '&page=' . $_page . '">' . $_page . '</a> ';
		}
		return $_pagelist;
	}
	
	// 首页
	private function first() 
	{
		if ($this->page > $this->bothnum + 1) {
			return ' <a href="' . $this->url . '">1</a> ...';
		}
	}
	
	// 上一页
	private function prev() 
	{
		if ($this->page == 1) {
			return '<span class="disabled">上一页</span>';
		}
		return ' <a href="' . $this->url . '&page=' . ($this->page - 1) . '">上一页</a> ';
	}
	
	// 下一页
	private function next() 
	{
		if ($this->page == $this->pagenum) {
			return '<span class="disabled">下一页</span>';
		}
		return ' <a href="' . $this->url . '&page=' . ($this->page + 1) . '">下一页</a> ';
	}
	
	// 尾页
	private function last() 
	{
		if ($this->pagenum - $this->page > $this->bothnum) {
			return ' ...<a href="' . $this->url . '&page=' . $this->pagenum . '">' . $this->pagenum . '</a> ';
		}
	}
	
	/**
	 * 显示的分页信息
	 * @return string
	 */
	public function showpage() 
	{
		$_page 	= "";
		$_page .= $this->first ();
		$_page .= $this->pageList ();
		$_page .= $this->last ();
		$_page .= $this->prev ();
		$_page .= $this->next ();
		return $_page;
	}
	
	/**
	 * 设置总条数
	 * @param number $total
	 */
	public function setTotal($total)
	{

		$this->total 	= $total ? $total : 1;
		$this->pagenum 	= ceil ( $this->total / $this->pagesize );
		$this->page 	= $this->setPage ();
		$this->limit 	= "LIMIT " . ($this->page - 1) * $this->pagesize . ",$this->pagesize";
		$this->url 		= $this->setUrl ();
		$this->bothnum 	= 2;
		$this->start 	= ($this->page-1)*($this->pagesize);
	}
}
?>