<?php
class Main{
	static private $_main=null;
	
	public $db=null;
	public $option=array();
	public $table=null;
	
	public $user=null;
	public $userStatus=false;
	
	public $isinitialize=false;
	public $isconnect=false;
	
	public $issession=false;
	public $cookiepath='';
	
	public $action=array();
	
	static public function getInstance(){
		if(!isset(self::$_main)){
			self::$_main=new Main;
		}
		return self::$_main;
	}
	
	function __construct(){
		$this->option=&$GLOBALS['option'];
		$this->table=&$GLOBALS['table'];
		$this->actions=&$GLOBALS['actions'];
	}
	
	function __destruct(){
		$this->terminate();
	}
	
/**
 *some necessary initializations
*/ 	
	public function initialize(){
		if(!$this->openConnect()) return false;
		
		date_default_timezone_set($this->option['SC_TIME_ZONE_NAME']);
		
		//$this->user=new Member();//member类还没有定义
		$this->isinitialize=true;
		$status=$this->checkUserStatus();
		if($status['s']=='1'){
			$this->user=new Member;
			$this->user->name='游客';
			$this->user->level='6';
			$this->user->id=0;
		}
	}
	public function checkUserStatus(){
		//$this->startSession();
		$u=base64_encode('un1');
		$p=base64_encode('ps1');
		if(isset($_COOKIE[$u])&&isset($_COOKIE[$p])){
			$user=base64_decode(base64_decode($_COOKIE[$u]));
			$psw=base64_decode(base64_decode($_COOKIE[$p]));
			$len=strlen($psw);
			$psw=substr($psw,0,$len-1);
			if($this->verify($user,$psw)){
				$userdata=$this->getMemberByName($user);
				$this->user=new Member($userdata['data']);
				$this->userStatus=true;
				return array('s'=>0,'user'=>$this->user);
			}else{
				return array('s'=>'1');
			}
		}/*else if(isset($_SESSION['user'])){
			$this->user=new Member($_SESSION['user']);
			return array('s'=>0,'type'=>'session');
		}*/else{
			return array('s'=>'1');
		}
	}
	public function openConnect(){
		if($this->isconnect) return false;
		if(!$this->option['SC_DATABASE_TYPE'])return false;
		switch($this->option['SC_DATABASE_TYPE']){
		case 'mysql':
		case 'pdo_mysql':
			try{
				if(!$this->initializeDb($this->option['SC_DATABASE_TYPE']))return false;
				if($this->db->open(array(
					$this->option['SC_MYSQL_SERVER'],
					$this->option['SC_MYSQL_USERNAME'],
					$this->option['SC_MYSQL_PASSWORD'],
					$this->option['SC_MYSQL_NAME'],
					$this->option['SC_MYSQL_PRE'],
					$this->option['SC_MYSQL_PORT'],
					$this->option['SC_MYSQL_PERSISTENT']
				))==false){
					return false;//$return=array('s'=>'0','r'=>'MYSQL can not be opened!');
				}
			}catch(Exception $e){
				die($e->getMessage());
				return false;//$return=array('s'=>'0','r'=>'MYSQL Database Connection Error!!');
			}
		break;
		}
		$this->isconnect=true;
		return true;
	}
	
	public function initializeDb($type){
		if(!trim($type))return false;
		$db_class='Db'.$type;
		$this->db=new $db_class();
		return true;
	}
	
	public function terminate(){
		if($this->isinitialize){
			$this->closeConnect();
		}
	}	
	
	public function CloseConnect(){
		if($this->isconnect){
			$this->db->Close();
		}
	}	
	
	public function startSession(){
		if($this->issession==true)return false;
		session_start();
		$this->issession=true;
		return true;
	}


	public function endSession(){
		if($this->issession==false)return false;
		session_unset();
		session_destroy();
		$this->issession=false;
		return true;
	}		
	
/**
 *Rights and Verify
*/ 		
	public function checkRights($op){
		if(!isset($this->actions[$op]))	return array('s'=>'2','r'=>'未知的操作');
		if ($this->user->level> $this->actions[$op]) {
			return array('s'=>'1','r'=>'权限不够');
		} else {
			return array('s'=>'0');
		}			
	}
	
