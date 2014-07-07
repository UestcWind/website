<?php
require 'scale.php';
$targetFolder = '/uploads/original'; 

$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	//$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;// Relative to the root
	$currentPath=str_replace('\\','/',dirname(__FILE__));
	$targetPath = $currentPath. $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
	// Validate the file type
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {
		$micoTime=microtime();
		$time=time();
		$sourceName=str_replace(' ','',$micoTime.'.'.$fileParts['extension']);
		mysql_connect("localhost",'root','root');
		mysql_select_db('schoolweb');
		mysql_query("INSERT INTO my_upload VALUES(null,'".$sourceName."','','".$time."')");
		$copyName=$currentPath.'/uploads/scale/'.iconv("UTF-8","gb2312",$sourceName);
		copy($tempFile,$currentPath.'/uploads/scale/'.iconv("UTF-8","gb2312",$sourceName));	
		(_UPLOADPIC($_FILES["Filedata"],$targetPath.'/',$sourceName));  
     	$show_pic_scal=show_pic_scal(150, 160, $copyName);  
  	    resize($copyName,$show_pic_scal[0],$show_pic_scal[1]);
		
		//move_uploaded_file($tempFile,iconv("UTF-8","gb2312", $targetFile));//这里插入了utf8字符的更改
		//move_uploaded_file($tempFile,iconv("UTF-8","gb2312", $currentPath.'/uploads/scale/'. $_FILES['Filedata']['name'])); 

		echo 'you have finish it';
	} else {
		echo 'Invalid file type.';
	}
}
?>