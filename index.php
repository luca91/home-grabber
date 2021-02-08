<?php 



$isproduction=true;


$WEB_SERVER_BASE="http://localhost";
$dbhost="localhost";
$dbuser="root";
$dbpass="";
$dbname="grabber_case";
if($isproduction){//
    $dbhost="";
    $dbuser="";
    $dbpass="";
    $dbname="";
} 
if($isproduction){//
    $dbhost="localhost";
    $dbuser="";
    $dbpass="";
    $dbname="";
	 
	 
	 

error_reporting(E_ALL);
set_time_limit(99999);
ini_set('display_errors', 1);

$limit=2000;
$max_images_num=4;


$defmodlitaquery =array();
$defmodlitaquery["0"]="Prezzo a mq2";
$defmodlitaquery["-1"]="Tutti";
$defmodlitaquery["1"]="Ultimi 100 prezzo/mq2";
$defmodlitaquery["2"]="Superindice";
$defmodlitaquery["3"]="Prezzo e mq2";
$defmodlitaquery["4"]="Ultimi"; 
$defmodlitaquery["5"]="Agenzia/ultimi"; 

//Limit
if(@is_numeric($_GET["limit"])){
	$limit=$_GET["limit"];
}

$deflocations=array("--Filtra Citta--","BOZEN");
 if(!isset($_GET['deflocation'] )){
	$_GET['deflocation']="--Filtra Citta--";
}

 

//filtermode
$filtermode=5; //Default mode
if(isset($_GET["filtermode"] )){
	$filtermode=intval($_GET["filtermode"]); 
}
$_GET["filtermode"]=$filtermode;

//Mailmode
$distinct_agenzia_limit=-1;
$distinct_total_limit=-1;
$mailmode=false; 
if(@$_GET["mailmode"]=="garagelaives"){
	$_GET["filtermode"]=$_GET["mailmode"];
}
else if(@$_GET["mailmode"]=="caseagenzie"){
	$mailmode=true;
	$limit=9999999999;
	$distinct_agenzia_limit=6;
	$distinct_total_limit=300;
}
if(  $filtermode=="5"){ 
	$limit=9999999999;
	$distinct_agenzia_limit=9999;
	$distinct_total_limit=30000000;
	
	//$_GET["deflocation"]="BOZEN";
}
 
 $limit=2000;

if(!isset($_GET['maxprice'] )){
	@$_GET['maxprice']="";
}

if(!isset($_GET['minprice'] )){
	@$_GET['minprice']="";
}


if(!isset($_GET['mindim'] )){
	//@$_GET['mindim']=60;
}
if(!isset($_GET['maxdim'] )){
	@$_GET['maxdim']="";
}
if(!isset($_GET['maxemq'] )){
	//@$_GET['maxemq']=3;
}
if(!isset($_GET['minemq'] )){
	//@$_GET['minemq']=80;
}
if(!isset($_GET['txt'] )){
	@$_GET['txt']="";
}
if(!isset($_GET['txt2'] )){
	@$_GET['txt2']="";
}
if(!isset($_GET['loc'] )){
	@$_GET['loc']="";
}
if(!isset($_GET['minroom'] )){
	@$_GET['minroom']="";
}
 
  function home_getDomainName($instr){
	  if(strlen(@$instr)<2)
		  return "nodomainfound";
		$urlp=parse_url($instr);
		return $urlp["host"];

  }

$map_url=$WEB_SERVER_BASE."/homegrabber/homegrabber_map.php?mindim=".@$_GET['mindim']."&minprice=".@$_GET['minprice']."&maxprice=".@$_GET['maxprice']."&filtermode=".@$_GET['filtermode']."&nome_agenzia=".@$_GET['nome_agenzia']."&ag_domain=".@$_GET['ag_domain']."&maxdim=".@$_GET['maxdim']."&maxemq=".@$_GET['maxemq']."&txt=".@$_GET['txt']."&txt2=".@$_GET['txt2']."&minroom=".@$_GET['minroom']."&minemq=".@$_GET['minemq'];


} 


$date = new DateTime('now'); 
$todaystr=$date->format('Y-m-d h:i');


$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
 
if ($conn->connect_errno) {
    echo "Db conn Error: " . $conn->connect_error;
    exit;
}

$sqlx="SELECT MAX(agenzia_nome) nomeag,MAX(ag_domain) agenzia,count(ag_domain) records, MAX(update_date) ultimoaggiornamento , MAX(ag_url ) ultimoarticolo FROM grabbed_articles GROUP BY ag_domain ORDER BY MAX(update_date)  desc  ";
$LAST_UPDATES=@getRecordsFromQuery($conn,$sqlx);
//print_r($LAST_UPDATES);

$sql="SELECT MAX(update_date) maxupd,MAX(id) maxid FROM grabbed_articles limit 1";
$MAX_UPDATE=@getRecordsFromQuery($conn,$sql);
$LAST_RECORD_UPDATE=$MAX_UPDATE[0];