	public function verify($username,$psw){
		//if(isset($this->user)&&$this->user->name!='游客')return array('s'=>'1','r'=>'您已经登录了!');
		$password=md5(md5($psw));
		$sql=$this->db->sql->Select($this->table['Member'],array('*'),array(array('=','mem_Name',$username),array('=','mem_Password',$password)),null,null);
		$exc=$this->db->Query($sql);
		if($exc['s'])return array('s'=>'1','r'=>$exc['r']);
		if(!empty($exc['data'])&&$exc['data'][0]['mem_ID']>0){
			return array('s'=>'0','user'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'密码错误!');
		}
	}
	
	
/**
 *Load data lists from database
*/ 			
	public function getList($type,$sql){
		$array=null;
		$list=array();
		$array=$this->db->Query($sql);
		if($array['s']){return array('s'=>'1','r'=>$array['r']);}
		/*foreach ($array as $a) {
			$l=new $type(); 
			$l->LoadInfoByAssoc($a);
			$list[]=$l;
		}*/
		return array('s'=>'0','data'=>$array['data']);
	}	

	function getMemberList($select=null,$where=null,$order=null,$limit=null){
		if(empty($select)){$select = array('*');}
		$sql = $this->db->sql->Select($this->table['Member'],$select,$where,$order,$limit);
		return $this->getList('Member',$sql);

	}
	function getArticleList($select=null,$where=null,$order=null,$limit=null){
		if(empty($select)){$select = array('*');}
		$sql = $this->db->sql->Select($this->table['Post'],$select,$where,$order,$limit);
		return $this->getList('Post',$sql);

	}
	function getCateList($select=null,$where=null,$order=null,$limit=null){
		if(empty($select)){$select = array('*');}
		$sql = $this->db->sql->Select($this->table['Category'],$select,$where,$order,$limit);
		return $this->getList('Category',$sql);
	}	
	function getMsgList($select=null,$where=null,$order=null,$limit=null){
		if(empty($select)){$select = array('*');}
		$sql = $this->db->sql->Select($this->table['Message'],$select,$where,$order,$limit);
		return $this->getList('Message',$sql);
	}
	function getUploadList($select=null,$where=null,$order=null,$limit=null){
		if(empty($select)){$select = array('*');}
		$sql = $this->db->sql->Select($this->table['Upload'],$select,$where,$order,$limit);
		return $this->getList('Upload',$sql);
	}		
	function getMdlList($select=null,$where=null,$order=null,$limit=null){
		if(empty($select)){$select = array('*');}
		$sql = $this->db->sql->Select($this->table['Module'],$select,$where,$order,$limit);
		return $this->getList('Module',$sql);
	}					
/**
 *Load data object from database
*/ 		
	
