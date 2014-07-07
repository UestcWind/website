<?php
class Theme{
	public $table='%pre%theme';
	
	public $id;
	public $authorId;
	public $content;
	public $type;
	
	function __construcor($theme){
		if(is_array($theme)){
			$this->id=$theme['thm_ID'];
			$this->parentId=$theme['thm_Author'];
			$this->status=$theme['thm_Content'];
			$this->content=$theme['thm_Type'];
		}else{
			throw new Exception('no valid theme data was supplied!');
		}
	}
}

?>