function getRecordsFromQuery($conn,$sql){
    $result = $conn->query($sql);
    $results_array = array();  
	if(!is_null($result))
	while ($row = @$result->fetch_assoc()) {
	  $results_array[] = $row;
	} 
	
    return  $results_array;
}


function executeSqlGeneric($conn,$sql){
	if ($conn->query($sql) === TRUE) {
		echo "<br>Record updated successfully";
	} else {
		echo "<br>Error deleting record: " . $conn->error;
	}
}




 //FAVORITE
if( isset($_GET['addfav']) && is_numeric($_GET['recordid'])){
	
	$sql = "UPDATE grabbed_articles SET favorite='true' WHERE id='".$_GET['recordid']."'";
	executeSqlGeneric($conn,$sql);
	
	echo "<br>Record ".$_GET['recordid']." fav added!";
	exit(0);
}
if( isset($_GET['removefav']) && is_numeric($_GET['recordid'])){
	
	$sql = "UPDATE grabbed_articles SET favorite='false' WHERE id='".$_GET['recordid']."'";
	executeSqlGeneric($conn,$sql);
	
	echo "<br>Record ".$_GET['recordid']." fav removed!";
	exit(0);
}

//DELETE!!!
if( isset($_GET['deletelogic']) && is_numeric($_GET['recordid'])){
	
	$sql = "UPDATE grabbed_articles SET deleted='true' WHERE id='".$_GET['recordid']."'";
	executeSqlGeneric($conn,$sql);
	
	echo "<br>Record ".$_GET['recordid']." fav removed!";
	exit(0);
}
if( isset($_GET['deleterecord']) && is_numeric($_GET['recordid'])){
	
	$sql = "DELETE FROM grabbed_articles WHERE id='".$_GET['recordid']."'";
	executeSqlGeneric($conn,$sql);
	
	echo "<br>Record ".$_GET['recordid']." deleted!";
	exit(0);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CASE BOZI <?php echo $todaystr; ?></title>

		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

			<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jq-3.3.1/jszip-2.5.0/dt-1.10.18/af-2.3.3/b-1.5.6/b-colvis-1.5.6/b-flash-1.5.6/b-html5-1.5.6/b-print-1.5.6/cr-1.5.0/fc-3.2.5/fh-3.1.4/kt-2.5.0/r-2.2.2/rg-1.1.0/rr-1.2.4/sc-2.0.0/sl-1.3.0/datatables.min.css"/>
		 
		 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
		 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
		 <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jq-3.3.1/jszip-2.5.0/dt-1.10.18/af-2.3.3/b-1.5.6/b-colvis-1.5.6/b-flash-1.5.6/b-html5-1.5.6/b-print-1.5.6/cr-1.5.0/fc-3.2.5/fh-3.1.4/kt-2.5.0/r-2.2.2/rg-1.1.0/rr-1.2.4/sc-2.0.0/sl-1.3.0/datatables.min.js"></script>
		 
		 
</head>
<body>
    <center><h1><a   href="<?php echo $WEB_SERVER_BASE; ?>/homegrabber/index.php">CASE BOZI <?php echo $todaystr; ?></a></h1></center>

<?php if($mailmode==false){ ?>

	<br>
 
	Last update:<?php echo $LAST_RECORD_UPDATE["maxupd"]; ?><br>
	<a href="mailto:sniak@gmx.net">Email</a>
	<br>  
 
	<?php
		$sql_a =" SELECT DISTINCT(agenzia_nome) FROM grabbed_articles ORDER BY agenzia_nome asc"; 
        $arr_agenzie=getRecordsFromQuery($conn,$sql_a); 
		//print_r($arr_agenzie);
		
	    $sql_a =" SELECT DISTINCT(ag_domain) FROM grabbed_articles ORDER BY ag_domain asc"; 
        $arr_domains=getRecordsFromQuery($conn,$sql_a); 
		
		$sql_a =" SELECT DISTINCT(ag_indirizzo) FROM grabbed_articles ORDER BY ag_indirizzo asc"; 
        $arr_zone=getRecordsFromQuery($conn,$sql_a); 
		   
	
		 
     ?>
	 
		<table style=" vertical-align: text-top;   ">	 
			 <tr style=" vertical-align: text-top;">
			  <td style="max-width: 50%;">
			    <b>Filtri:</b><br>
				  <table style=" vertical-align: text-top;   ">	 
	 
						<form method="GET" action="index.php" > 

						<input type="hidden" name="nome_agenzia" id="nome_agenzia" value="" />
						<input type="hidden" name="ag_domain" id="ag_domain" value="" /> 
							
                          <tr ><td style="max-width:19%">
								   Modalità:
							 </td >
							 <td style="max-width:21%">
							 
								  <select name="filtermode" >
											 
									<?php
									if(count(@$defmodlitaquery )>0)
										foreach($defmodlitaquery AS $ind=>$val){
									 ?>			 
											 <option value="<?php echo $ind; ?>"
											  <?php if(@$ind==@$_GET["filtermode"])echo " SELECTED "; ?>
											  ><?php echo @$val; ?></option> 
								  <?php } ?>
									 </select>
									 
									 &nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo $WEB_SERVER_BASE; ?>/homegrabber/index.php?mailmode=true"  >mailmode</a>
	                                  &nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo $WEB_SERVER_BASE; ?>/homegrabber/index.php?filterfav=true"  >solo favoriti</a>
							 </td >
						</tr>	
						
						<tr ><td style="max-width:19%">
								   Location:
							 </td >
							 <td style="max-width:21%">
							 
								  <select name="deflocation" >
											 
									<?php
									if(count(@$deflocations )>0)
										foreach($deflocations AS $loc_name){
									 ?>			 
											 <option value="<?php echo $loc_name; ?>"
											  <?php if(@$loc_name==@$_GET["deflocation"])echo " SELECTED "; ?>
											  ><?php echo @$loc_name; ?></option> 
								  <?php } ?>
									 </select>
							 </td >
						</tr>		
						<tr ><td style="max-width:19%">
							   Agenzia
							 </td >
							 <td style="max-width:21%">
								<select    onchange="document.getElementById('nome_agenzia').value=this.options[this.selectedIndex].text"
								           style="max-width:21%" >
										 <option value="">--Filtra Agenzia--</option>
								<?php
								if(count(@$arr_agenzie )>0)
									foreach($arr_agenzie AS $AGENZIA){
								 ?>			 
										 <option  style="max-width:30%"

       										 value="<?php echo @$AGENZIA['agenzia_nome']; ?>"
											 <?php if(@$AGENZIA['agenzia_nome']==@$_GET["nome_agenzia"])echo " SELECTED "; ?>
											 ><?php echo @$AGENZIA['agenzia_nome']; ?></option> 
								 <?php } ?>
									</select>
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
							  Sito/Grabber
							 </td >
							 <td style="max-width:21%">
								 <select    onchange="document.getElementById('ag_domain').value=this.options[this.selectedIndex].text">
										 <option value="any">--Filtra Sito--</option>
								<?php
								if(count(@$arr_domains )>0)
									foreach($arr_domains AS $AGENZIA){
								 ?>			 
										 <option value="<?php echo @$AGENZIA['ag_domain']; ?>"
										  <?php if(@$AGENZIA['ag_domain']==@$_GET["ag_domain"])echo " SELECTED "; ?>
										 ><?php echo @$AGENZIA['ag_domain']; ?></option> 
								  <?php } ?>
									</select>
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Favoriti (true/false)?
							 </td >
							 <td style="max-width:21%">
								<input type="text" name="filterfav" id="filterfav" value="<?php echo @$_GET['filterfav']; ?>" />
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Min price
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="minprice" id="minprice" value="<?php echo @$_GET['minprice']; ?>" />
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Max price
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="maxprice" id="maxprice" value="<?php echo @$_GET['maxprice']; ?>" />
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Min dim
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="mindim" id="mindim" value="<?php echo @$_GET['mindim']; ?>" />
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Max dim
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="maxdim" id="maxdim" value="<?php echo @$_GET['maxdim']; ?>" />
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Min eumq
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="minemq" id="minemq" value="<?php echo @$_GET['minemq']; ?>" />
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Max eumq
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="maxemq" id="maxemq" value="<?php echo @$_GET['maxemq']; ?>" />
							 </td >
						</tr>
						<tr ><td style="max-width:19%">
								Min rooms
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="minroom" id="minroom" value="<?php echo @$_GET['minroom']; ?>" />
							 </td >
						</tr> 
						<tr ><td style="max-width:19%">
								Location
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="loc" id="loc" value="<?php echo @$_GET['loc']; ?>" />
							 </td >
						</tr> 
						<tr ><td style="max-width:19%">
								Description tags
							 </td >
							 <td style="max-width:21%">
							   <input type="text" name="txt" id="txt" value="<?php echo @$_GET['txt']; ?>" /> OR
							   <input type="text" name="txt2" id="txt2" value="<?php echo @$_GET['txt2']; ?>" />
							 </td >
						</tr> 
						<tr ><td style="max-width:19%">
								 
							 </td >
							 <td style="max-width:21%">
							    <input type="submit" name="search"   class="btn btn-primary" value="Search"/>
							 </td >
						</tr>			
								 

					 </form> 
		        </table>	
			  </td>
			  <td style="max-width: 50%;"> 
			        <b>Ultimi aggiornamenti:</b><br>
			        <ul>
			   		 <?php
					  
						if(count(@$LAST_UPDATES )>0)
							foreach($LAST_UPDATES AS $agenziaupdate){
							 
								$sqlx="SELECT (SUM((CAST(ag_prezzo AS SIGNED)))/SUM(CAST(ag_dimensioni AS SIGNED))) as  pmqagenzia FROM grabbed_articles  WHERE ag_domain='".@$agenziaupdate["agenzia"]."' AND (CAST(ag_prezzo AS SIGNED))>100 AND CAST(ag_dimensioni AS SIGNED)>40  AND (CAST(ag_prezzo AS SIGNED))<700 AND CAST(ag_dimensioni AS SIGNED)<180 GROUP BY ag_domain ";
                                $AVERAGE_PRICE_AG=@getRecordsFromQuery($conn,$sqlx);
								$averageprezzomq =@round(@$AVERAGE_PRICE_AG[0]["pmqagenzia"],2);
								
 
						 ?>			 
						 <li><a href="index.php?&filtermode=4&ag_domain=<?php echo urlencode(@$agenziaupdate["agenzia"]); ?>"><?php echo home_getDomainName(@$agenziaupdate["agenzia"]); ?></a> tot recs:<b><?php echo @$agenziaupdate["records"]; ?></b> E/mq: <u><?php echo @$averageprezzomq ; ?></u>  ult. <?php echo @$agenziaupdate["ultimoaggiornamento"]; ?> ca   <a target="_blank" href="<?php echo @$agenziaupdate["agenzia"]; ?>">apri ag.</a></li>
					  <?php } ?>
					  </ul>
			  </td>
			 </tr>
		 </table>	 
			
	 
	
<?php } ?>
	
 
	
    <div style="     margin-left: 0;">

     <?php
        $num=0;
        $sql_options ="";
        $sqlpre   = "SELECT * ";
		$sqlpre  .= " ,(((CAST(ag_prezzo AS SIGNED))/CAST(ag_dimensioni AS SIGNED))) AS prezzo_mq ";
		$sqlpre  .= " ,CAST(ag_prezzo AS SIGNED) AS ag_prezzo_int ";
		$sqlpre  .= " ,CAST(ag_dimensioni AS SIGNED) AS ag_dimensioni_int ";
		//$sqlpre  .= " ,MIN(CAST(ag_prezzo AS SIGNED)) AS ag_prezzo_min ";
		//$sqlpre  .= " ,MAX(CAST(ag_prezzo AS SIGNED)) AS ag_prezzo_max";
		//$sqlpre  .= " ,MIN(CAST(ag_dimensioni AS SIGNED)) AS ag_dimensioni_min";
		//$sqlpre  .= " ,MAX(CAST(ag_dimensioni AS SIGNED)) AS ag_dimensioni_max ";
		//$sqlpre  .= " ,MIN((CAST(ag_prezzo AS SIGNED)/CAST(ag_dimensioni AS SIGNED))) AS ag_prezzomq_pers_min ";
		//$sqlpre  .= " ,MAX((CAST(ag_prezzo AS SIGNED)/CAST(ag_dimensioni AS SIGNED))) AS ag_prezzomq_pers_max  ";
		//$sqlpre  .= "  ,((CAST(ag_prezzo AS SIGNED)/MAX(CAST(ag_prezzo AS SIGNED)))*100) AS ag_prezzo_perc ";
		$sqlpre  .= "  FROM grabbed_articles ";
		
		$sqlwhere=" WHERE  (deleted is null or deleted='false') ";
		if(@$filtermode==-1){
		  
		}else{
			//$sqlwhere.="  AND CAST(ag_prezzo AS SIGNED)>2  AND CAST(ag_prezzo AS SIGNED)<320  AND CAST(ag_dimensioni AS SIGNED)>70  AND CAST(ag_dimensioni AS SIGNED)>10  AND CAST(dist_centro AS SIGNED)<15 ";
		}
		
		//Filtra nome agenzia
		 if(@strlen(@$_GET["nome_agenzia"])>1){  
		   $sqlwhere .="  AND agenzia_nome like '%".addslashes(@$_GET["nome_agenzia"])."%' "; 
		 }
		 
		 //Filtra ag_domain agenzia
		 if(@strlen(@$_GET["ag_domain"])>1){  
		   $sqlwhere .="  AND ag_domain like '%".@$_GET["ag_domain"]."%' "; 
		 }
		 
	     //Filtra ag_description agenzia
		 if(@strlen(@$_GET["txt"])>1){  
		   $sqlwhere .="  AND ag_description like '%".@$_GET["txt"]."%' "; 
		 } 
		 if(@strlen(@$_GET["txt2"])>1){  
		   $sqlwhere .="  AND ag_description like '%".@$_GET["txt2"]."%' "; 
		 }
		 
		  if(@strlen(@$_GET["loc"])>1){  
		   $sqlwhere .="  AND ag_indirizzo like '%".@$_GET["loc"]."%' "; 
		 }
		  	
		 $sqlbozen ="   AND  ((ag_indirizzo like '%ano%' OR ag_indirizzo like '%lzano%' OR  ag_indirizzo like '%bz%' OR  ag_indirizzo like '%ozen%' ) OR (ag_description like '%zano%' OR ag_description like '%lzano%' OR  ag_description like '%bz%' OR  ag_description like '%ozen%' ))  ";
        
		if($mailmode){
			
			$sqlwhere .=$sqlbozen."    ";
			
			$sql_options .=" order by id desc";
		
		}else{
			/*
			$defmodlitaquery =array();
			$defmodlitaquery["0"]="Prezzo a mq2";
			$defmodlitaquery["-1"]="Tutti";
			$defmodlitaquery["1"]="Prezzo/mq2";
			$defmodlitaquery["2"]="Superindice";
			$defmodlitaquery["3"]="Prezzo e mq2";
			$defmodlitaquery["4"]="Ultimi"; 
			$defmodlitaquery["5"]="Agenzia/ultimi"; 
			*/

			if($filtermode==0){//Prezzo a mq
				$sql_options .=" order by CAST(prezzo_mq AS SIGNED) asc ";
		
			} 
			else if($filtermode==1){
				//$sqlwhere .="  AND id> '".(intval($LAST_RECORD_UPDATE["maxid"])-500)."' AND (ag_indirizzo like '%lzano%' OR  ag_indirizzo like '%bz%' OR  ag_indirizzo like '%ozen%' )";
				
				$sql_options .=" order by CAST(prezzo_mq AS SIGNED) asc ";
		
			}
			else if($filtermode==2){
				//$sqlwhere.="   AND CAST(ag_dimensioni AS SIGNED)>10 AND CAST(dist_centro AS SIGNED)>0 AND CAST(dist_centro AS SIGNED)<10 ";
				$sql_options .=" order by CAST(prezzo_mq AS SIGNED) asc ";
		
			}
			else if($filtermode==3){
			    //$sqlwhere .="  AND CAST(dist_centro AS SIGNED)>0 ";
				$sql_options .=" order by    CAST(prezzo_mq AS SIGNED) asc ,CAST(ag_dimensioni AS SIGNED) desc";
				 
		
			}	
            else if($filtermode==4){
			    //$sqlwhere .="  AND CAST(dist_centro AS SIGNED)>0 ";
				$sql_options .=" order by  id desc";
				//$sql_options .=" order by    (ag_dimensioni AS SIGNED) desc,CAST(prezzo_mq AS SIGNED) desc ";
		
			}	
           else if($filtermode==5){//Agenzia ordered
			   //$sqlwhere  .=$sqlbozen;
			
			   $sql_options .=" order by id desc";
			}				
			else{//-1
				$sql_options .=" order by  CAST(prezzo_mq AS SIGNED) asc  ";
		
			}
			
		}
		
	     if(isset($_GET["filterfav"]) && @$_GET["filterfav"]=="true"){
			 $sqlwhere .="  AND favorite='true'  "; 
		 }
		 if(isset($_GET["deflocation"]) && @$_GET["deflocation"]=="BOZEN"){
			 $sqlwhere .=$sqlbozen; 
		 
		 }
		
		if(is_numeric(@$_GET["maxprice"]) && @$_GET["maxprice"]>=0){
			 $sqlwhere .="  AND CAST(ag_prezzo AS SIGNED) <= '".@$_GET["maxprice"]."' "; 
		}
		
	    if(is_numeric(@$_GET["minprice"]) && @$_GET["minprice"]>=0){
			 $sqlwhere .="  AND CAST(ag_prezzo AS SIGNED) >= '".@$_GET["minprice"]."' "; 
		}
		
		
	    if(is_numeric(@$_GET["mindim"]) && @$_GET["mindim"]>=0){
			 $sqlwhere .="  AND CAST(ag_dimensioni AS SIGNED) >= '".@$_GET["mindim"]."' "; 
		}
	    if(is_numeric(@$_GET["maxdim"]) && @$_GET["maxdim"]>=0){
			 $sqlwhere .="  AND CAST(ag_dimensioni AS SIGNED) <= '".@$_GET["maxdim"]."' "; 
		}
		if(is_numeric(@$_GET["minroom"]) && @$_GET["minroom"]>=0){
			 $sqlwhere .="  AND CAST(ag_locali AS SIGNED) >= '".@$_GET["minroom"]."' "; 
		}
	    if(is_numeric(@$_GET["minemq"]) && @$_GET["minemq"]>=0){
			 $sqlwhere .="  AND (((CAST(ag_prezzo AS SIGNED))/CAST(ag_dimensioni AS SIGNED))) >= '".@$_GET["minemq"]."' "; 
		}
        if(is_numeric(@$_GET["maxemq"]) && @$_GET["maxemq"]>=0){
			 $sqlwhere .="  AND (((CAST(ag_prezzo AS SIGNED))/CAST(ag_dimensioni AS SIGNED))) <= '".@$_GET["maxemq"]."' "; 
		}			
		
		//$sql_options.=" , CAST(ag_locali AS SIGNED)  desc";
		


        $sql_options .=" limit ".$limit;
		
		$sql=$sqlpre. $sqlwhere .$sql_options;
		
        $sql_o=$sql; 
		
		//echo $sql;
		
        $arr_case=@getRecordsFromQuery($conn,$sql);
	 
		 
		//MINS MAXES
		 
		$sqlpre  = "SELECT ";
		$sqlpre  .= " MIN(CAST(ag_prezzo AS SIGNED)) AS ag_prezzo_min, ";
		$sqlpre  .= " MAX(CAST(ag_prezzo AS SIGNED)) AS ag_prezzo_max, ";
		$sqlpre  .= " MIN(CAST(ag_dimensioni AS SIGNED)) AS ag_dimensioni_min,";
		$sqlpre  .= " MAX(CAST(ag_dimensioni AS SIGNED)) AS ag_dimensioni_max, ";
		$sqlpre  .= " MIN(CAST(ag_locali AS SIGNED)) AS ag_locali_min,";
		$sqlpre  .= " MAX(CAST(ag_locali AS SIGNED)) AS ag_locali_max, ";		
		$sqlpre  .= " MIN(CAST(dist_centro AS SIGNED)) AS dist_centro_min,";
		$sqlpre  .= " MAX(CAST(dist_centro AS SIGNED)) AS dist_centro_max, ";		
		$sqlpre  .= " MIN((CAST(ag_prezzo AS SIGNED)/CAST(ag_dimensioni AS SIGNED))) AS ag_prezzomq_pers_min, ";
		$sqlpre  .= " MAX((CAST(ag_prezzo AS SIGNED)/CAST(ag_dimensioni AS SIGNED))) AS ag_prezzomq_pers_max  ";
		
		$sqlpre  .= "   FROM grabbed_articles  ";
		$newsql=$sqlpre. $sqlwhere." LIMIT 1";
		// echo "<br>newsql2=".$newsql;
		$arr_case_minmaxes_price=@getRecordsFromQuery($conn,$newsql); 
		// echo "<br>RECORDS FOUND:<b>".count( @$arr_case_minmaxes_price);//."</b>    Sql:".$sql ."<br>";
		$arr_case_minmaxes_price=@$arr_case_minmaxes_price[0]; 
		
		if($mailmode==false){
					//echo "<br>ag_dimensioni_min=".@$arr_case_minmaxes_price["ag_dimensioni_min"]. "  ag_dimensioni_max=".@$arr_case_minmaxes_price["ag_dimensioni_max"]."   ag_prezzo_min=".@$arr_case_minmaxes_price["ag_prezzo_min"]. "  ag_prezzo_max=".@$arr_case_minmaxes_price["ag_prezzo_max"]	."  dist_centro_min=".@$arr_case_minmaxes_price["dist_centro_min"]. "  dist_centro_max=".@$arr_case_minmaxes_price["dist_centro_max"]."  ag_prezzomq_pers_min=".@$arr_case_minmaxes_price["ag_prezzomq_pers_min"]. "  ag_prezzomq_pers_max=".@$arr_case_minmaxes_price["ag_prezzomq_pers_max"];
		
		}

		//Superindex
		$arr_case_skip=array();
		 foreach($arr_case as $ind=>$CASA) { 
		 
		  
					$prezzo_mq1=(@doubleval( $arr_case[$ind]["prezzo_mq"])); 
					$dist_centro1=(@doubleval( $arr_case[$ind]["dist_centro"])); 
					$ag_locali=(@intval( $arr_case[$ind]["ag_locali"])); 
					$ag_dimensioni=(@intval( $arr_case[$ind]["ag_dimensioni"])); 
					
					if (strpos(strtolower( $CASA["ag_indirizzo"]), 'olzano') !== false) {
						$dist_centro1=2;
					 }
					
					if($dist_centro1<=1){
					 $dist_centro1=doubleval(@$arr_case_minmaxes_price["dist_centro_max"]); 
					} 
					if($prezzo_mq1<=0){ 
					 $prezzo_mq1=doubleval(@$arr_case_minmaxes_price["ag_prezzomq_pers_max"]);
					} 
					$dist_perc1    =getPercentuale(false,$dist_centro1,@$arr_case_minmaxes_price["dist_centro_min"],@$arr_case_minmaxes_price["dist_centro_max"]);  
					if($dist_perc1<=0){
						$dist_perc1=100;//Peggioralo
						

						//$arr_case_skip[$ind]=true;
					}
					$prezzomq_perc1=getPercentuale(false,$prezzo_mq1,@$arr_case_minmaxes_price["ag_prezzomq_pers_min"],@$arr_case_minmaxes_price["ag_prezzomq_pers_max"]); 
					if($prezzomq_perc1<=1){
						$prezzomq_perc1=100;//Peggioralo
					}
					
					$ag_locali_perc1=getPercentuale(true,$ag_locali,@$arr_case_minmaxes_price["ag_locali_min"],@$arr_case_minmaxes_price["ag_locali_max"]); 
					if($ag_locali_perc1<=1){
						$ag_locali_perc1=100;//Peggioralo
					}
					
			        $ag_dimensioni_perc=getPercentuale(true,$ag_dimensioni,@$arr_case_minmaxes_price["ag_dimensioni_min"],@$arr_case_minmaxes_price["ag_dimensioni_m"]); 
					if($ag_dimensioni_perc<=1){
						$ag_dimensioni_perc=100;//Peggioralo
					}
					
					//$indice1=1000000/($prezzomq_perc1*$dist_perc1); 
					$indice1=(1/(
						 //(0.2*(100/$dist_perc1))*
						  (0.4*(100/$prezzomq_perc1))
						 // (0.3*(100/$ag_dimensioni_perc))*
						  //(0.1*(100/$ag_locali_perc1)))
					  )*10000);
					
					//echo "<br>indice1=".$indice1."         prezzo_mq1=".@$prezzo_mq1. "  dist_centro1=".@$dist_centro1."   dist_perc1=".@$dist_perc1. "  prezzomq_perc1=".@$prezzomq_perc1;
					$arr_case[$ind]["dist_perc"]=$dist_perc1;
					$arr_case[$ind]["prezzomq_perc"]=$prezzomq_perc1;
					$arr_case[$ind]["ag_locali_perc"]=$ag_locali_perc1;
					$arr_case[$ind]["ag_dimensioni_perc"]=$ag_dimensioni_perc;
					
					
		            $arr_case[$ind]["superindex"]=$indice1;
					
					
					
		 } 
		
		//SUPERINDICE
		if($filtermode==2){
			    echo "<br>Attivo filtro per superindex";
				 
				 //SORT INDICE 
				usort($arr_case, function(array $a, array $b) use($arr_case_minmaxes_price){

		 
					$indice1=(doubleval($a["superindex"]));
					$indice2=(doubleval($b["superindex"]));
					  
					 
					if($indice1==$indice2){
						return 0;
					}
					 
					return ($indice1 < $indice2) ? -1 : 1;
						
				});
		}
	 
		function getPercentuale($percinversa,$value,$min,$max){
			if($value>0 && $min>0 && $max>0){
				$res=@round( ((doubleval($value) - doubleval($min)) * 100) / (doubleval($max) - doubleval($min)) ,2); 
			}else{
				$res=100;
			}
			
			
			if($percinversa==true){
				$res=100-$res;
			} 
			//echo "<br>getPercentuale     res=".$res."       value=".@$value."   min=".@$min. "  max=".@$max;
					
			return $res;
		} 
		 
		
		$distinct_agenzia_count=array();
		$effective_added=0;
        echo "<br>RECORDS FOUND:<b>".count( @$arr_case);//."</b>    Sql:".$sql ."<br>";
     ?>
        <table  width="100%"  class="table table-striped table-responsive-md btn-table" >
            <thead>
                <th>Num/Superindex</th> 
				<th>EUR/mq</th>
				<th>DistBZ</th>
				
                <th>URL</th>  
                <th>Prezzo</th>
                <th>Dimensioni</th>
		        <th>Indirizzo</th>
                <th>Immagini</th>
				
                <th>Titolo</th>
                <th>Descrizione</th> 
				<th>Num Locali</th>
                <th>Piano</th> 

                <th>Anno costr</th>
				<th>Agenzia</th>
                <th>Tipo prop</th>
                <th>Classe EN</th>
                <th>Stato casa</th>
                <th>Riscaldam</th> 
            </thead>
            <tbody> 
                <?php  
                 if(true) { ?> 
                    <?php foreach($arr_case as $ind=>$casa) { $num++;
					          
							if(isset($arr_case_skip[$ind])  )
								continue;

							//Agenzia max recs limit
							if($distinct_agenzia_limit>0 && strlen(@$casa["ag_domain"])>5){
								if(!is_numeric( @$distinct_agenzia_count[@$casa["ag_domain"]])){
									$distinct_agenzia_count[@$casa["ag_domain"]]=0;
								}
								$distinct_agenzia_count[@$casa["ag_domain"]]++; 
								if($distinct_agenzia_count[@$casa["ag_domain"]]>$distinct_agenzia_limit){
									continue;
								}
							}
							if($mailmode=="true"  || $filtermode=="5"){
								if($distinct_total_limit<0){
									continue;
								}
								$distinct_total_limit--;
							}

                           $effective_added++;

                            $sql = "SELECT (a_key) a_key,(a_value) a_value FROM grabbed_articles_meta ";
                            $sql .="   WHERE  ag_id='".$casa["ag_id"]."' AND a_key like 'image%' limit 6 ";
                            $arr_casa_meta=getRecordsFromQuery($conn,$sql);
                        
                        ?>
                        <tr>
                            <td><?php echo @$num; ?>(<?php echo @$casa['id']; ?>)fav:<?php echo @$casa['favorite']; ?>
								<br>
								 <div style="color:red"><?php echo @$casa['superindex']; ?>  (<?php echo @$casa['prezzomq_perc']; ?>%mq,<?php echo @$casa['dist_perc']; ?>%dist,<?php echo @$casa['ag_dimensioni_perc']; ?>%dim) </div>
								 
								  <br><a target="_blank" href="index.php?deletelogic=true&recordid=<?php echo @$casa['id']; ?>">Delete</a>
							     <br> <a target="_blank" href="index.php?addfav=true&recordid=<?php echo @$casa['id']; ?>">++Fav</a>
							     &nbsp; <a target="_blank" href="index.php?removefav=true&recordid=<?php echo @$casa['id']; ?>">--favFav</a>
								 <br>
								 agg:<?php echo @$casa['update_date']; ?>
							 </td>
                             
							<td><?php echo @$casa['prezzo_mq']; ?></td>
							<td><?php echo @$casa['dist_centro']; ?></td>
							
							
							<td><a target="_blank" href="<?php echo (@$casa['ag_url']); ?>">Link <?php echo home_getDomainName(@$casa['ag_domain']); ?></a></td> 
                            <td><?php echo @$casa['ag_prezzo']; ?></td>
                            <td><?php echo @$casa['ag_dimensioni']; ?></td>
							
							
							 <td>
							<a target="_blank" href="https://www.google.com/maps/place/<?php echo @$casa['ag_indirizzo']; ?>">
							 <?php echo @$casa['ag_indirizzo']; ?>
							 </a>
							 </td>
                            <td><?php
                            if(!empty($arr_casa_meta)) {
							$imgnum=0;
                             foreach($arr_casa_meta as $meta) {// echo @$meta['a_key']."=".@$meta['a_value']."<br>";
                                //Images
                                if((@substr( @$meta['a_key'], 0, 5 ) === "image")){ $imgnum++;?>
                                   
                                 <?php 
								     if($imgnum>50){
										 break;
								     }
								  
								    if($imgnum>$max_images_num){?>
									&nbsp; <a target="_blank" href="<?php echo @stripslashes(@$meta['a_value']); ?>">
                                      Img<?php echo (@$imgnum); ?>
                                    </a>
								<?php }else{
									
									?>
									 <a target="_blank" href="<?php echo @stripslashes(@$meta['a_value']); ?>">
                                      <img src="<?php echo @stripslashes(@$meta['a_value']); ?>" height="130px">
                                    </a>
									<?php
								    }
								 } 
                             }
                            }
                            ?></td> 
							
							
							
                            <td><?php echo @$casa['ag_title']; ?></td>
                            <td><?php echo @$casa['ag_description']; ?></td>  
							<td><?php echo @$casa['ag_locali']; ?></td>
                            <td><?php echo @$casa['ag_piano']; ?></td> 
                            <td><?php echo @$casa['ag_annocostruzione']; ?></td> 
							<td><?php echo @$casa['agenzia_nome']; ?></td> 
                            <td><?php echo @$casa['ag_tipoproprieta']; ?></td>
                            <td><?php echo @$casa['ag_classe_energetica']; ?></td>
                            <td><?php echo @$casa['ag_statocasa']; ?></td>
                            <td><?php echo @$casa['ag_riscaldamento']; ?></td>
                            <!--
                            <td><?php
                            if(false && !empty($arr_casa_meta)) {
                             foreach($arr_casa_meta as $meta) {// echo @$meta['a_key']."=".@$meta['a_value']."<br>";
                                //MEta
                                if(!(@substr( @$meta['a_key'], 0, 5 ) === "image")){?>
                                     <?php echo @$meta['a_key']; ?>=<?php echo @$meta['a_value']; ?><br>
                                 <?php } 
                             }
                            }
                            ?></td>--> 
							 
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>

<br>
        <center><h1>FINE ANNUNCI</h1></center>
    </div> 
    <script>
        $(document).ready(function() {
            /*
			$('#mainTab').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],                
                "paging": false,
                "bJQueryUI":true,
                "bSort":true, 
                "columnDefs": [
                    { "width": "20%", "targets": 0 }
                ],          
                columnDefs: [ {
                    targets: [ 0 ],
                    orderData: [ 0, 1 ]
                }, {
                    targets: [ 1 ],
                    orderData: [ 1, 0 ]
                }, {
                    targets: [ 4 ],
                    orderData: [ 4, 0 ]
                } ]
            } );
			*/
 
        } );
    </script>
	
	
	<?php
		 echo "<br>Displayed records:".$effective_added;
		 echo "<br>SQL=".$sql_o;
		 
		?>
		<br>
		<a href="<?php echo $WEB_SERVER_BASE; ?>/homegrabber/homegrabber.php?cleandb=true" target="_blank">Clean database</a>
</body>
</html>