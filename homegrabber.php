<?php

error_reporting(E_ALL);
 ini_set('default_socket_timeout', 30); //secs

set_time_limit(9999999999);

$isproduction=true;


//Command line argument?
if(isset($argv) && count($argv)>0){
	foreach ($argv as $arg) {
		$e=explode("=",$arg);
		if(count($e)==2)
			$_GET[$e[0]]=$e[1];
		else   
			$_GET[$e[0]]=0;
	}
} 

$WEB_SERVER_BASE="http://localhost";
$dbhost="localhost";
$dbuser="root";
$dbpass="";
$dbname="grabber_case";
if($isproduction){//Aruba
    $dbhost="";
    $dbuser="";
    $dbpass="";
    $dbname="";
} 
if($isproduction){//Papin
    $dbhost="";
    $dbuser="";
    $dbpass="";
    $dbname="";
	 
} 



//Mailmode
$mailmode=false; 
if(isset($_GET["mailmode"])){
	$mailmode=true; 
}
 

//require_once("lib/simple_html_dom.php");
require_once("vendor/autoload.php"); 
require_once("lib/PHPMailer/PHPMailerAutoload.php");

/*

IMMOBILIARE FUSINA
*/
echo '<html><head> <meta charset="UTF-8"> </head><body>';
echo "<br>*************** *************************** *****************";
echo "<br>************* GRABBER IMMOBILIARI BOLZANO *************";
echo "<br>*************** *************************** *****************";
echo "<br>************************ ".date("Y-m-D h:i:s")." ************************";

if($mailmode){
	$title='Casa Bozi - Newsletter!';
	$html_content=file_get_contents($WEB_SERVER_BASE."/homegrabber/index.php?mailmode=caseagenzie");
	sendEmaile("raffamondo@gmail.com","raffamondo@gmail.com",$title,$html_content,"" );
	die();
}

echo "<br>Init database..";
$connection = new PDO('mysql:host='.$dbhost.';dbname='.$dbname.'', $dbuser, $dbpass);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo "<br>Init database.. ok";
//https://clancats.io/hydrahon/v1.1.6/#runners
$database=array();
$database["connection"] = $connection;
$database["hydrahon"] = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
{ 
    //echo "<br>Sql.Query in:".$queryString; 
    if(true){
        $queryStringParams=$queryString;
        foreach($queryParameters AS $KEY=>$VAL ){
            $queryParameters[$KEY]=addslashes(@$VAL);
            $queryStringParams    =preg_replace('/\?/',"'". @$queryParameters[$KEY]."'", $queryStringParams, 1);
        }
        //echo "<br>Sql.Query params out:".$queryStringParams;
    }
    
    $statement = $connection->prepare($queryString);
    @$statement->execute($queryParameters);
 
    if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface)
    { 
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
       

    }
});

//------------------------------------------------Delete some records
if(isset($_GET["cleandb"])){
	echo "<br>Start delete queries....";
    $tab_grabbed_articles = $database["hydrahon"]->table("grabbed_articles")->delete()->where("id", '>', 0)->execute();
	$tab_grabbed_articles = $database["hydrahon"]->table("grabbed_articles_meta")->delete()->where("id",'>', 0)->execute();
	exit;
} 

//Grabber azimmobiliare
// require_once("homegrabber_azimmobiliare.php");   exit;

//Grabber aste 
require_once("homegrabber_aste.php"); 

//Grabber  sparkasse
require_once("homegrabber_sparkasse.php");   

//Grabber realpro
require_once("homegrabber_realpro.php"); 

//Grabber immo dolomiti
 require_once("homegrabber_immodolomiti.php");    

//Grabber bzimmobiliare
 require_once("homegrabber_bzimmobiliare.php");  
 
//Grabber benedikter
 require_once("homegrabber_benedikter.php");   
 
//Grabber lorenzon
 require_once("homegrabber_lorenzon.php");  

//Grabber ruth
 require_once("homegrabber_ruth.php"); 

