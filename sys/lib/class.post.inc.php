<?php
class NewsItem{
	public $nid;
	public $title;
	public $mainbody;
	public $create_time;
	public $modify_time;
	public $pid;
	public $category;
	public $views;
	public $comment_count;
	public $status;
	
	function __constructor($news){
		if(is_array($news)){
			$this->nid=$news['nid'];
			$this->title=$news['title'];
			$this->mainbody=$news['mainbody'];
			$this->create_time=$news['create_time'];
			$this->modify_time=$news['modify_time'];
			$this->pid=$news['pid'];
			$this->category=$news['category'];
			$this->views=$news['views'];
			$this->comment_count=$news['comment_count'];
			$this->status=$news['status'];
		}else{
			throw new Exception('no valid news data was supplied!');
		}
	}

	
}
?>