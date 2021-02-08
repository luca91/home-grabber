<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


//filtermode
$filtermode=0; 
if(is_numeric(@$_GET["filtermode"] )){
	$filtermode=intval($_GET["filtermode"]); 
}
 

$isproduction=true;

$dbhost="localhost";
$dbuser="root";
$dbpass="";
$dbname="grabber_case";
if($isproduction){
    $dbhost="";
    $dbuser="";
    $dbpass="";
    $dbname="";
} 
if($isproduction){//Papin
    $dbhost="localhost";
    $dbuser="phpmyadmin";
    $dbpass="";
    $dbname="";
	 
} 


function getRecordsFromQuery($conn,$sql){
    $result = $conn->query($sql);
    $results_array = array();  
	if(!is_null($result))
	while ($row = @$result->fetch_assoc()) {
	  $results_array[] = $row;
	} 
	
    return  $results_array;
}

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
 
if ($conn->connect_errno) {
    echo "Db conn Error: " . $conn->connect_error;
    exit;
}

 

$sql_options ="";
$sqlpre   = "SELECT * ";
$sqlpre  .= " ,(((CAST(ag_prezzo AS SIGNED))/CAST(ag_dimensioni AS SIGNED))) AS prezzo_mq ";
$sqlpre  .= " ,CAST(ag_prezzo AS SIGNED) AS ag_prezzo_int ";
$sqlpre  .= " ,CAST(ag_dimensioni AS SIGNED) AS ag_dimensioni_int "; 
$sqlpre  .= "  FROM grabbed_articles ";

$sqlwhere=" WHERE 1=1 ";
		
if($filtermode==1){
	$sql_options .=" order by id desc, CAST(ag_prezzo AS SIGNED) desc, CAST(ag_dimensioni AS SIGNED) desc ";

}
else if($filtermode==2){ 
	$sql_options .=" order by prezzo_mq desc ";

}
else if($filtermode==3){ 
	$sql_options .=" order by   CAST(dist_centro AS SIGNED) asc ,CAST(prezzo_mq AS SIGNED) asc ,CAST(ag_dimensioni AS SIGNED) desc";

}			
else{
	$sql_options .=" order by  CAST(ag_prezzo AS SIGNED) desc, CAST(ag_dimensioni AS SIGNED) desc ";

}

if(is_numeric($_GET["maxprice"]) && @$_GET["maxprice"]>=0){
 $sqlwhere .="  AND CAST(ag_prezzo AS SIGNED) <= '".@$_GET["maxprice"]."' "; 
}

if(is_numeric($_GET["minprice"]) && @$_GET["minprice"]>=0){
 $sqlwhere .="  AND CAST(ag_prezzo AS SIGNED) >= '".@$_GET["minprice"]."' "; 
}

if(is_numeric($_GET["mindim"]) && @$_GET["mindim"]>=0){
 $sqlwhere .="  AND CAST(ag_dimensioni AS SIGNED) >= '".@$_GET["mindim"]."' "; 
}

//Filtra nome agenzia
if(@strlen(@$_GET["nome_agenzia"])>=1){  
 $sqlwhere .="  AND agenzia_nome like '%".@$_GET["nome_agenzia"]."%' "; 
}

//Filtra ag_domain agenzia
if(@strlen(@$_GET["ag_domain"])>=1){  
 $sqlwhere .="  AND ag_domain like '%".@$_GET["ag_domain"]."%' "; 
}

if(@strlen(@$_GET["txt"])>=1){  
 $sqlwhere .="  AND ag_description like '%".@$_GET["txt"]."%' "; 
}
if(@strlen(@$_GET["txt2"])>=1){  
 $sqlwhere .="  AND ag_description like '%".@$_GET["txt2"]."%' "; 
}

if(@strlen(@$_GET["loc"])>1){  
$sqlwhere .="  AND ag_indirizzo like '%".@$_GET["loc"]."%' "; 
}

