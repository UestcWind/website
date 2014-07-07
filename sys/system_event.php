<?php
/**辅助函数
 *
*/
function isUserExist($username){
	global $main;
	$sql=$main->db->sql->Select($main->table['Member'],array('*'),array(array('=','mem_Name',$username)),null,null);
	$exc=$main->db->Query($sql);
	if($exc['s']){
		return array('s'=>'2','r'=>$exc['r']);
	}
	if(!empty($exc['data'])&&$exc['data'][0]['mem_ID']>0){
		return array('s'=>'1','r'=>'该用户已存在');
	}else{
		return array('s'=>'0','r'=>'该用户不存在');
	}
}
function isUserLocked($username){
	global $main;
	$sql=$main->db->sql->Select($main->table['Member'],array('mem_Status'),array(array('=','mem_Name',$username)),null,null);
	$exc=$main->db->Query($sql);
	if($exc['data']['0']['mem_Status'])return true;
	else return false;
} 
function isStuff($name){
	global $main;
	$sql=$main->db->sql->Select($main->table['Member'],array('*'),array(array('=','mem_Name',$name)),null,null);
	$exc=$main->db->Query($sql);
	if($exc['s'])	return false;
	if(empty($exc['data'][0]))return false;
	else return true;	
}
function isAdmin($id){
	global $main;
	$sql=$main->db->sql->Select($main->table['Member'],array('*'),array(array('=','mem_Level','0')),null,null);
		$exc=$main->db->Query($sql);
		if($exc['s']){
			return array('s'=>'2','r'=>$exc['r']);
		}
		if(!empty($exc['data'])){
			foreach($exc['data'] as $key=>$value){
				if($id==$value['mem_ID'])
				return array('s'=>'0');
			}
			return array('s'=>'1');	
		}else{
			return array('s'=>'1');
		}	
}

/**用户模块
 *
*/
function verifyLogin(){
	global $main;
	if($main->userStatus)	return array('s'=>'1','r'=>'您已经登录了，不要重复登录','d'=>$main->user);
	if(empty($_POST['username']))	return array('s'=>'1','r'=>'用户名不能为空');
	if(empty($_POST['password']))	return array('s'=>'1','r'=>'密码不能为空');	
	$isUserExist=isUserExist($_POST['username']);
	if($isUserExist['s']==2)return array('s'=>'1','r'=>$isUserExist['r']);
	if(!$isUserExist['s']){
		return array('s'=>'1','r'=>'用户名不存在');
	}
	if(isUserLocked($_POST['username']))return array('s'=>'1','r'=>'您暂时被冻结，请联系管理员咨询解决方案!');
	$verify=$main->verify($_POST['username'],$_POST['password']);
	if(!$verify['s']){
		$un=base64_encode(base64_encode($_POST['username']));
		$ps=base64_encode(base64_encode($_POST['password'].'3'));
		if($_POST['savedate']==0){
			setcookie(base64_encode("un1"), $un,0);
			setcookie(base64_encode("ps1"), $ps,0);
		}else{
			setcookie(base64_encode("un1"), $un, time()+3600*24*$_POST['savedate']);
			setcookie(base64_encode("ps1"), $ps, time()+3600*24*$_POST['savedate']);
		}
		return array('s'=>'0');
	}else{
		return array('s'=>'1','r'=>$verify['r']);
	}
}

function logout(){
	global $main;
	setcookie(base64_encode("un1"), '',time() - 3600);
	setcookie(base64_encode("ps1"), '',time() - 3600);
	$main->endSession();
	$main->user=null;
	return array('s'=>'0');
}
function memberView(){
	global $main;
	if(!is_numeric($_GET['id']))	return array('s'=>'1','r'=>'id格式错误');	
	$id=(int)$_GET['id'];
	$check=$main->checkRights('memberAll');
	if($check['s'] and $main->user->id!=$id) return array('s'=>'1','r'=>'您只能查看自己的信息');
	$member=$main->getMemberById($id);
	if($member['s']){
		return array('s'=>'1','r'=>$member['r']);
	}else{
		return array('s'=>'0','data'=>$member['data']);
	}
}
function memberList(){
	global $main;
	$order=isset($_GET['sortmode'])?memSortMode($_GET['sortmode']):null;
	$page=isset($_GET['page'])?$_GET['page']:1;
	$list=$main->getMemberNums();
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	$limit=paging($page,$list['nums']);
	$limit=$limit['limit'];//输出array(0,30)
	$members=$main->getMemberList(null,null,$order,$limit);
	if($members['s'])	return array('s'=>'1','r'=>$members['r']);
	return array('s'=>'0','count'=>$list['nums'],'data'=>$members['data']);
}

