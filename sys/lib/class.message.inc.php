<?php
class Message{
	public $table='%pre%message';
	
	public $id;
	public $parentId;
	public $status;
	public $content;
	public $author;
	public $to;
	public $time;
	function __construcor($messages){
		if(is_array($messages)){
			$this->id=$messages['msg_ID'];
			$this->parentId=$messages['msg_ParentID'];
			$this->status=$messages['msg_Status'];
			$this->content=$messages['msg_Content'];
			$this->author=$messages['msg_Author'];
			$this->to=$messages['msg_To'];
			$this->time=$messages['msg_Time'];
		}else{
			throw new Exception('no valid messages data was supplied!');
		}
	}
}

?>