if(is_numeric($_GET["maxdim"]) && @$_GET["maxdim"]>=0){
	 $sqlwhere .="  AND CAST(ag_dimensioni AS SIGNED) <= '".@$_GET["maxdim"]."' "; 
}

if(is_numeric($_GET["emq"]) && @$_GET["emq"]>=0){
	 $sqlwhere .="  AND (((CAST(ag_prezzo AS SIGNED))/CAST(ag_dimensioni AS SIGNED))) <= '".@$_GET["emq"]."' "; 
}	

$query=$sqlpre. $sqlwhere .$sql_options;
$result=getRecordsFromQuery($conn,$query); 
 

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';
$kml[] = ' <Style id="restaurantStyle">';
$kml[] = ' <IconStyle id="restuarantIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon63.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
$kml[] = ' <Style id="barStyle">';
$kml[] = ' <IconStyle id="barIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon27.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' <LabelStyle>';
$kml[] = '          <color>ff0000cc</color>';
$kml[] = '          <colorMode>random</colorMode>';
 $kml[] = '         <scale>1.5</scale>';
 $kml[] = '      </LabelStyle>';
$kml[] = ' </Style>';
$kml[] = '<Style id="exampleBalloonStyle">';
    $kml[] = '<BalloonStyle>';
      $kml[] = '<!-- a background color for the balloon -->';
      $kml[] = '<bgColor>ffffffbb</bgColor>';
      $kml[] = '<!-- styling of the balloon text -->';
      $kml[] = '<text><![CDATA[';
      $kml[] = '<b><font color="#CC0000" size="+3">$[name]</font></b>';
      $kml[] = '<br/><br/>';
      $kml[] = '<font face="Courier">$[description]</font>';
      $kml[] = '<br/><br/>';
      $kml[] = 'Extra text that will appear in the description balloon';
      $kml[] = '<br/><br/>';
      $kml[] = '<!-- insert the to/from hyperlinks -->';
      $kml[] = '$[geDirections]';
      $kml[] = ']]></text>';
    $kml[] = '</BalloonStyle>';
  $kml[] = '</Style>';
  
      $kml[] = '<Style id="pushpin">';
      $kml[] = '<IconStyle id="mystyle"><color>ffffffff</color><colorMode>normal</colorMode>   ';
       $kml[] = ' <Icon>';
       $kml[] = '   <href>http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href>';
        //$kml[] = '  <scale>0.5</scale>';
        $kml[] = '</Icon>';
      $kml[] = '</IconStyle>';
    $kml[] = '</Style>';

// Iterates through the rows, printing a node for each row.
foreach ($result as $row)
{
	if(@strlen(@$row['geoc_longitude'])>1
	   //&& $row['ag_url']!="https://www.lifandi.it/it/immobilien-immobili/merano-corso-d-liberta-attico-con-terrazza-panoramica"
	){
		
		$link='<a href="'.htmlentities(@$row['ag_url']).'">OPEN</a> || <a target="_blank" href="index.php?addfav=true&recordid='. @$row['id'].'">++Fav</a> || <a target="_blank" href="index.php?removefav=true&recordid='. @$row['id'].'">--favFav</a> || <a target="_blank" href="index.php?deletelogic=true&recordid='. @$row['id'].'">Del</a>';
		
	  $kml[] = ' <Placemark id="placemark' . @$row['id'] . '">';
	  $kml[] = ' <name>' .htmlentities(@$row['prezzo_mq']). ") ". htmlentities(@$row['ag_prezzo'])."€ ". htmlentities(@$row['ag_dimensioni']).'mq</name>';
	  
	  $kml[] = ' <description>' . htmlentities(@$link) . '</description>';
	  
      $kml[] = ' <styleUrl>#barIcon</styleUrl>';
	  $kml[] = ' <Point>';
	  $kml[] = ' <coordinates>' . $row['geoc_longitude'] . ','  . $row['geoc_latitude'] . '</coordinates>';
	  $kml[] = ' </Point>';
	  $kml[] = ' </Placemark>';
	}
  
 
} 

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>