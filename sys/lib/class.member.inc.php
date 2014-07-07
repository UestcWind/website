<?php
class Member{
	public $table='%pre%member';
	
	public $id;
	public $name;
	public $password;
	public $level;
	public $status;
	public $department;
	public $email;
	public $intro;
	public $articles;
	public $uploads;
	
	function __construct($member=null){
		if(isset($member)){
			if(is_array($member)){
				$this->id=$member['mem_ID'];
				$this->name=$member['mem_Name'];
				$this->password=$member['mem_Password'];
				$this->level=$member['mem_Level'];
				$this->status=$member['mem_Status'];
				$this->department=$member['mem_Department'];
				$this->email=$member['mem_Email'];
				$this->intro=$member['mem_Intro'];
			}else{
				throw new Exception('no valid member data was supplied!');
			}
		}
	}
}
?>