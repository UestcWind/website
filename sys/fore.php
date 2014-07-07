<?php
require 'system_base.php';
$op=$_GET['op'];
$check_op=$main->checkRights($op);
if($check_op['s']!='2'){
	if(($check_op['s'])){
		$status=$main->checkUserStatus();
		if($status['s']){
			$return=array('s'=>'1','r'=>'还未登录');
			die(rstr_encode($return));
		}else{
			$return=array('s'=>'1','r'=>$check_op['r']);
			die(rstr_encode($return));
		}
	}
}else{
	$return=array('s'=>'1','r'=>$check_op['r']);
	die(rstr_encode($return));
}
switch($op){
	//用户
	case 'login':
		$return=verifyLogin();
		break;
	case 'logout':
		$return=logout();
		break;	
	case 'checkUserStatus':
		$return=$main->checkUserStatus();
		break;			
	case 'memberView':
		$return=memberView();
		break;	
	case 'memberList':
		$return=memberList();
		break;								
	case 'memberNew':
		$return=MemberNew();
		break;
	/*case 'memberDel':
		$return=MemberDel();
		break;		*/	
	case 'memberEdt':
		$return=memberEdt();
		break;				
	case 'freezeUser':
		$return=freezeUser();
		break;	
	case 'unfreezeUser':
		$return=unfreezeUser();
		break;					

	case 'changeMyPassword':
		$return=changeMyPassword();
		break;				
	case 'editMyInfo':
		$return=editMyInfo();
		break;						
	//文章	
	case 'cancelHot':
		$return=cancelHot();
		break;
		
	case 'hotArticle':
		$return=hotArticle();
		break;
	case 'cancelNew':
		$return=cancelNew();
		break;
		
	case 'newArticle':
		$return=newArticle();
		break;		
		
	case 'cancelFromMainPage':
		$return=cancelFromMainPage();
		break;
		
	case 'addToMainPage':
		$return=addToMainPage();
		break;
														
	case 'articlePst':
		$return=articlePst();
		break;
	case 'articleEdt':
		$return=ArticleEdt();		
		break;	
	case 'articleDel':
		$return=articleDel();
		break;	
					
	case 'viewArticleByID':
		$return=viewArticleByID();
		break;
	case 'listArticles':
		$return=listArticles();
		break;	
	case 'backListArticles':
		$return=backListArticles();
		break;	
	case 'articleSearch':
		$return=articleSearch();
		break;			
		
	case 'moveCate':
		$id=$_GET['id'];
		$cate=(int)$_GET['cate'];
		$return=moveCate($id,$cate);
		break;				
	//分类
	case 'categoryPst':
		$return=categoryPst();
		break;
	case 'categoryEdt':
		$return=categoryEdt();
		break;
	case 'categoryDel':
		$return=categoryDel();
		break;				
	case 'categoryMng':
		$return=categoryMng();
		break;	
		
	//留言
	case 'msgNew':
		$return=msgNew();
		break;
	case 'msgReply':
		$return=msgReply();
		break;
	case 'msgDelete':
		$return=msgDelete();
		break;
	case 'viewMsgs':
		$return=viewMsgs();
		break;
	case 'backViewMsgs':
		$return=backViewMsgs();
		break;				
	//附件模块
	case 'addImageInfo':
		$return=addImageInfo();
		break;
	case 'listImages':
		$return=listImages();
		break;		
	case 'deleteImages':
		$return=deleteImages();
		break;		
	//页面模块
	case 'createModule':
		$return=createModule();
		break;
	case 'modifyModule':
		$return=modifyModule();
		break;
	case 'deleteModule':
		$return=deleteModule();
		break;
	case 'getModuleById':
		if(!empty($_GET['id']))	$id=(int)$_GET['id'];
		$return=$main->getModuleById($id);
		break;			
	case 'moduleList':
		$return=moduleList();
		break;							
	case 'addToLogModule':
		$return=addToLogModule();
		break;
	case 'deleteFromLogModule':
		$return=deleteFromLogModule();
		break;	
	case 'showFromLogModule':
		$return=showFromLogModule();
		break;
	case 'clearFromLogModule':
		$return=clearFromLogModule();
		break;						
	case 'moduleMng':
		$return=moduleMng();
		break;			
								
									
	default:
		$return=array('s'=>'1','r'=>'无效的操作!');
		break;	
}
ob_end_flush();
die(rstr_encode($return));
?>