//Grabber rossi
 require_once("homegrabber_rossi.php");  

//Grabber talvera
 require_once("homegrabber_talvera.php"); 

//Grabber metropolis
 require_once("homegrabber_metropolis.php"); 
 
//Grabber immobiliarebm
 require_once("homegrabber_immobiliarebm.php"); 

//Grabber abc
require_once("homegrabber_abcimmo.php");    

//Grabber cento case
require_once("homegrabber_centocase.php");   

//Grabber casa haus
require_once("homegrabber_casahaus.php");   

//Grabber immobilien suedtirol
require_once("homegrabber_immobiliensuedtirol.php");  


//Grabber futuro immobiliare
require_once("homegrabber_futuroimmobiliare.php");  

//Grabber immos
require_once("homegrabber_immos.php"); 

//Grabber rsimmo
require_once("homegrabber_rsimmo.php"); 

//Grabber gentilini
require_once("homegrabber_gentilini.php"); 

//Grabber opera
require_once("homegrabber_opera.php"); 

//Grabber dimensione casa bz
require_once("homegrabber_dimensionecasabz.php"); 

//Grabber   igordapunt
require_once("homegrabber_igordapunt.php");


//Grabber sabrina manzetti
require_once("homegrabber_sabrina.php"); 

//Grabber toninandel
require_once("homegrabber_toninandel.php"); 
 
//Grabber ehrenstein
require_once("homegrabber_ehrenstein.php"); 



 //Grabber fusina
require_once("homegrabber_immobiliarefusina.php"); 



//Grabber  Donofrio 
require_once("homegrabber_donofrio.php");  

//Grabber  Rimmo 
require_once("homegrabber_rimmo.php");  

//Grabber  Residence immo 
require_once("homegrabber_residenceimmo.php");  

//Grabber giglio
require_once("homegrabber_immobiliaregiglio.php");
 
   

//Grabber bolzanoprogetto
require_once("homegrabber_bolzanoprogetto.php");  

//Grabber bolzanoprogetto
require_once("homegrabber_studiobolzano.php");   







//Grabber wiki casa
//require_once("homegrabber_wikicasa.php"); 


 

//Grabber  lamaison
//require_once("homegrabber_lamaison.php"); exit;




//Grabber subito 
require_once("homegrabber_subito.php");

//Grabber lifandi 
require_once("homegrabber_lifandi.php");


 

	
//Grabber Gutzmer
require_once("homegrabber_gutzmeru.php"); 

//Grabber montecchio
require_once("homegrabber_montecchio.php");  
 

//Grabber bonetto
require_once("homegrabber_bonetto.php");  

//Grabber studioalfa
require_once("homegrabber_studioalfa.php");  

//Grabber oberrrauch
require_once("homegrabber_oberrauch.php");

//Grabber immo-bozen
require_once("homegrabber_immobozen.php");

//Grabber immoweb
require_once("homegrabber_immoweb.php");
 
//Grabber abitarehaus
require_once("homegrabber_abitarehaus.php");



  
//Grabber immobiliareit:immobili
$immobiliare_tipograb=null;
//require_once("homegrabber_immobiliareit.php");

//Grabber immobiliareit:aste
$immobiliare_tipograb=1;
//require_once("homegrabber_immobiliareit.php");








//Output all articles
$grabbed_articles=$database["hydrahon"]->table("grabbed_articles")->select()->execute(); 
//echoArray( $grabbed_articles);

//Send alerts




echo "<br>*************** GRABBING FINISHED *****************";
echo "<br>************************ ".date("Y-m-D h:i:s")." ************************";



 

/*


******************
     FUNCTIONS
******************


*/


