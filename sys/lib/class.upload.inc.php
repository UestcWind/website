<?php
class Upload{
	public $table='%pre%upload';
	
	public $id;
	public $authorId;
	public $size;
	public $name;
	public $sourceName;
	public $mimeType;
	public $postTime;
	public $downNums;
	public $intro;
	
	function __construtor($Upload){
		if(is_array($Upload)){
			$this->id=$Upload['ul_ID'];
			$this->authorId=$Upload['ul_AuthorID'];
			$this->size=$Upload['ul_Size'];
			$this->name=$Upload['ul_Name'];
			$this->sourceName=$Upload['ul_SourceName'];
			$this->mimeType=$Upload['ul_MimeType'];
			$this->postTime=$Upload['ul_PostTime'];
			$this->downNums=$Upload['ul_DownNums'];
			$this->intro=$Upload['ul_Intro'];
		}else{
			throw new Exception('no valid Upload data was supplied!');
		}
	}
}

?>