function memberNew(){//暂定为只有管理员才能添加
	global $main;
	if(!(isset($_POST['mem_Name'])&&isset($_POST['mem_Password'])&&isset($_POST['mem_Level'])&&isset($_POST['mem_Department'])&&isset($_POST['mem_Email']))){
		return array('s'=>'1','r'=>'信息提供不完整');
	}	
	if(usernameRE($_POST['mem_Name'])){
		$datainfo['mem_Name']=$_POST['mem_Name'];
	}else {
		return array('s'=>'1','r'=>'用户名格式不合要求!');
	}
	if(pswRE($_POST['mem_Password'])){
		$datainfo['mem_Password']=md5(md5($_POST['mem_Password']));
	}else {
		return array('s'=>'1','r'=>'密码格式不合要求!');
	}
	if(emailRE($_POST['mem_Email'])){
		$datainfo['mem_Email']=$_POST['mem_Email'];
	}else {
		return array('s'=>'1','r'=>'email格式不对!');
	}
	$datainfo['mem_Level']=$_POST['mem_Level'];
	$datainfo['mem_Department']=$_POST['mem_Department'];

	
	if(isset($_POST['mem_Intro']))$datainfo['mem_Intro']=$_POST['mem_Intro'];
	
	$isUserExist=isUserExist($datainfo['mem_Name']);
	if($isUserExist['s']){
		return array('s'=>'1','r'=>'该用户已经存在!');
	}	
	$sql=$main->db->sql->Insert($main->table['Member'],$datainfo);
	$insert=$main->db->Insert($sql);
	if(!$insert['s']){
		return array('s'=>'0','id'=>$insert['id']);	
	}else{
		return array('s'=>'1','r'=>$insert['r']);
	}	
}
function memberEdt(){//主要用于管理员修改用户的用户名，部门，权限，状态等信息
	global $main;
	if(!is_numeric($_GET['id']))	return array('s'=>'1','r'=>'id格式错误');	
	$datainfo=array();
	$id=(int)$_GET['id'];
	/*$check=$main->checkRights('memberAll');
	if($check['s'])	return array('s'=>'1','r'=>'对不起，管理员才能进行这个操作');	
	*/
	if(isset($_POST['mem_Name'])){
		$name=$main->getMemberById($id);
		if($name['s']){
			return array('s'=>'1','r'=>$name['r']);
		}
		if(!usernameRE($_POST['mem_Name']))	return array('s'=>'1','r'=>'用户名格式不合法。正确格式为2-11个字符,字符允许字母数字和下划线，但是开头和结尾不能是下划线');
		if($_POST['mem_Name']==$name['data']['mem_Name']){
			$datainfo['mem_Name']=$name['data']['mem_Name'];
		}else{		
			$memberList=$main->getMemberList();
			if($memberList['s'])	return array('s'=>'1','r'=>$memberList['r']);
			$memberList=$memberList['data'];
			foreach($memberList as $key=>$value){
				if($value['mem_Name']==$name['data']['mem_Name'])	continue;
				if($_POST['mem_Name']==$value['mem_Name'])return array('s'=>'1','r'=>'已经被注册了,换一个吧~');
			}
			$datainfo['mem_Name']=$_POST['mem_Name'];	
		}
	}

	if(isset($_POST['mem_Department']))	$datainfo['mem_Department']=$_POST['mem_Department'];
	if(isset($_POST['mem_Level'])){	
		$isAdmin=isAdmin($id);
		if(!$isAdmin['s'])	return array('s'=>'1','r'=>'不能修改管理员的权限');
		
		$datainfo['mem_Level']=$_POST['mem_Level'];
	}
	$sql=$main->db->sql->Update($main->table['Member'],$datainfo,array(array('=','mem_ID',$id)));
	$update=$main->db->Update($sql);
	if(!$update['s']){
		return array('s'=>'0');	
	}else{
		return array('s'=>'1','r'=>$update['r']);
	}		
}

function changeMyPassword(){
	global $main;
	if(!is_numeric($_GET['id']))	return array('s'=>'1','r'=>'id格式错误');	
	$id=(int)$_GET['id'];
	$check=$main->checkRights('memberAll');
	if($check['s'] and $main->user->id!=$id) return array('s'=>'1','r'=>'您只能更改自己的信息');
	$member=$main->getMemberById($id);
	if($member['s'])	return array('s'=>'1','r'=>$member['r']);
	if(isset($_POST['mem_Password'])){
		if(pswRE($_POST['mem_Password'])){
			$datainfo['mem_Password']=md5(md5($_POST['mem_Password']));
		}else{
			return array('s'=>'1','r'=>'密码必须由6-12个数字、字母组成');
		}
	}		
	$sql=$main->db->sql->Update($main->table['Member'],$datainfo,array(array('=','mem_ID',$id)));
	$update=$main->db->Update($sql);
	if(!$update['s']){
		return array('s'=>'0');	
	}else{
		return array('s'=>'1','r'=>$update['r']);
	}			
}
function editMyInfo(){
	global $main;
	if(!is_numeric($_GET['id']))	return array('s'=>'1','r'=>'id格式错误');	
	$id=(int)$_GET['id'];
	$check=$main->checkRights('memberAll');
	if($check['s'] and $main->user->id!=$id) return array('s'=>'1','r'=>'您只能更改自己的信息');	
	if(isset($_POST['mem_Email'])){
		if(emailRE($_POST['mem_Email'])){
			$datainfo['mem_Email']=$_POST['mem_Email'];
		}else{
			return array('s'=>'1','r'=>'email格式错误');
		}
	}	
	if(isset($_POST['mem_Intro'])){
		$datainfo['mem_Intro']=$_POST['mem_Intro'];
	}
	$sql=$main->db->sql->Update($main->table['Member'],$datainfo,array(array('=','mem_ID',$id)));
	$update=$main->db->Update($sql);
	if(!$update['s']){
		return array('s'=>'0');	
	}else{
		return array('s'=>'1','r'=>$update['r']);
	}				
}
function freezeUser(){
	global $main;	
	if(!is_numeric($_GET['id']))	return array('s'=>'1','r'=>'id格式错误');	
	$id=(int)$_GET['id'];
	$name=$main->getMemberById($id);
	if($name['s']){
		return array('s'=>'1','r'=>$name['r']);
	}	
	$check=$main->checkRights('memberAll');
	if($check['s'])	return array('s'=>'1','r'=>$check['r']);
	$isAdmin=isAdmin($id);
	if(!$isAdmin['s'])	return array('s'=>'1','r'=>'管理员不能被冻结');	
	$datainfo['mem_Status']='1';	
	$sql=$main->db->sql->Update($main->table['Member'],$datainfo,array(array('=','mem_ID',$id)));
	$update=$main->db->Update($sql);
	if(!$update['s']){
		return array('s'=>'0');	
	}else{
		return array('s'=>'1','r'=>$update['r']);
	}			
}
function unfreezeUser(){
	global $main;
	if(!is_numeric($_GET['id']))	return array('s'=>'1','r'=>'id格式错误');		
	$id=(int)$_GET['id'];
	$name=$main->getMemberById($id);
	if($name['s']){
		return array('s'=>'1','r'=>$name['r']);
	}	
	$check=$main->checkRights('memberAll');
	if($check['s'])	return array('s'=>'1','r'=>$check['r']);
	$datainfo['mem_Status']='0';	
	$sql=$main->db->sql->Update($main->table['Member'],$datainfo,array(array('=','mem_ID',$id)));
	$update=$main->db->Update($sql);
	if(!$update['s']){
		return array('s'=>'0');	
	}else{
		return array('s'=>'1','r'=>$update['r']);
	}				
}



