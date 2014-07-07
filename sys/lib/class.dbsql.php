<?php
class DbSql
{
	public $type=null;
	public function ParseWhere($where){
		global $main;

		$sqlw=null;
		if(!empty($where)) {
			$sqlw .= ' WHERE ';
			$comma = '';
			foreach($where as $k => $w) {
				$eq=$w[0];
				if($eq=='='|$eq=='<'|$eq=='>'|$eq=='LIKE'|$eq=='<>'|$eq=='<='|$eq=='>='|$eq=='like'){
					$x = (string)$w[1];
					$y = (string)$w[2];
					$y = $main->db->EscapeString($y);
					$sqlw .= $comma . " $x $eq '$y' ";
				}
				if($eq=='BETWEEN'||$eq=='between'){
					$b1 = (string)$w[1];
					$b2 = (string)$w[2];
					$b3 = (string)$w[3];
					$sqlw .= $comma . " $b1 BETWEEN '$b2' AND '$b3' ";
				}
				$comma = 'AND';
			}
		}
		return $sqlw;
	}

	public function Select($table,$select,$where,$order,$limit)
	{
		global $main;

		$sqls='';
		$sqlw='';
		$sqlo='';
		$sqll='';

		if(!empty($select)) {
			if(is_array($select)){
				$selectstr=implode($select,',');
				$sqls="SELECT $selectstr FROM $table ";			
			}else{
				$sqls="SELECT $select FROM $table ";	
			}
		}

		$sqlw=$this->ParseWhere($where);

		if(!empty($order)) {
			$sqlo .= ' ORDER BY ';
			$comma = '';
			foreach($order as $k=>$v) {
				$sqlo .= $comma ."$k $v";
				$comma = ',';
			}
		}

		if(!empty($limit)){
			if(!isset($limit[1])){
				$sqll .= " LIMIT $limit[0]";
			}else{
				if($limit[1]>0){
					$sqll .= " LIMIT $limit[0], $limit[1]";
				}
			}
		}

		return $sqls . $sqlw . $sqlo . $sqll;
	}

	public function Count($table,$count,$where)
	{
		global $main;

		$sqlc="SELECT ";

		if(!empty($count)) {
			foreach ($count as $key => $value) {
				$sqlc.=" $value[0]($value[1]) AS $value[2],";
			}
		}
		$sqlc=substr($sqlc, 0,strlen($sqlc)-1);

 		$sqlc.=" FROM $table ";

		$sqlw=$this->ParseWhere($where);

		return $sqlc . $sqlw;
	}
	
	public function Update($table,$keyvalue,$where)
	{
		global $main;

		$sql="UPDATE $table SET ";

		$comma = '';
		foreach ($keyvalue as $k => $v) {
			$v=$main->db->EscapeString($v);
			$sql.= $comma . "$k = '$v'";
			$comma = ' , ';
		}

		$sql.=$this->ParseWhere($where);
		return $sql;
	}

	public function Insert($table,$keyvalue)
	{
		global $main;

		$sql="INSERT INTO $table ";

		$sql.='(';
		$comma = '';
		foreach($keyvalue as $k => $v) {
			$sql.= $comma . "$k";
			$comma = ',';
		}
		$sql.=')VALUES(';

		$comma = '';
		foreach($keyvalue as $k => $v) {
			$v=$main->db->EscapeString($v);
			$sql.= $comma . "'$v'";
			$comma = ',';
		}
		$sql.=')';
		return  $sql;
	}

	public function Delete($table,$where)
	{
		global $main;

		$sql="DELETE FROM $table ";
		$sql.=$this->ParseWhere($where);
		return $sql;
	}

}
