<?php
class Dbpdo_MySQL
{
	
	public $dbpre = null;
	public $db = null;

	public $sql=null;

	function __construct()
	{
		$this->sql=new DbSql;
		$this->sql->type=__CLASS__;
	}
	
	public function EscapeString($s){
		return addslashes($s);
	}

	function Open($array){
		if($array[6]==false){
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',PDO::ATTR_PERSISTENT => false); 
		}else{
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',PDO::ATTR_PERSISTENT => false); 
		}
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',PDO::ATTR_PERSISTENT => false); 
		$db_link = new PDO('mysql:host=' . $array[0] . ';port=' . $array[5] . ';dbname=' . $array[3],$array[1],$array[2],$options);
		$this->db = $db_link;
		$this->db->exec('set session wait_timeout=10000');
		$this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$this->dbpre=$array[4];
		return true;
	}

	function CreateDB($dbmysql_server,$dbmysql_port,$dbmysql_username,$dbmysql_password,$dbmysql_name){
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',); 
		$db_link = new PDO('mysql:host=' . $dbmysql_server . ';port=' . $dbmysql_port,$dbmysql_username,$dbmysql_password,$options);
		$this->db = $db_link;
		$this->db->exec('CREATE DATABASE ' . $dbmysql_name);
	}
	
	function Close(){
	}
	
	function Query($query){
		$query=str_replace('%pre%', $this->dbpre, $query);
		try{
			$stmt=$this->db->prepare($query);
			$stmt->execute();
			$results=$stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return array('s'=>'0','data'=>$results);
		}catch(PDOexception $e){
			return array('s'=>'1','r'=>$e->getMessage());
		}

	}

	function Update($query){
		$query=str_replace('%pre%', $this->dbpre, $query);
		try{
			$stmt=$this->db->prepare($query);
			$stmt->execute();
			return array('s'=>'0');
		}catch(PDOexception $e){
			return array('s'=>'1','r'=>$e->getMessage());
		}
	}

	function Delete($query){
		$query=str_replace('%pre%', $this->dbpre, $query);
		try{
			$stmt=$this->db->prepare($query);
			$stmt->execute();
			return array('s'=>'0');
		}catch(PDOexception $e){
			return array('s'=>'1','r'=>$e->getMessage());
		}
	}

	function Insert($query){
		$query=str_replace('%pre%', $this->dbpre, $query);
		try{
			$stmt=$this->db->prepare($query);
			$stmt->execute();
			$id=$this->db->lastInsertId();
			return array('s'=>'0','id'=>$id);
		}catch(PDOexception $e){
			return array('s'=>'1','r'=>$e->getMessage());
		}
	}

}

?>