/**新闻模块
 *
*/
function articlePst(){
	global $main;
	if(empty($_POST['log_CateID']) or empty($_POST['log_AuthorName']) or empty($_POST['log_Title']) or empty($_POST['log_Intro'])or empty($_POST['log_Content'])){
		return array('s'=>'1','r'=>'信息提供不完整');
	}
	$articleinfo['log_CateID']=$_POST['log_CateID'];
	$articleinfo['log_AuthorName']=$_POST['log_AuthorName'];
	$articleinfo['log_Title']=$_POST['log_Title'];
	$articleinfo['log_Intro']=$_POST['log_Intro'];
	$articleinfo['log_Content']=$_POST['log_Content'];
	
	$articleinfo['log_CreateDate']=time();	
	$articleinfo['log_ModifyDate']=time();	
	$main->db->db->beginTransaction();
	try{
		$sql=$main->db->sql->Insert($main->table['Post'],$articleinfo);
		$insert=$main->db->Insert($sql);
		if($insert['s']){
			throw new PDOexception('提交出错了:'.$insert['r']);
		}
		$main->db->db->commit();
		return array('s'=>'0','id'=>$insert['id']);		
	}catch(PDOexception $e){
		$main->db->db->rollBack();
		return array('s'=>'1','r'=>$e->getMessage());
	}
}
function articleDel(){
	global $main;
	$id=$_POST['id'];
	$idArray=json_decode($id,true);
	if(!$idArray)	return array('s'=>'1','r'=>'josn数据解析错误');
	foreach($idArray as $value){
		$pst_check=$main->getPostById($value);
		if($pst_check['s'])	   return array('s'=>'1','r'=>$pst_check['r']);
		$main->db->db->beginTransaction();
		try{
			$sql=$main->db->sql->Delete($main->table['Post'],array(array('=','log_ID',$value)));
			$delete=$main->db->Delete($sql);
			if($delete['s']){
				throw new PDOexception('删除出错了:'.$delete['r']);
			}
			//下面是查询是否该文章属于某个模块，如果是的话，则删掉模块中相应的新闻。
			$mdlList=$main->getMdlList();
			if($mdlList['s'])	throw new PDOexception('获取模块列表失败了:'.$mdlList['r']); 
			foreach($mdlList['data'] as $mdlValue){
				$moduleID=$mdlValue['mdl_ID'];
				$index=$mdlValue['mdl_Index'];
				$indexToArray=explode(',',$index);
				foreach($indexToArray as $idKey=>$idValue){
					if($idValue==$value){
						unset($indexToArray[$idKey]);
						$indexToStr=implode(',',$indexToArray);
						$sql=$main->db->sql->Update($main->table['Module'],array('mdl_Index'=>$indexToStr),array(array('=','mdl_ID',$moduleID)));
						$moduleUpdate=$main->db->Update($sql);
						if($moduleUpdate['s'])		throw new PDOexception('修改模块出错了:'.$moduleUpdate['r']);
						break;
					}
				}				
			}
			$main->db->db->commit();	
		}catch(PDOexception $e){
			$main->db->db->rollBack();
			return array('s'=>'1','r'=>$e->getMessage());
		}
	}
	return array('s'=>'0');	
}
function articleEdt(){
	global $main;
	if(!is_numeric($_GET['id']))	return array('s'=>'1','r'=>'id格式错误');	
	$id=(int)$_GET['id'];	
	$pst_check=$main->getPostById($id);
	if($pst_check['s']){
		return array('s'=>'1','r'=>$pst_check['r']);
	}
	if(empty($_POST['log_CateID'])  or empty($_POST['log_Title']) or empty($_POST['log_Content'])){
		return array('s'=>'1','r'=>'信息提供不完整');
	}
	if(isset($_POST['log_Status']))	$datainfo['log_Status']=$_POST['log_Status'];
	if(isset($_POST['log_isHot']))	$datainfo['log_isHot']=$_POST['log_isHot'];
	if(isset($_POST['log_isNew']))	$datainfo['log_isNew']=$_POST['log_isNew'];
	
	if(isset($_POST['log_AuthorName']))	$datainfo['log_AuthorName']=$_POST['log_AuthorName'];	
	if(isset($_POST['log_Intro']))	$datainfo['log_Intro']=$_POST['log_Intro'];	
	if(isset($_POST['log_Title']))	$datainfo['log_Title']=$_POST['log_Title'];	
	if(isset($_POST['log_Content']))$datainfo['log_Content']=$_POST['log_Content'];	
	if(isset($_POST['log_CateID']))$datainfo['log_CateID']=$_POST['log_CateID'];			
	$datainfo['log_ModifyDate']=time();		
	$sql=$main->db->sql->Update($main->table['Post'],$datainfo,array(array('=','log_ID',$id)));
	$update=$main->db->Update($sql);
	if(!$update['s']){
		return array('s'=>'0');	
	}else{
		return array('s'=>'1','r'=>$update['r']);
	}		
}
function viewArticleByID(){
	global $main;	
	$id=(int)$_GET['id'];
	$news=$main->getPostById($id);
	if($news['s'])	return array('s'=>'1','r'=>$news['r']);
	if(!isStuff($main->user->name)){
		$view_count=$news['data']['log_ViewNums'];
		$view_count=$view_count+1;
		$sql=$main->db->sql->Update($main->table['Post'],array('log_ViewNums'=>$view_count),array(array('=','log_ID',$id)));
		$update=$main->db->Update($sql);
		if($update['s'])	return array('s'=>'1','r'=>$update['r']);
	}
	$cate=$main->getCateByID($news['data']['log_CateID']);
	if($cate['s'])	return array('s'=>'1','r'=>$cate['r'],'id'=>$news['data']['log_CateID']);
	$news['data']['log_CateName']=$cate['data']['cate_Name'];
	$news['data']['log_CreateDate']=date('Y-m-d H:i:s',$news['data']['log_CreateDate']);
	if(!empty($news['log_ModifyDate']))
	$news['data']['log_ModifyDate']=date('Y-m-d H:i:s',$news['data']['log_ModifyDate']);	
	return array('s'=>'0','data'=>$news['data']);	
}
function  listArticles(){
	global $main;
	$where=null;
	$select=null;
	$page=isset($_GET['page'])?$_GET['page']:1;	
	if(!empty($_GET['sortmode']))	$order=logSortMode($_GET['sortmode']);	else $order=logSortMode(0);	
	if(!empty($_GET['type'])){
		$where=array(array('=','log_CateID',$_GET['type']));
	}//取消了$_GET['content']
	$select=array('log_CateID,log_ID,log_AuthorName,log_isHot,log_isNew,log_CreateDate,log_Status,log_Title,log_Intro,log_Content');	
	$list=$main->getArticleNums($where);
	$moduleList=moduleMng();
	if($moduleList['s'])	return array('s'=>'1','r'=>$moduleList['r']);
	$moduleList=$moduleList['data'];//获得模块列表
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	if(!$list['nums'])	return array('s'=>'1','r'=>'啊哦,这个地儿还没有新闻啊');
	$limit=paging($page,$list['nums']);
	$limit=$limit['limit'];	
	$articles=$main->getArticleList($select,$where,$order,$limit);
	if($articles['s'])	return array('s'=>'1','r'=>$articles['r']);
	foreach($articles['data'] as $key=>&$value){
		$cate=$main->getCateByID($value['log_CateID']);
		if($cate['s'])	return array('s'=>'1','r'=>$cate['r'],'id'=>$value['log_CateID']);
		$value['log_CateName']=$cate['data']['cate_Name'];			
		$value['log_CreateDate']=date('Y-m-d H:i:s',$value['log_CreateDate']);
		//下面是验证某篇新闻是不是属于某个模块
		foreach($moduleList as $moduleValue){
			$moduleID=$moduleValue['mdl_ID'];
			$moduleIndex=$moduleValue['mdl_Index'];
			$moduleArray=explode(',',$moduleIndex);
			if(in_array($value['log_ID'],$moduleArray)){
				$value['log_Module'][$moduleID]=true;
			}else{
				$value['log_Module'][$moduleID]=false;
			}
		}	
	}
	return array('s'=>'0','count'=>$list['nums'],'data'=>$articles['data']);		
}
function  backListArticles(){
	global $main;
	if(!empty($_GET['sortmode']))	$order=logSortMode($_GET['sortmode']);	else $order=logSortMode(0);
	if(!empty($_GET['class']))	$class=$_GET['class'];	else $class='all';
	$page=isset($_GET['page'])?$_GET['page']:1;
	$where=null;
	$select=null;
	switch($class){
		case 'all':
		$where=null;		
		break;
		
		case 'type':
		if(!empty($_GET['type']))	$type=$_GET['type'];	else return array('s'=>'1','r'=>'还没有指定您要看的类型哦');
		$where=array(array('=','log_CateID'));		
		break;
		
		case 'author':
		if(!empty($_GET['authorID']))	$type=$_GET['authorID'];	else return array('s'=>'1','r'=>'还没有指定作者哦');
		$where=array(array('=','log_AuthorID'));		
		break;		
				
		default:
		$where=null;
		break;
	}
	if(!empty($_GET['content']))	$content=$_GET['content'];	else	$content='0';
	if(!$content){		
		$select=array('log_ID,log_CateID,log_AuthorID,log_isHot,log_isNew,log_CreateDate,log_Status,log_Title,log_Intro','log_ModifyDate','log_ViewNums');		
	}else{
		$select=array('log_ID,log_CateID,log_AuthorID,log_isHot,log_isNew,log_CreateDate,log_Status,log_Title,log_Intro,log_Content','log_ModifyDate','log_ViewNums');
	}
	$list=$main->getArticleNums($where);
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	$limit=paging($page,$list['nums']);
	$limit=$limit['limit'];
	
	$moduleList=moduleMng();
	if($moduleList['s'])	return array('s'=>'1','r'=>$moduleList['r']);
	$moduleList=$moduleList['data'];//获得模块列表
	
	$articles=$main->getArticleList($select,$where,$order,$limit);
	if($articles['s'])	return array('s'=>'1','r'=>$articles['r']);
	foreach($articles['data'] as $key=>&$value){
		$cate=$main->getCateByID($value['log_CateID']);
		if($cate['s'])	return array('s'=>'1','r'=>$cate['r'],'id'=>$value['log_CateID']);
		$value['log_CateName']=$cate['data']['cate_Name'];			
		$mem=$main->getMemberById($value['log_AuthorID']);
		if($mem['s'])	return array('s'=>'1','r'=>$mem['r']);
		$value['log_AuthorName']=$mem['data']['mem_Name'];	
		$value['log_CreateDate']=date('Y-m-d H:i:s',$value['log_CreateDate']);
		$value['log_ModifyDate']=date('Y-m-d H:i:s',$value['log_ModifyDate']);	
			//下面是验证某篇新闻是不是属于某个模块
		foreach($moduleList as $moduleValue){
			$moduleID=$moduleValue['mdl_ID'];
			$moduleIndex=$moduleValue['mdl_Index'];
			$checkID= strval($value['log_ID']);
			$checkIDs=$moduleIndex;
			$moduleArray=explode(',',$moduleIndex);
			$check=in_array($checkID,$moduleArray);
			if($check){
				$value['log_Module'][$moduleID]=true;
			}else{
				$value['log_Module'][$moduleID]=false;
			}
		}			
	}
	return array('s'=>'0','count'=>$list['nums'],'data'=>$articles['data']);	
}

