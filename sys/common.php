<?php
function usernameRE($name){//2-11个字符,字符允许字母数字和下划线，但是开头和结尾不能有下划线
	$regex='/^[a-zA-Z0-9\x{4e00}-\x{9fa5}][a-zA-Z0-9_\x{4e00}-\x{9fa5}]{0,9}[a-zA-Z0-9\x{4e00}-\x{9fa5}]$/u';
	if(preg_match($regex,$name))return true;else return false;
}
function pswRE($psw){//6-12个数字、字母组成的字符串
	$regex='/^[a-zA-Z0-9][a-zA-Z0-9]{4,10}[a-zA-Z0-9]$/';
	if(preg_match($regex,$psw))return true;else return false;
}

function birthRE($bir){//1991-09-11格式的生日
	$regex='/^\d{4}-\d{2}-\d{2}$/';
	if(preg_match($regex,$bir))return true;else return false;
}
function emailRE($email){
	$regex='/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w)*$/';
	if(preg_match($regex,$email))return true;else return false;
}
function paging($page,$count){//返回总页数,limit第一个参数,limit第二个参数
	$perpage=14;//每页显示数量
	$pages=(int)ceil($count/$perpage);
	if($page>$pages)	$page=$pages;
	$start=($page-1)*$perpage;
	if($page<$pages){
		$page=array('page'=>$pages,'limit'=>array($start,$perpage));
	}else{
		$last_amount=$count-($pages-1)*$perpage;
		$page=array('page'=>$pages,'limit'=>array($start,$last_amount));
	}
	return $page;	
}
function memSortMode($mode){
	switch($mode){
		case '0':
		
		break;
		
		default:
		break;	
	}	
	
}
function logSortMode($mode){
	switch($mode){
		case 0:
			$order=array('log_CreateDate'=>'DESC');
		break;
		
		case 1:
			$order=array('log_AuthorID'=>'ASC','log_CreateDate'=>'DESC');
		break;		
		
		case 2:
			$order=array('log_CateID'=>'ASC','log_CreateDate'=>'DESC');
		break;	
		
		default:
			$order=array('log_CreateDate'=>'DESC');
		break;		
	}
	return $order;
}

?>