function sendEmaile($from_mail,$to_mail,$title,$html_content,$to_mail2="" ){ 

		$MAIL_PROPS["smtp_host"]=""; 
		$MAIL_PROPS["smtp_port"]=465;
		$MAIL_PROPS["smtp_secure"]='ssl';
		$MAIL_PROPS["smtp_auth"]=true;
		$MAIL_PROPS["smtp_usr"]="";
		$MAIL_PROPS["smtp_pwd"]="";  
	 

		try {
		    $mail              = new PHPMailer();
			
			$mail->SMTPDebug  = 2;
			$mail->Debugoutput = 'html';
			
			$mail->isSMTP();                                  // Set mailer to use SMTP
			$mail->Host        = $MAIL_PROPS["smtp_host"];  // Specify main and backup SMTP servers
			$mail->SMTPAuth    = $MAIL_PROPS["smtp_auth"];    // Enable SMTP authentication
			$mail->Username    = $MAIL_PROPS["smtp_usr"];     // SMTP username
			$mail->Password    = $MAIL_PROPS["smtp_pwd"];     // SMTP password
			$mail->SMTPSecure  = $MAIL_PROPS["smtp_secure"];  // Enable TLS encryption, `ssl` also accepted
			$mail->Port        = $MAIL_PROPS["smtp_port"];    // TCP port to connect to
			 

			//Recipients
			$mail->setFrom($from_mail);
			$mail->addAddress($to_mail);  
            if(strlen($to_mail2)>1)	{
				$mail->addAddress($to_mail2);  
			}		
			       

			// Attachments
			//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $title;
			$mail->Body    = $html_content;
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$mail->send();
			echo 'Message has been sent';
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
}

function extractDateFromMixedString($mixed){
    
    preg_match_all('/\d{2}\/\d{2}\/\d{4}/',$mixed,$matches);
    return @$matches[0][0];
}
function extractFirstNumberFromString($string){  
    $filteredNumbers = @array_filter(preg_split("/\D+/", @$string));
    $firstOccurence = @reset(@$filteredNumbers);
    return $firstOccurence; 
}

function echoArray( $OUTPUT_ARTICLES){

    foreach($OUTPUT_ARTICLES as $OUTPUT_ARTICLE){
        echo "<br>*** ECHO ARTICLE";
        foreach($OUTPUT_ARTICLE as $KEY=>$VAL){
            echo '<br>  article.'.@$KEY.':'.@$VAL ; 
        }
        echo "<br>";
    }

}

function saveGrabbedArticles($database,$OUTPUT_ARTICLES){

    $artind=0;
    foreach($OUTPUT_ARTICLES as $OUTPUT_ARTICLE){$artind++;

        $article_ind=$OUTPUT_ARTICLE["ag_id"];

        echo '<br>'.$article_ind.')       ********** SAVE ARTICLE N.'.$artind;
        foreach($OUTPUT_ARTICLE as $KEY=>$VAL){
            
    
             if($KEY=="ag_images"){
                //echo '<br>'.$article_ind.') IMAGES FOUND!'; var_dump($VAL);
                $img_index=1;
                foreach($VAL as $img_key=>$img_link){
                  echo '<br>'.$article_ind.')       article.image'.@$img_key.':'.@$img_link ;
                  
                  $article_meta=array();
                  $article_meta["ag_domain"] =$OUTPUT_ARTICLE["ag_domain"];
                  $article_meta["ag_id"]     =$article_ind;
                  $article_meta["a_key"]     ="image_".$img_index."";
                  $article_meta["a_value"]   = ($img_link);
				  
				  $article_meta["update_date"]   = date("Y-m-d H:i:s");
				  
				  //$img_local_path            = getLocalPathFromImageUrl($full_img_link,$OUTPUT_ARTICLE["ag_id"],$prefilename_add);
				  //$article_meta["a_value_2"] =$img_local_path;
                  
                  $save_art_res=saveUpdateRecord($database,"grabbed_articles_meta",$article_meta, "ag_id","a_key",null);
                  $img_index++;
                }
            }else  if(is_array($VAL)){

            }
            else  if((strpos($KEY, "ag_") === 0) && strlen(@$KEY)>0){
                echo '<br>'.$article_ind.')       article.'.@$KEY.':'.@$VAL ; 

                  $article_meta=array();
                  $article_meta["ag_domain"] =$OUTPUT_ARTICLE["ag_domain"];
                  $article_meta["ag_url"]    =$OUTPUT_ARTICLE["ag_url"];
                  
                  $article_meta["ag_id"]     =$article_ind;
                  $article_meta["a_key"]     =$KEY;
                  $article_meta["a_value"]   =@$VAL;
				  $article_meta["update_date"]   = date("Y-m-d H:i:s");
                  $save_art_res=saveUpdateRecord($database,"grabbed_articles_meta",$article_meta, "ag_id","a_key",null);
            }
            
        }
		
		 $OUTPUT_ARTICLE["ag_indirizzo"]=@trim(@ $OUTPUT_ARTICLE["ag_indirizzo"]);
		 $OUTPUT_ARTICLE["ag_indirizzo"]=@str_replace("  ","",@ $OUTPUT_ARTICLE["ag_indirizzo"]);
		$address = $OUTPUT_ARTICLE["ag_indirizzo"]; // Google HQ
		if(strlen(@$address )>2  ){
			
			if(true){
				 
				$url =  ("https://nominatim.openstreetmap.org/?format=json&format=json&limit=1&addressdetails=1&q={".rawurlencode($address)."%2CItaly}");
				 
				echo "<br>".$article_ind.")  OPENSTREETMAP GEOCODE:".$url;
				$geocode=getWebsiteContent($url );
				$output= @json_decode($geocode);
				//print_r($output);
				if(!is_numeric( @$OUTPUT_ARTICLE["geoc_latitude"])){
					$OUTPUT_ARTICLE["geoc_latitude"]  = @$output[0]->lat;
				}
				if(!is_numeric( @$OUTPUT_ARTICLE["geoc_longitude"])){
					$OUTPUT_ARTICLE["geoc_longitude"] = @$output[0]->lon; 
				}
				
				
				
				$OUTPUT_ARTICLE["dist_centro"]    =calcolaDistanzaDaBolzano($OUTPUT_ARTICLE);
				echo "<br>Post distanza calcolata:".$OUTPUT_ARTICLE["dist_centro"] ;
			}
			
			
			if(false){
					
				$prepAddr = str_replace(' ','+',$address);
				$googleurl='https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false';
				echo "<br>".$article_ind.")  GOOGLE GEOCODE:".$googleurl;
				$geocode=file_get_contents($googleurl );
				$output= @json_decode($geocode);
				var_dump($output);
				$OUTPUT_ARTICLE["geoc_latitude"]  = @$output->results[0]->geometry->location->lat;
				$OUTPUT_ARTICLE["geoc_longitude"] = @$output->results[0]->geometry->location->lng;
				$OUTPUT_ARTICLE["dist_centro"]    =calcolaDistanzaDaBolzano($OUTPUT_ARTICLE);
				
			}
			
			echo "<br>".$article_ind.")  GEOCODED LAT=". $OUTPUT_ARTICLE["geoc_latitude"]." LONG=".$OUTPUT_ARTICLE["geoc_longitude"]." DIST CENTRO=".$OUTPUT_ARTICLE["dist_centro"];
		}

		
		
        $ACCEPTED_KEYS=array( "ag_id","ag_description"=>'', "ag_cod"=>'',"ag_dimensioni"=>'',"ag_indirizzo"=>'', "ag_localita"=>'',
         "ag_piano"=>'', "ag_prezzo"=>'', "ag_provincia"=>'', "ag_domain"=>'', "ag_id"=>'', "ag_prezzo"=>''
        , "ag_tipoofferta"=>'', "ag_title"=>'', "ag_url"=>'', "listurl"=>'', "ag_dataannuncio"=>''
        , "ag_contratto"=>'', "ag_tipologia"=>'', "ag_locali"=>'', "listurl"=>'', "ag_tipoproprieta"=>''
        , "ag_infocatastali"=>'', "ag_annocostruzione"=>'', "ag_annocostruzione"=>'', "ag_statocasa"=>'', "ag_riscaldamento"=>''
        , "ag_climatizzazione"=>'', "ag_classe_energetica"=>''
		, "agenzia_nome"=>'' 
		, "geoc_latitude"=>'', "geoc_longitude"=>'', "dist_centro"=>'', "update_date"=>''
       );
	   
	   $OUTPUT_ARTICLE["update_date"]   = date("Y-m-d H:i:s");

        $save_art_res=saveUpdateRecord($database,"grabbed_articles",$OUTPUT_ARTICLE, "ag_url",null,$ACCEPTED_KEYS);

        echo "<br>Saving record result:";
		var_dump($save_art_res);
      
    }
    

}

function calcolaDistanzaDaBolzano($CASA){
	
		$lat1 =46.495210 ;
		$lon1 =11.345840;
		
		$lat2 =@floatval(@$CASA["geoc_latitude"]);
		$lon2 =@floatval(@$CASA["geoc_longitude"]);
		
		echo "<br>calcolaDistanzaDaBolzano: LAT1/LON1=".@$lat1."".$lon1."  LAT2/LON2=".@$lat2."".$lon2;
     
       if($lat1==$lat2 || $lat1==0 ||$lon1==0 || $lat2==0 ||$lon2==0 || $lat1==-2 || $lat2==-2){
         return 0;
       }
     
       $theta = $lon1 - $lon2;
       $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
       $dist = acos($dist);
       $dist = rad2deg($dist);
       $miles = $dist * 60 * 1.1515; 
        
       return @intval(@abs($miles * 1.609344 *1000)/1000);//Km
           
    }
	
 
function getRecordByTableAndField($database,$table_name,$id_field_value,$id_fieldname="id"  ){

   
 
    //echo "<br>Saving article:<br>";print_r($record);
    $tab_grabbed_articles = $database["hydrahon"]->table($table_name);

 
    $grabbed_article_first=$tab_grabbed_articles->select()->where($id_fieldname, $id_field_value);
 
    $grabbed_article_first=$grabbed_article_first->one(); 
	
    return $grabbed_article_first;	
		
}

function saveUpdateRecord($database,$table_name,$record,$id_fieldname="id",$id_fieldname_2=null,$ACCEPTED_KEYS=null){
   try { 
		  
			if($ACCEPTED_KEYS!=null){
				$record2 = array_intersect_key ($record, $ACCEPTED_KEYS);
				$record =$record2 ;
			}
		 
			//echo "<br>Saving article:<br>";print_r($record);
			$tab_grabbed_articles = $database["hydrahon"]->table($table_name);

			$res=false;

			if(strlen(@$record[$id_fieldname])<0){
				echo "<br>Erro, no insert id for record:<br>";print_r($record);
				return $res;
			}

			$grabbed_article_first=$tab_grabbed_articles->select()->where($id_fieldname, @$record[$id_fieldname]);
			if($id_fieldname_2!=null){
				//echo "<br>Second id filter on.   ".$id_fieldname_2.":".$record[$id_fieldname_2];
				$grabbed_article_first=$grabbed_article_first->where($id_fieldname_2, $record[$id_fieldname_2]);
			}
			$grabbed_article_first=$grabbed_article_first->one();
			//echo "<br>Found article:<br>";var_dump($grabbed_article_first);
			if(strlen($grabbed_article_first[$id_fieldname])>0){
			   // echo "<br>Start updating record..";
			   $rtab_grabbed_articleses=  $tab_grabbed_articles->update()->set($record)->where($id_fieldname, $record[$id_fieldname]);
			   if($id_fieldname_2!=null){ 
				$rtab_grabbed_articleses=$rtab_grabbed_articleses->where($id_fieldname_2, $record[$id_fieldname_2]); 
			   }
			   $rtab_grabbed_articleses->execute();
			}else{
			  // echo "<br>Start inserting record..";
			   $res=  $tab_grabbed_articles->insert(
					array( $record)
					)->execute();

			   
			} 
	
	  } catch (Exception $e) {
		   echo "<br><br><div style='color:red'>";
           echo('<b>Exception in DBConnector class</b><br>');
           var_dump($e);
		   echo "</div>";
      }

    return $res;

}


function  getLocalPathFromImageUrl($full_img_link,$article_id,$prefilename_add=""){

	$full_img_link_noquest = strtok($full_img_link, "?");
	$filename_arr = explode("\\", $full_img_link_noquest);
	$filename=$prefilename_add.@$filename_arr[@count(@$filename_arr )-1] ;
	 
    $host_filename        = getDomain($full_img_link);    
	$save_filename="./images/downloaded_articles/".$host_filename."/".$article_id."/".$filename;
	
	return $save_filename;
}
 
function downloadImage($article ,$full_img_link,$prefilename_add="") {

    if(strlen($full_img_link)<5){
        return false;
    } 
  
    $save_filename        = getLocalPathFromImageUrl($full_img_link,$article["ag_id"],$prefilename_add);
    $host_filename        = getDomain($full_img_link);  
   
    //echo "<br>Download image, save filename:".$save_filename  ; 
	if(false){
		if (file_exists($save_filename )) { 
			 echo "<br>Image already downloaded.";
			 return $save_filename; 
		}

		if (! file_exists("./images/downloaded_articles/".$host_filename)) {
			   @mkdir("./images/downloaded_articles/".$host_filename );
		}
	 
		if (! file_exists("./images/downloaded_articles/".$host_filename."/".$article["ag_id"] )) {
			   @mkdir("./images/downloaded_articles/".$host_filename."/".$article["ag_id"] );
		}

		
		if (! file_exists($save_filename)) {
			@file_put_contents($save_filename, file_get_contents($full_img_link));
		}
	}


    return $save_filename;
}

function getDomain($url){ 
	$explode = @explode("\\", $url); 
	$name = @$explode[2];
	if(strlen($name)<1){
		return "notfound.com";
	}
	return $name ;
}
 
 
function getWebsiteContentPost($sUrl,$forcereload=false,$params_arr=array()) {

    //echo '<br>getWebsiteContent URL='.$sUrl  ;
    //Cache?
    if(true){

         // our folder with cache files
        $sCacheFolder = 'cache/';
        // cache filename
        $urlhash=md5($sUrl);
        $sFilename = date('YmdH').$urlhash.'.html';
        
        if ($forcereload || ! file_exists($sCacheFolder.$sFilename)) {
            echo '<br>Start curling...';
            $ch = curl_init($sUrl);
            //$fp = fopen($sCacheFolder.$sFilename, 'w');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_FILE, $fp);
            //curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15'));
			curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,  $params_arr);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 600);
            $rethtml=curl_exec($ch);
            curl_close($ch);
           // fclose($fp);
		   return $rethtml;
        }
        return file_get_contents($sCacheFolder.$sFilename);

    }else{
        echo "<br>Url already downloaded. Using cache.";
        return  file_get_html($sUrl);

    }



}