function articleSearch(){
	global $main;
	$page=isset($_POST['page'])?$_POST['page']:1;
	$keyWords='%'.$_POST['keywords'].'%';	
	$list=$main->getArticleNums();
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	$limit=paging($page,$list['nums']);
	$limit=$limit['limit'];
	$sql=$main->db->sql->Select($main->table['Post'],array('*'),array(array('like','log_Title',$keyWords)),null,$limit);
	$exc=$main->db->Query($sql);
	if(!$exc['s']&&!empty($exc['data'])){
		foreach($exc['data'] as &$value){
			$cate=$main->getCateByID($value['log_CateID']);
			if($cate['s'])	return array('s'=>'1','r'=>$cate['r'],'id'=>$value['log_CateID']);
			$value['log_CateName']=$cate['data']['cate_Name'];			
			$value['log_CreateDate']=date('Y-m-d H:i:s',$value['log_CreateDate']);
			if(!empty($value['log_ModifyDate'])) $value['log_ModifyDate']=date('Y-m-d H:i:s',$value['log_ModifyDate']);	else 	$value['log_ModifyDate']=0;		
		}
		return array('s'=>'0','data'=>$exc['data'],'count'=>$list['nums']);
	}else{
		return array('s'=>'1','r'=>'未查询到相关结果');
	}	
}
function moveCate($id,$cate){
	global $main;
	if(is_string($id)){//json格式还没有测试
		$id=json_decode($id,true);
		foreach($id as $key=>$value){
			$check=$main->getCateById($value);	
			if($check['s'])	return array('s'=>'1','r'=>$check['r']);	
			$sql=$main->db->sql->Update($main->table['Post'],array('log_CateID'=>1),array(array('=','log_ID',$value)));
			$update=$main->db->Update($sql);
			if($update['s'])	return array('s'=>'1','r'=>$update['r']);
		}
		return array('s'=>'0');
	}
	$check=$main->getCateById($id);	
	if($check['s'])	return array('s'=>'1','r'=>$check['r']);
	$datainfo['log_CateID']=$cate;
	$sql=$main->db->sql->Update($main->table['Post'],$datainfo,array(array('=','log_CateID',$id)));
	$update=$main->db->Update($sql);
	if($update['s'])	return array('s'=>'1','r'=>$update['r']);	
	return array('s'=>'0');
	
}
/**新闻分类模块
 *
*/
function categoryPst(){
	global $main;
	if(!(isset($_POST['Cate_Name'])&&isset($_POST['Cate_ParentID'])))	return array('s'=>'1','r'=>'信息输入不完整');
	$check=$main->getCaterByName($_POST['Cate_Name']);
	if(!$check['s'])	return array('s'=>'1','r'=>'这个分类已经存在');
	$datainfo['Cate_Name']=$_POST['Cate_Name'];
	$datainfo['Cate_ParentID']=$_POST['Cate_ParentID'];
	$datainfo['Cate_Intro']=isset($_POST['Cate_Intro'])?$_POST['Cate_Intro']:'';
	//以下为
	$datainfo['Cate_Image']=isset($_POST['Cate_Image'])?$_POST['Cate_Image']:'';
	if($datainfo['Cate_ParentID']==0){
		$sql=$main->db->sql->Insert($main->table['Category'],$datainfo);
		$insert=$main->db->Insert($sql);
		if($insert['s'])	return array('s'=>'1','r'=>$insert['r']);
		else return array('s'=>'0');
	}else{
		$parent=$main->getCateById($datainfo['Cate_ParentID']);
		if($parent['s'])	return array('s'=>'1','r'=>$parent['r']);
		if($parent['data']['cate_Name']=='未分类')	return array('s'=>'1','r'=>'不能在"未分类"下创建子分类');
		if(!$parent['data']['cate_Child']){
			$update['cate_Child']='1';	
			$sql=$main->db->sql->Update($main->table['Category'],$update,array(array('=','cate_ID',$datainfo['Cate_ParentID'])));
			$update=$main->db->Update($sql);
			if($update['s'])	return array('s'=>'1','r'=>$update['r']);	
		}
		$sql=$main->db->sql->Insert($main->table['Category'],$datainfo);
		$insert=$main->db->Insert($sql);
		if($insert['s'])	return array('s'=>'1','r'=>$insert['r']);
		else return array('s'=>'0','id'=>$insert['id']);
	}
}
function categoryEdt(){
	global $main;
	$id=(int)$_GET['id'];
	if(!isset($_POST['Cate_Name']))	return array('s'=>'1','r'=>'信息输入不完整');
	$datainfo['cate_Name']=$_POST['Cate_Name'];
	if($_POST['Cate_Name']=='未分类')	return array('s'=>'1','r'=>'"未分类"是默认分类,不能修改');
	$name=$main->getCateById($id);
	if($name['s'])	return array('s'=>'1','r'=>$name['r']);
	if($name['data']['cate_Name']!=$_POST['Cate_Name']){
		$check=$main->getCaterByName($_POST['Cate_Name']);
		if(!$check['s'])	return array('s'=>'1','r'=>'这个分类已经存在');
	}	
	$datainfo['cate_ParentID']=isset($_POST['Cate_ParentID'])?$_POST['Cate_ParentID']:0;
	$parent=$main->getCateById($datainfo['cate_ParentID']);
	if($parent['s'])	return array('s'=>'1','r'=>$parent['r']);
	if($parent['data']['cate_Name']=='未分类')	return array('s'=>'1','r'=>'不能在"未分类"下创建子分类');
	if(!$parent['data']['cate_Child'])	$datainfo['cate_Child']='1';	
	$datainfo['Cate_Intro']=isset($_POST['Cate_Intro'])?$_POST['Cate_Intro']:'';
	
	$sql=$main->db->sql->Update($main->table['Category'],$datainfo,array(array('=','cate_ID',$id)));
	$update=$main->db->Update($sql);
	if($update['s'])	return array('s'=>'1','r'=>$update['r']);	
	else	return array('s'=>'0');		
}