	function getMemberByName($name){
		$sql=$this->db->sql->Select($this->table['Member'],array('*'),array(array('=','mem_Name',$name)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'用户名不存在');
		}	
	}
	function getMemberById($id){
		$sql=$this->db->sql->Select($this->table['Member'],array('*'),array(array('=','mem_ID',$id)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']&&!empty($exc['data'])){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'不存在的用户ID');
		}	
	}
	function getMemberNums(){
		$name='nums';
		$count=array(array('COUNT','mem_ID',$name));
		$sql=$this->db->sql->Count($this->table['Member'],$count,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']){
			if(!empty($exc['data'][0][$name])){
				return array('s'=>'0','nums'=>$exc['data'][0][$name]);
			}else{
				return array('s'=>'1','r'=>'暂时还没有用户');
			}
		}else{
			return array('s'=>'1','r'=>$exc['r']);
		}		
	}	
	function getPostById($id){
		$sql=$this->db->sql->Select($this->table['Post'],array('*'),array(array('=','log_ID',$id)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']&&!empty($exc['data'])){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'不存在的新闻ID');
		}	
	}
	function getArticleNums($where=null){
		$name='nums';
		$count=array(array('COUNT','log_ID',$name));
		if(isset($where))
		$sql=$this->db->sql->Count($this->table['Post'],$count,$where);
		else
		$sql=$this->db->sql->Count($this->table['Post'],$count,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']){
			if(!empty($exc['data'][0][$name])){
				return array('s'=>'0','nums'=>$exc['data'][0][$name]);
			}else{
				return array('s'=>'1','r'=>'暂时还没写文章');
			}
		}else{
			return array('s'=>'1','r'=>$exc['r']);
		}		
	}				
	
	function getCateById($id){
		$sql=$this->db->sql->Select($this->table['Category'],array('*'),array(array('=','cate_ID',$id)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']&&!empty($exc['data'])){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'不存在的分类ID');
		}	
	}	
	function getCaterByName($name){
		$sql=$this->db->sql->Select($this->table['Category'],array('*'),array(array('=','cate_Name',$name)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s'] and !empty($exc['data'])){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'分类不存在');
		}	
	}
	function getCateNums($where=null){
		$name='nums';
		$count=array(array('COUNT','cate_ID',$name));
		if(isset($where))
		$sql=$this->db->sql->Count($this->table['Category'],$count,$where);
		else
		$sql=$this->db->sql->Count($this->table['Category'],$count,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']){
			if(!empty($exc['data'][0][$name])){
				return array('s'=>'0','nums'=>$exc['data'][0][$name]);
			}else{
				return array('s'=>'1','r'=>'暂时还没分类');
			}
		}else{
			return array('s'=>'1','r'=>$exc['r']);
		}		
	}				
	//信息
	function getMsgById($id){
		$sql=$this->db->sql->Select($this->table['Message'],array('*'),array(array('=','msg_ID',$id)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']&&!empty($exc['data'])){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'不存在的信息ID');
		}	
	}	
	function getMsgNums($where=null){
		$name='nums';
		$count=array(array('COUNT','msg_ID',$name));
		if(isset($where))
		$sql=$this->db->sql->Count($this->table['Message'],$count,$where);
		else
		$sql=$this->db->sql->Count($this->table['Message'],$count,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']){
			if(!empty($exc['data'][0][$name])){
				return array('s'=>'0','nums'=>$exc['data'][0][$name]);
			}else{
				return array('s'=>'1','r'=>'暂时还没有留言');
			}
		}else{
			return array('s'=>'1','r'=>$exc['r']);
		}		
	}
	//附件
	function getUploadById($id){
		$sql=$this->db->sql->Select($this->table['Upload'],array('*'),array(array('=','ul_ID',$id)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']&&!empty($exc['data'])){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'不存在的附件ID');
		}	
	}	
	function getUploadNums($where=null){
		$name='nums';
		$count=array(array('COUNT','ul_ID',$name));
		if(isset($where))
		$sql=$this->db->sql->Count($this->table['Upload'],$count,$where);
		else
		$sql=$this->db->sql->Count($this->table['Upload'],$count,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']){
			if(!empty($exc['data'][0][$name])){
				return array('s'=>'0','nums'=>$exc['data'][0][$name]);
			}else{
				return array('s'=>'1','r'=>'暂时还没附件');
			}
		}else{
			return array('s'=>'1','r'=>$exc['r']);
		}		
	}			
	//模块	
	function getModuleById($id){
		$sql=$this->db->sql->Select($this->table['Module'],array('*'),array(array('=','mdl_ID',$id)),null,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']&&!empty($exc['data'])){
			return array('s'=>'0','data'=>$exc['data'][0]);
		}else{
			return array('s'=>'1','r'=>'不存在的模块ID');
		}	
	}	
	function getMdlNums($where=null){
		$name='nums';
		$count=array(array('COUNT','mdl_ID',$name));
		if(isset($where))
		$sql=$this->db->sql->Count($this->table['Module'],$count,$where);
		else
		$sql=$this->db->sql->Count($this->table['Module'],$count,null);
		$exc=$this->db->Query($sql);
		if(!$exc['s']){
			if(!empty($exc['data'][0][$name])){
				return array('s'=>'0','nums'=>$exc['data'][0][$name]);
			}else{
				return array('s'=>'1','r'=>'暂时还没主页模块');
			}
		}else{
			return array('s'=>'1','r'=>$exc['r']);
		}		
	}
}
?>