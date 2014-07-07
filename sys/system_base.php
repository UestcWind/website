<?php
function _stripslashes(&$val) {
	if(!is_array($val)) return stripslashes($val);
	foreach($val as $k => &$v) $val[$k] = _stripslashes($v);
	return $val;
}
if(get_magic_quotes_gpc()){
	_stripslashes($_GET);
	_stripslashes($_POST);
	_stripslashes($_COOKIE);
}
ob_start();
if(file_exists('config/option.php')){
	$option_users=require('config/option.php');
}else{
	die('初始信息文件不存在!!');
}

foreach ($option_users as $key => $value) {
	$option[$key]=$value;
}
require 'system_event.php';
require 'common.php';

date_default_timezone_set($option['SC_TIME_ZONE_NAME']);


$table=array(
'Post'=> '%pre%post',
'Category'=> '%pre%category',
'Comment'=> '%pre%comment',
'Tag'=> '%pre%tag',
'Upload'=> '%pre%upload',
'Member'=> '%pre%member',
'Message'=> '%pre%message',
'Theme'=> '%pre%theme',
'Module'=> '%pre%module',
);

#定义命令
$actions=array(
	'login'=>6,
	'logout'=>6,
	'checkUserStatus'=>6,
	'admin'=>5,
	'search'=>6,
	'misc'=>6,
	'feed'=>6,
	'cmt'=>6,
	'getcmt'=>6,

	'articleEdt'=>4,
	'articlePst'=>4,
	'articleDel'=>4,
	'articlePub'=>3,
	'viewArticleByID'=>6,	
	//'topArticle'=>2,
	//'cancelTop'=>2,	
	'addToMainPage'=>2,	
	'cancelFromMainPage'=>2,
	'hotArticle'=>2,	
	'cancelHot'=>2,	
	'newArticle'=>2,	
	'cancelNew'=>2,		
	'listArticles'=>6,
	'backListArticles'=>2,	
	'articleSearch'=>6,
	'moveCate'=>4,
		
	

	'PageEdt'=>2,
	'PagePst'=>2,
	'PageDel'=>2,

	'categoryEdt'=>2,
	'categoryPst'=>2,
	'categoryDel'=>2,
	'categoryMng'=>2,
	
	'CommentEdt'=>5,
	'CommentSav'=>5,
	'CommentDel'=>5,
	'CommentChk'=>5,
	'CommentBat'=>5,
	
	'memberView'=>5,
	'changeMyPassword'=>5,
	'editMyInfo'=>5,
	'memberEdt'=>1,
	'memberPst'=>5,
	//'memberDel'=>1,
	'memberNew'=>1,	
	'memberList'=>1,
	'freezeUser'=>1,
	'unfreezeUser'=>1,
	
	'unfreezeUser'=>1,	
	'unfreezeUser'=>1,
	'unfreezeUser'=>1,
	'unfreezeUser'=>1,
	
	'msgNew'=>6,
	'msgReply'=>2,
	'msgDelete'=>2,
	'viewMsgs'=>6,
	'backViewMsgs'=>2,

	'PluginEnb'=>1,
	'PluginDis'=>1,

	'UploadPst'=>3,
	'UploadDel'=>3,
	'listImages'=>6,
	'addImageInfo'=>2,
	'deleteImages'=>2,
	
	'createModule'=>2,
	'deleteModule'=>2,
	'modifyModule'=>2,
	'getModuleById'=>6,
	'moduleList'=>2,		
	'addToLogModule'=>3,
	'deleteFromLogModule'=>3,
	'showFromLogModule'=>6,
	'moduleMng'=>2,	
	'clearFromLogModule'=>2,	

	'ThemeSet'=>1,
	'SidebarSet'=>1,
	
	'SettingSav'=>1,	

	'ArticleMng'=>4,
	'PageMng'=>2,
	'categoryMng'=>2,
	'SettingMng'=>1,
	'TagMng'=>2,
	'CommentMng'=>5,
	'UploadMng'=>3,
	'MemberMng'=>5,
	'ThemeMng'=>1,

	'articleAll'=>2,
	'pageAll'=>2,
	'categoryAll'=>2,
	'commentAll'=>2,
	'memberAll'=>1,
	'tagAll'=>2,
	'uploadAll'=>2,

	'root'=>1,
);

$main=Main::getInstance();
$main->initialize();


/*autoload*/
function __autoload($class){
	$class=strtolower($class);
	if(file_exists('lib/class.'.$class.'.inc.php')){
		require('lib/class.'.$class.'.inc.php');
	}else if(file_exists('lib/class.'.$class.'.php')){
		require('lib/class.'.$class.'.php');
	}
	else{
		die('需要包含的文件不存在!!');
	}
}

function rstr_encode($array){
	$return_str=isset($_GET['callback'])?$_GET['callback'].'('.json_encode($array).')':json_encode($array);
	return $return_str;
}
?>