function categoryDel(){
	global $main;
	$id=(int)$_GET['id'];
	$check=$main->getCateById($id);	
	if($check['s'])	return array('s'=>'1','r'=>$check['r']);
	if($check['data']['cate_Child'])	return array('s'=>'1','r'=>'该分类下有子分类,不能删除');
	if($check['data']['cate_Name']=='未分类')	return array('s'=>'1','r'=>'"未分类"是默认分类,不能删除');
	$main->db->db->beginTransaction();
	try{
		$move=moveCate($id,1);
		$sql=$main->db->sql->Delete($main->table['Category'],array(array('=','cate_ID',$id)));
		$delete=$main->db->Delete($sql);
		if($move['s']==='1'){
			throw new PDOexception('修改出错了:'.$move['r']);
		}
		if($delete['s']){
			throw new PDOexception('删除出错了:'.$delete['r']); 
		}
		$main->db->db->commit();
		return array('s'=>'0');		
	}catch(PDOexception $e){
		$main->db->db->rollBack();
		return array('s'=>'1','r'=>$e->getMessage());
	}
}
function  cateRecursion($node,&$cateByOrder){
	global $main;
	$subCates=$main->getCateList('',array(array('=','cate_ParentID',$node['cate_ID'])),'','');
	if($subCates['s'])	return array('s'=>'1','reason'=>$subCates['r']);
	$subCates=$subCates['data'];
	foreach($subCates as $key=>$value){
		$cateByOrder[$value['cate_ID']]=$value;
		if($value['cate_Child'])	cateRecursion($value,$cateByOrder);	//else $cateByOrder[$key]=$value;
	}
	return true;
}
function categoryMng(){//还没测试
	global $main;
	$cate=$main->getCateList('',array(array('=','cate_ParentID',0)),'','');
	if($cate['s'])	return array('s'=>'1','r'=>$cate['r']);
	$rootcate=array();
	$nums=$main->getCateNums();
	if($nums['s'])	return array('s'=>'1','r'=>$nums['r']);
	$nums=$nums['nums'];
	foreach($cate['data'] as $value){
		$rootcate[$value['cate_ID']]=$value;
	}
	$cateByOrder=array();
	foreach($rootcate as $key=>$value){
		$cateByOrder[$key]=$value;
		cateRecursion($value,$cateByOrder);
	}
	return array('s'=>'0','data'=>$cateByOrder,'count'=>$nums);
}

