<?php

 
//Connecto to db AT BOOT
$database["mysql_connection"]=sql_connect($database);
 
 /*
function sql_cleanvar($var){	
	
	return mysqli_real_escape_string($database["mysql_connection"], addslashes($var));
	
}
*/
function sql_cleanvar_reverse($var){	
	
	return stripslashes($var);
	
}
 
function sql_connect($database){ 

    try {
		
		$pdo = new PDO('mysql:host='.$database["mysql_server"].';dbname='.$database["mysql_db"].';charset='.$database["mysql_charset"], $database["mysql_user"], $database["mysql_password"]);
	 
	 	$database["mysql_connection"]=$pdo;
	
	    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	
	    if($database["connect_once"])
	     $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
	 
	     return $pdo;
	     
	} catch (PDOException $e) {
		print "<br/>DBError!: " . $e->getMessage() . "<br/>";
		die();
	}
	

	
} 

function sql_disconnect($database)
{
	$database["mysql_connection"] = null;
    sleep(60);
 
}


 function sql_query_row_count($strsql,$database)
{

  try {
			
		if($database["debug"])
		 echo "<br>SqlManager:sql_query:Sql:".$strsql;
		 
		 if(!$database["mysql_connection"]){
			//Open connection
			$database["mysql_connection"]=sql_connect($database);
		 }	 
		 
		$stmt      = $database["mysql_connection"]->query($strsql);
		$row_count = $stmt->rowCount();
	 
		return $row_count ;
		
  } catch (PDOException $e) {
  	    print "<br/>Query:".$strsql;
		print "<br/>DBError!: " . $e->getMessage() . "<br/>";
		die();
  }
		
}

function sql_query($strsql,$database)
{

  try {
	if($database["debug"])
	 echo "<br>SqlManager:sql_query:Sql:".$strsql;
	 
	 if(!$database["mysql_connection"]){
		//Open connection
		echo 'CONNECTION OPEN';
		$database["mysql_connection"]=sql_connect($database);
	 }	
 
	 
	$stmt    = $database["mysql_connection"]->query($strsql);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	 
 
	return $results ;
  } catch (PDOException $e) {
  	    print "<br/>Query:".$strsql;
		print "<br/>DBError!: " . $e->getMessage() . "<br/>";
		die();
  }	
}

// just a wrapper
function sql_query_select_first($strsql,$database){
	return sql_query_get_first($strsql,$database);
}

function sql_query_get_first($strsql,$database)
{

  try {
	 
	if($database["debug"])
	 echo "<br>SqlManager:sql_query:Sql:".$strsql;
	 
	 if(!$database["mysql_connection"]){
		//Open connection
		$database["mysql_connection"]=sql_connect($database);
	 }	 
	 
	 $stmt    = $database["mysql_connection"]->query($strsql); 
	 
	 $first_row=array();
	 while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {		
		$first_row= $row;
		break;
     } 
	  
    return  $first_row ;
    
  } catch (PDOException $e) {
  	    print "<br/>Query:".$strsql;
		print "<br/>DBError!: " . $e->getMessage() . "<br/>";
		die();
  }    
}
 

function sql_query_insert_getId($strsql,$database)
{

  try {
 
	if($database["debug"])
	 echo "<br>SqlManager:sql_query:Sql:".$strsql;
	 
	 if(!$database["mysql_connection"]){
		//Open connection
		$database["mysql_connection"]=sql_connect($database);
	 }	
	
	$RES_DATA=array();
	$RES_DATA["res"]           = $database["mysql_connection"]->exec($strsql);
    $RES_DATA["res_id"]        = $database["mysql_connection"]->lastInsertId();
  
	return $RES_DATA;
	
  } catch (PDOException $e) {
  	    print "<br/>Query:".$strsql;
		print "<br/>DBError!: " . $e->getMessage() . "<br/>";
		die();
  }	
}

function sql_query_insert($strsql,$database)
{

  try {
	$res    = $database["mysql_connection"]->exec($strsql);
	  
	return $res;
  } catch (PDOException $e) {
  	    print "<br/>Query:".$strsql;
		print "<br/>DBError!: " . $e->getMessage() . "<br/>";
		die();
  }	
}
 
 
function sql_num_rows($rs)
{
	return @mysql_num_rows($rs); 
}

function sql_fetch_array($rs)
{
	return mysql_fetch_array($rs);
}
 
function sql_fetch_object($rs)
{
	return mysql_fetch_object($rs);
}

function sql_free_result($rs)
{
	@mysql_free_result($rs);
    if($database["debug"]){    sql_error_echo();}
}

function sql_data_seek($rs,$cnt)
{
	@mysql_data_seek($rs, $cnt);
}

function sql_error()
{
	return mysql_error();
}
 
function sql_error_echo()
{
	echo "<br>SqlErroreeEcho:".mysql_error();
}


function sql_db2html($val)
{ 
	return $val;
}



function sql_query_count($database,$baseSql,$where=NULL){
		$sql = "
			SELECT 
				count(*) as Count 
			FROM
				($baseSql";
		if($where){
			$sql.=" $where";
		}
		$sql.=") f";
		$res		= sql_query_select_first($sql,$database);
		if($res) return $res['Count'];
		return 0;
}
 
?>