function getWebsiteContent($sUrl,$forcereload=false) {

   // echo '<br>getWebsiteContent URL='.$sUrl  ;
	
	if(strlen($sUrl)<4){
		return "getWebsiteContent:url too short error:".@$sUrl;
	}
	
    //Cache?
    if(true){

         // our folder with cache files
        $sCacheFolder = 'cache/';
        // cache filename
        $urlhash=md5($sUrl);
        $sFilename = date('YmdH').$urlhash.'.html';
        
        if ($forcereload || ! file_exists($sCacheFolder.$sFilename)) {
           // echo '<br>Start curling...';
            $ch = curl_init($sUrl);
            //$fp = fopen($sCacheFolder.$sFilename, 'w');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           // curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15'));
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            //curl_setopt($ch, CURLOPT_TIMEOUT_MS, 900);	
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			 
            $returstr=curl_exec($ch); 
            curl_close($ch);  
           // fclose($fp); 
			return $returstr;
        }
        return file_get_contents($sCacheFolder.$sFilename);

    }else{
        echo "<br>Url already downloaded. Using cache.";
        return  file_get_html($sUrl);

    }



}


function util_string_contains($checkstr,$containsstr){
	
	if (strpos($checkstr, $containsstr) !== false) {
		return true;
	}
	return false;
}