/**附件模块
 *
*/
function addImageInfo(){
	global $main;
	$id=(int)$_GET['id'];
	$check=$main->getUploadById($id);
	if($check['s'])	return array('s'=>'1','r'=>$check['r']);	
	if(!empty($_POST['info'])){
		$datainfo['ul_Info']=$_POST['info'];
	}else{
		return array('s'=>'1','r'=>'简介不能为空');
	}
	$sql=$main->db->sql->Update($main->table['Upload'],$datainfo,array(array('=','ul_ID',$id)));
	$update=$main->db->Update($sql);			
	if($update['s']) return array('s'=>'1','r'=>$update['r']); else return array('s'=>'0');	
}
function listImages(){
	global $main;
	$page=isset($_GET['page'])?$_GET['page']:1;
	$list=$main->getUploadNums();
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	$limit=paging($page,$list['nums']);
	$limit=$limit['limit'];	
	$order=array('ul_ID'=>'DESC');
	$images=$main->getUploadList('','',$order,$limit);
	if($images['s'])	return array('s'=>'1','r'=>$images['r']);		else return array('s'=>'0','count'=>$list['nums'],'data'=>$images['data']);
}
function deleteImages(){
	global $main;
	$id=$_POST['id'];
	$idArray=json_decode($id,true);
	if(!$idArray)	return array('s'=>'1','r'=>'josn数据解析错误');
	foreach($idArray as $value){
		if(empty($value))	continue;
		$check=$main->getUploadById($value);
		if($check['s'])	return array('s'=>'1','r'=>$check['r']);
		$sql=$main->db->sql->Delete($main->table['Upload'],array(array('=','ul_ID',$value)));
		$delete=$main->db->Delete($sql);
		if($delete['s'])	return array('s'=>'1','r'=>$delete['r']);
		if(!unlink('../uploadify/uploads/original/'.$check['data']['ul_SourceName'])){
			return array('s'=>'1','r'=>'删除原图失败');
		}
		if(!unlink('../uploadify/uploads/scale/'.$check['data']['ul_SourceName'])){
			return array('s'=>'1','r'=>'删除原图失败');
		}
	}
	return array('s'=>'0');
}
/**回复提问模块
 *
*/
function msgNew(){
	global $main;
	if(empty($_POST['msg_Author'])){
		$datainfo['msg_Author']=$_SERVER['REMOTE_ADDR'];
	}else{
		$datainfo['msg_Author']=$_POST['msg_Author'];
	}
	$datainfo['msg_Time']=time();
	if(empty($_POST['msg_Title']))	return array('s'=>'1','r'=>'留言标题不能为空');
	if(empty($_POST['msg_Content']))	return array('s'=>'1','r'=>'留言内容不不能为空');
	$datainfo['msg_Title']=addslashes($_POST['msg_Title']);	
	$datainfo['msg_Content']=addslashes($_POST['msg_Content']);	
	$datainfo['msg_Status']='0';
	$datainfo['msg_Reply']='';
	if(!empty($_POST['msg_Email']))	$datainfo['msg_Email']=$_POST['msg_Email'];		else $datainfo['msg_Email']='';
	if(!empty($_POST['msg_Avatar']))	$datainfo['msg_Avatar']=addslashes($_POST['msg_Avatar']);		else return array('s'=>'1','r'=>'还没有选择头像');
	$sql=$main->db->sql->Insert($main->table['Message'],$datainfo);
	$insert=$main->db->Insert($sql);
	if($insert['s'])	return array('s'=>'1','r'=>$insert['r']);
	else return array('s'=>'0');	
}
function msgReply(){
	global $main;
	$id=(int)$_GET['id'];
	$check=$main->getMsgById($id);
	if($check['s'])	return array('s'=>'1','r'=>$check['r']);
	if(empty($_POST['msg_Reply']))	return array('s'=>'1','r'=>'回复内容不能为空');
	$datainfo['msg_Reply']=$_POST['msg_Reply'];
	$datainfo['msg_Status']='1';	
	$sql=$main->db->sql->Update($main->table['Message'],$datainfo,array(array('=','msg_ID',$id)));
	$update=$main->db->Update($sql);			
	if($update['s']) return array('s'=>'1','r'=>$update['r']); else return array('s'=>'0');		
}
function msgDelete(){
	global $main;
	$id=$_POST['id'];
	$idArray=json_decode($id,true);
	if(!$idArray)	return array('s'=>'1','r'=>'josn数据解析错误');
	foreach($idArray as $value){
		$check=$main->getMsgById($value);
		if($check['s'])	return array('s'=>'1','r'=>$check['r']);
		$sql=$main->db->sql->Delete($main->table['Message'],array(array('=','msg_ID',$value)));
		$delete=$main->db->Delete($sql);
		if($delete['s'])	return array('s'=>'1','r'=>$delete['r']);
	}
	return array('s'=>'0');
}

