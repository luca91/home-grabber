<?php
 
/*

IMMOBILIARE REALPRO
*/
 
echo "<br>************* GRABBER IMMOBILIARE REALPRO ************* START"; 


$form_query_domain    ="http://www.realpro.it";
 
$full_articles_link   ="http://www.realpro.it/it/Immobili-Residenziali/?mq1=70&p=";
   

for($current_page=0;$current_page<18;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
     $full_articles_link_new =$full_articles_link.$current_page;  

    echo "<br><br>List link::".$full_articles_link_new;	 
     
    //$sUrlHtml = getWebsiteContent2($full_articles_link_new,true);  
	$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link_new,null,null);

    $dom = new DOMDocument();
    //echo "HTML=".$sUrlHtml; exit;
	 
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="detail-sec"]');
    $article_ind=1;
    echo "<br>Found links:".count($articles);
    foreach($articles as $article_tr_out)
    { 
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$linkit=trim($article_tr->getAttribute('href'));
		 
        $full_article_link=$form_query_domain ."".$linkit ; 
		
        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
	 
		
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
		   continue;
		}
    


        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 
		//echo $article_html; exit;
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = $article_xpath->query('//title')->item(0)->textContent; 
		
		 
		//Agenzia  
		$article["agenzia"]= "REAL PRO";
		 
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="desc"]')->item(0)->textContent; 
		
	    //Description
        $article["via"]        = @$article_xpath->query('//div[@class="indirizzo"]')->item(0)->textContent; 
         
		 if (($pos = strpos($article["via"], ",")) !== FALSE) { 
			$article["localita"] = substr($article["via"], $pos+1); 
		}

		//Various info
		$article_tables= $article_xpath->query('//ul[@class="row carat"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			//echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('li') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('div')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('div')->item(1)->nodeValue); 

                //SAnitazion
				if(util_string_contains($art_tab_key,"alit")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"Via,")){
					$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"tri qua")){
					$art_tab_key="superficie";
				}
                else if(util_string_contains($art_tab_key,"rezzo")){
					$art_tab_key="prezzo";
				}
                else if(util_string_contains($art_tab_key,"Tipo di immobile")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"Locali")){
					$art_tab_key="camere";
				}		
                else if(util_string_contains($art_tab_key,"nergeti")){
					$art_tab_key="energetica";
				}						


			    // echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  
		  } 
		} 
 

        $newarticle=array();
        $newarticle["ag_images"]=array();

         //Images 
        $article_table= $article_xpath->query('//div[@class="obre"]'); 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				//echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
               // echo "<br>Table XYZ";
                    $full_img_link=$form_query_domain .$tr_img->getAttribute('src');
					
					$full_img_link=str_replace("_pre","_big",$full_img_link);
                    
                  //echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
         }
        } 

        //Rename for output   

		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superficie"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["prezzo"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["localita"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]= @$article["via"];
		
		$newarticle["agenzia_nome"]= (@$article["agenzia"]); 
		
        $newarticle["listurl"]=$full_articles_link; 
        $newarticle["ag_domain"]=@$form_query_domain;
        $newarticle["ag_url"]=$full_article_link;    
        $newarticle["ag_id"]=$article_id;
    
        $newarticle["ag_title"]      =@$article["title"];
        $newarticle["ag_description"]=@$article["description"];

        $newarticle["ag_cod"]="123123";

        $newarticle["ag_tipoofferta"]=@$article["tipologia"];
        $newarticle["ag_provincia"]=@$article["localita"];
		
 
        $newarticle["ag_piano"]=extractFirstNumberFromString(@$article["Piano"]);
		

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[""]);
		$newarticle["ag_tipologia"]= (@$article["tipologia"]);
		$newarticle["ag_locali"]= (@$article["camere"]);
		$newarticle["ag_tipoproprieta"]= (@$article[""]);
		$newarticle["ag_infocatastali"]= (@$article[""]);
		$newarticle["ag_annocostruzione"]= (@$article[""]);
		$newarticle["ag_statocasa"]= (@$article["Stato"]);
		$newarticle["ag_riscaldamento"]= (@$article["Riscaldamento"]);
		$newarticle["ag_climatizzazione"]= (@$article[""]);
		$newarticle["ag_classe_energetica"]= (@$article["energetica"]);  

		  
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
        //exit;
        $article_ind++; 

 
		 if($article_ind>10){
		   $current_page=99999;
		   break;
		 }
 
		 
    }


}




echo "<br>************* GRABBER REALPRO ************* STOP";


 function getWebsiteContent3($sUrl,$forcereload=false) {
    
	
	$params="H_Url=http%3A%2F%2Fwww.realpro.it%2Fit%2FImmobili-Residenziali%2F%3F%26p%3D14&Src_Li_Tip=J&Src_Li_Cit=&Src_Li_Zon=&Src_Li_Cat=&Src_T_Cod=&Src_T_Mq1=&Src_T_Mq2=&Src_T_Pr1=&Src_T_Pr2=&Src_Li_Ord=&azi=Archivio&lin=it&n=1";
	
	$data_url = str_replace("amp;","",$params); //fix for &amp; to &
	 
 
$options = array(
    'http' => array(
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                    "Content-Length: ".strlen($data_url)."\r\n".
                    "User-Agent:MyAgent/1.0\r\n",
        'method'  => "POST",
        'content' => $data_url,
    ),
);
$context = stream_context_create($options);
$result = file_get_contents($sUrl, false, $context, -1, 40000);

		
		return $result;
}
   
function getWebsiteContent2($sUrl,$forcereload=false) {

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
			
			$params="H_Url=http%3A%2F%2Fwww.realpro.it%2Fit%2FImmobili-Residenziali%2F%3F%26p%3D14&Src_Li_Tip=J&Src_Li_Cit=&Src_Li_Zon=&Src_Li_Cat=&Src_T_Cod=&Src_T_Mq1=&Src_T_Mq2=&Src_T_Pr1=&Src_T_Pr2=&Src_Li_Ord=&azi=Archivio&lin=it&n=1";
			
            $ch = curl_init($sUrl);
            $fp = fopen($sCacheFolder.$sFilename, 'w');

			$params="H_Url=http%3A%2F%2Fwww.realpro.it%2Fit%2FImmobili-Residenziali%2F%3F%26p%3D14&Src_Li_Tip=J&Src_Li_Cit=&Src_Li_Zon=&Src_Li_Cat=&Src_T_Cod=&Src_T_Mq1=&Src_T_Mq2=&Src_T_Pr1=&Src_T_Pr2=&Src_Li_Ord=";
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FILE, $fp); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, true); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Host" => "www.realpro.it",
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
				"Accept" => "application/json, text/javascript, */*; q=0.01",
				"Accept-Language" => "en-us,en;q=0.5",
				"Accept-Encoding" => "gzip, deflate",
				"Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
				"Accept-Charset" => "application/json, text/javascript, */*; q=0.01",
				"Keep-Alive" => "115",
				"Cookie" =>'_ga=GA1.2.586906973.1566909794; okCookiesAccettazione=approved; ASP.NET_SessionId=1okndugcfmys12eub42vgkwk; _gid=GA1.2.2044314607.1569397312
',
				"Connection" => "keep-alive",
				"X-Requested-With" => "XMLHttpRequest",
				"Referer" => "http://www.realpro.it/it/Immobili-Residenziali/?&p=1"
			));
			curl_setopt($ch, CURLOPT_VERBOSE, true); 
			
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        return file_get_contents($sCacheFolder.$sFilename);

    }else{
        echo "<br>Url already downloaded. Using cache.";
        return  file_get_html($sUrl);

    }



}  

?>