/*
https://zenscrape.com/#pricingSection  1000 al mese
https://www.import.io/standard-plans/  1000 al mese 

import ioAPI Key

 
 */
 
 function home_getGrabbedDataWithApi($database,$url,$parser=null,$params=null){
 
         if($parser==null){
			  //$parser="zenscrape" ;
			 // $parser="importio" ;
			 $parser="local" ;
		 }
		// echo "<br>Parser:".$parser;
		 $returnHTML="";
		 
		 if($parser=="local" ){	
          		 
				$command='/usr/bin/python /var/www/papin/homegrabber/grabdata.py "'.$url.'" 2>&1'; 
				exec($command, $output);
				$output = implode("", $output);
				return $output ;
		 }
		 if($parser=="papin" ){	
          		// $url=urldecode($url);
				  // $url=urlencode($url);
				   
				   $url = base64_encode(trim($url));
	               $url = urlencode($url);
	
                   $sUrl		=$WEB_SERVER_BASE."/homegrabber/grabwrapper.php?u=". ($url)	; 
				  // echo '<br>Start curling... url='.$sUrl;
				   return getWebsiteContent($sUrl,true);
				   if(false){
					   $ctx = stream_context_create(array('http'=>
							array(
								'timeout' => 69,  //1200 Seconds is 20 Minutes
							)
						));

				     return file_get_contents( $sUrl, true, $ctx);
					 }  
				    
		 }
		 
		  if($parser=="importio" ){
			    echo "<br>Start importio grabber url:".$url;
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, false);

				$data = array (
				   "url" =>$url,
				   "location" => "eu", 
				   "render" => "true"
				   //,"keep_headers"=>"true"
				   
				);

				curl_setopt($ch, CURLOPT_URL, "https://app.zenscrape.com/api/v1/get?" . http_build_query($data));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				  "Content-Type: application/json",
				  "apikey: 8ce",  
				));

				$response = @curl_exec($ch);
				@curl_close($ch); 
				$json = @json_decode($response);
				
				
				echo "<br>Zenscrape.response:".@$response;
				echo "<br>Zenscrape.remainingrequests:".@$json["remaining_requests"];
				echo "<br>Zenscrape.remainingrequests:".@$json["errors"]["q"];

				var_dump($json);
				
				$returnHTML=$response;
			 
		 }
		 
		 if($parser=="zenscrape" ){
			    echo "<br>Start zenscape grabber url:".$url;
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, false);

				$data = array (
				   "url" =>$url,
				   "location" => "eu",
				   "render" => "false"
				   //,"keep_headers"=>"true"
				   
				);

				curl_setopt($ch, CURLOPT_URL, "https://app.zenscrape.com/api/v1/get?" . http_build_query($data));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				  "Content-Type: application/json",
				  "apikey: 8ce",  
				));

				$response = @curl_exec($ch);
				@curl_close($ch); 
				$json = @json_decode($response);
				
				
				echo "<br>Zenscrape.response:".@$response;
				echo "<br>Zenscrape.remainingrequests:".@$json["remaining_requests"];
				echo "<br>Zenscrape.remainingrequests:".@$json["errors"]["q"];

				var_dump($json);
				
				$returnHTML=$response;
			 
		 }
		 
		 if($parser=="importio"){
			 
			 
		 }

        return $response;
		 
 }
 
 
 
 
function CallAPI($method, $url, $data = false,$headers=null)
{
	
	echo "<br>CallAPI start<br>";
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
		    echo "<br>..POST<br>";
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default: 
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    if($headers==null){
   
   }else{
       curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
   }
   
    // Optional Authentication:
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
echo "<br>CallAPI stop<br>";
    curl_close($curl);

    return $result;
}


echo '</body></html>';
?>