function viewMsgs(){
	global $main;	
	$page=isset($_GET['page'])?$_GET['page']:1;
	$select=array('msg_ID','msg_Title','msg_Author','msg_Email','msg_Content','msg_Reply','msg_Time','msg_Avatar');
	$where=array(array('=','msg_Status','1'));
	$list=$main->getMsgNums($where);
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	$limit=paging($page,$list['nums']);
	$limit=$limit['limit'];	
	$order=array('msg_Time'=>'DESC');
	$msgs=$main->getMsgList($select,$where,$order,$limit);
	if($msgs['s'])	return array('s'=>'1','r'=>$msgs['r']);	
	foreach($msgs['data'] as &$value){
		$value['msg_Title']=stripslashes($value['msg_Title']);
		$value['msg_Content']=stripslashes($value['msg_Content']);
		$value['msg_Avatar']=stripslashes($value['msg_Avatar']);
		$value['msg_Time']=date('Y-m-d H:i:s',$value['msg_Time']);
	}
	return array('s'=>'0','count'=>$list['nums'],'data'=>$msgs['data']);
}
function backViewMsgs(){
	global $main;	
	$page=isset($_GET['page'])?$_GET['page']:1;
	$select=array('msg_ID','msg_Title','msg_Status','msg_Author','msg_Email','msg_Content','msg_Reply','msg_Time','msg_Avatar');
	$where=null;
	$list=$main->getMsgNums($where);
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	$limit=paging($page,$list['nums']);
	$limit=$limit['limit'];	
	$order=array('msg_Status'=>'ASC','msg_Time'=>'DESC');
	$msgs=$main->getMsgList($select,$where,$order,$limit);
	if($msgs['s'])	return array('s'=>'1','r'=>$msgs['r']);	
	foreach($msgs['data'] as &$value){
		$value['msg_Title']=stripslashes($value['msg_Title']);
		$value['msg_Content']=stripslashes($value['msg_Content']);
		$value['msg_Avatar']=stripslashes($value['msg_Avatar']);
		$value['msg_Time']=date('Y-m-d H:i:s',$value['msg_Time']);
	}
	return array('s'=>'0','count'=>$list['nums'],'data'=>$msgs['data']);	
}
/**网站首页模块
 *
*/
function createModule(){
	global $main;
	$datainfo['mdl_Type']=isset($_POST['mdl_Type'])?$_POST['mdl_Type']:0;
	if(empty($_POST['mdl_Name']) or empty($_POST['mdl_Limit']))	return array('s'=>'1','r'=>'模块名或模块上限不能为空');
	$datainfo['mdl_Name']=$_POST['mdl_Name'];
	$datainfo['mdl_Limit']=$_POST['mdl_Limit'];
	$datainfo['mdl_Index']='';
	$mdlList=$main->getMdlList();
	if($mdlList['s'])	return array('s'=>'1','r'=>$mdlList['r']);
	foreach($mdlList['data'] as $value)
	if($value['mdl_Name']==$datainfo['mdl_Name'])	return array('s'=>'1','r'=>'这个模块名已经存在了');
	$sql=$main->db->sql->Insert($main->table['Module'],$datainfo);
	$insert=$main->db->Insert($sql);
	if($insert['s'])	return array('s'=>'1','r'=>$insert['r']);
	return array('s'=>'0');
}
function deleteModule(){
	global $main;
	$id=(int)$_GET['id'];
	$module=$main->getModuleById($id);
	if($module['s'])	return array('s'=>'1','r'=>$module['r']);
	$sql=$main->db->sql->Delete($main->table['Module'],array(array('=','mdl_ID',$id)));
	$delete=$main->db->Delete($sql);
	if($delete['s'])	return array('s'=>'1','r'=>$delete['r']);
	return array('s'=>'0');
}
function modifyModule(){
	global $main;
	$id=(int)$_GET['id'];
	$module=$main->getModuleById($id);
	if($module['s'])	return array('s'=>'1','r'=>$module['r']);
	if(!empty($_POST['mdl_Type']))	$datainfo['mdl_Type']=$_POST['mdl_Type'];
	if(!empty($_POST['mdl_Name']))	$datainfo['mdl_Name']=$_POST['mdl_Name'];
	if(isset($datainfo['mdl_Name'])){
		$mdlList=$main->getMdlList();
		foreach($mdlList['data'] as $value)
		if($value['mdl_Name']==$module['data']['mdl_Name']) continue;
		if($value['mdl_Name']==$datainfo['mdl_Name'])	return array('s'=>'1','r'=>'这个模块名已经存在了');
	}
	if(!empty($_POST['mdl_Limit']))	$datainfo['mdl_Limit']=$_POST['mdl_Limit'];
	if(isset($datainfo['mdl_Limit']) and $datainfo['mdl_Limit']>'9999')	return array('s'=>'1','r'=>'上限不能超过9999');
	if(empty($datainfo))	return array('s'=>'1','r'=>'修改信息不能为空');
	$sql=$main->db->sql->Update($main->table['Module'],$datainfo,array(array('=','mdl_ID',$id)));
	$update=$main->db->Update($sql);
	if($update['s'])	return array('s'=>'1','r'=>$update['r']);	
	else	return array('s'=>'0');			
}
function addToLogModule(){//用,分隔
	global $main;
	$mid=(int)$_GET['mid'];
	$module=$main->getModuleById($mid);
	if($module['s'])	return array('s'=>'1','r'=>$module['r']);
	$index=$module['data']['mdl_Index'];
	if(isset($_POST['nid'])){
		$nId=json_decode($_POST['nid'],true);
		$nIdNums=count($nId);
		if(empty($index)){
			if($nIdNums>$module['data']['mdl_Limit'])	return array('s'=>'1','r'=>'您首次勾选的数量超过上限('.$module['data']['mdl_Limit'].')了哦,请重新勾选');
			foreach($nId as $value){
				$news=$main->getPostById($value);
				if($news['s'])		return array('s'=>'1','r'=>$news['r'].'-'.$value);					
			}			
			$nId_string=implode(',',$nId);
			$sql=$main->db->sql->Update($main->table['Module'],array('mdl_Index'=>$nId_string),array(array('=','mdl_ID',$mid)));
			$update=$main->db->Update($sql);
		}else{
			$indexArray=explode(',',$index);
			$indexNums=count($indexArray);
			if(($nIdNums+$indexNums)>$module['data']['mdl_Limit'])	return array('s'=>'1','r'=>'您只能再往这儿再添加'.($module['data']['mdl_Limit']-$indexNums).'条新闻,先清除一些新闻吧。');
			foreach($nId as $value){
				$news=$main->getPostById($value);
				if($news['s'])		return array('s'=>'1','r'=>$news['r'].'-'.$value);					
				if(in_array($value,	$indexArray))
				return array('s'=>'1','r'=>'《'.$news['data']['log_Title'].'》这则新闻已经添加进这个地方了,不能重复添加哦');
				$index=$value.','.$index;
			}
			$index=explode(',',$index);
			//rsort($index);
			$new_nums=count($index);
			if($new_nums>$module['data']['mdl_Limit']){
				$indexByOrder=array_slice($index,0,$module['data']['mdl_Limit']);
				$indexByOrder=implode(',',$indexByOrder);
			}else{
				$indexByOrder=implode(',',$index);
			}
			$sql=$main->db->sql->Update($main->table['Module'],array('mdl_Index'=>$indexByOrder),array(array('=','mdl_ID',$mid)));
			$update=$main->db->Update($sql);
		}
		if($update['s'])	return array('s'=>'1','r'=>$update['r']);	
		else	return array('s'=>'0');					
	}else{
		return array('s'=>'1','r'=>'没有接收到新闻');
	}
	
}
function clearFromLogModule(){
	global $main;
	$mid=(int)$_GET['mid'];
	$module=$main->getModuleById($mid);
	if($module['s'])	return array('s'=>'1','r'=>$module['r']);
	$module=$main->getModuleById($mid);
	$sql=$main->db->sql->Update($main->table['Module'],array('mdl_Index'=>''),array(array('=','mdl_ID',$mid)));
	$update=$main->db->Update($sql);
	if($update['s'])	return array('s'=>'1','r'=>$update['r']);	
	else	return array('s'=>'0');			
}
function deleteFromLogModule(){
	global $main;
	$mid=(int)$_GET['mid'];
	$nid=(int)$_GET['nid'];
	$module=$main->getModuleById($mid);
	$news=$main->getPostById($nid);
	if($news['s'])		return array('s'=>'1','r'=>$news['r']);	
	if($module['s'])	return array('s'=>'1','r'=>$module['r']);
	$index=$module['data']['mdl_Index'];
	if(empty($index))	return array('s'=>'1','r'=>'该模块没有内容');
	$index=explode(',',$index);
	$indexNums=count($index);
	foreach($index as $key=>&$value){
		if($value==$nid)
		unset($index[$key]);
	}
	$index=implode(',',$index);
	$sql=$main->db->sql->Update($main->table['Module'],array('mdl_Index'=>$index),array(array('=','mdl_ID',$mid)));
	$update=$main->db->Update($sql);
	if($update['s'])	return array('s'=>'1','r'=>$update['r']);	
	else	return array('s'=>'0');	
}
function showFromLogModule(){
	global $main;
	$mid=(int)$_GET['mid'];
	$module=$main->getModuleById($mid);
	if($module['s'])	return array('s'=>'1','r'=>$module['r']);
	$index=$module['data']['mdl_Index'];
	if(empty($index))	return array('s'=>'2','r'=>'模块内容为空','limit'=>$module['data']['mdl_Limit'],'count'=>0);
	$index=explode(',',$index);
	$indexNums=count($index);
	$data=array();
	foreach($index as $value){
		$news=$main->getPostById($value);
		if($news['s'])	return array('s'=>'1','r'=>$news['r']);
		unset($news['data']['log_Content']);
		$data[]=$news['data'];
	}
	$newsNums=count($data);
	$dataByOrder=array();
	for($i=0;$i<$newsNums;$i++){
		for($j=$i+1;$j<$newsNums;$j++)
		if($data[$i]['log_CreateDate']<$data[$j]['log_CreateDate']){
			$temp=$data[$i];
			$data[$i]=$data[$j];
			$data[$j]=$temp;
		}
		$data[$i]['log_CreateDate']=date('Y-m-d',$data[$i]['log_CreateDate']);
	}
	return array('s'=>'0','data'=>$data,'limit'=>$module['data']['mdl_Limit'],'count'=>$newsNums);	
}
function moduleMng(){
	global $main;
	$select=array('mdl_ID','mdl_Name','mdl_Index');
	$list=$main->getMdlList($select,'','','');
	if($list['s'])	return array('s'=>'1','r'=>$list['r']);
	foreach($list['data'] as &$value){
		if(empty($value['mdl_Index'])){
			$value['mdl_count']=0;
		}else{
			$index=$value['mdl_Index'];
			$indexArray=explode(',',$index);
			$count=count($indexArray);
			$value['mdl_count']=$count;
		}
	}
	$nums=$main->getMdlNums();
	if($nums['s'])	return array('s'=>'1','r'=>$nums['r']);
	$nums=$nums['nums'];
	return array('s'=>'0','data'=>$list['data'],'count'=>$nums); 	
}
?>


