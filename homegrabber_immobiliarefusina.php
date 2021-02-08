<?php
 
 
/*

GRABBER FUSINA
*/
 
echo "<br>************* GRABBER IMMOBILIARE FUSINA  ************* START"; 


$data = array("UIc"=>"it-IT",
			  "Sede"=>"all",
			  "MacroGruppo"=>"99",
			  "GruppoInternet"=>0,
			  "IdTipoOfferta"=>0,
			  "CodiceTipoImmobile"=>"",
			  "Localita"=>"100023",
			  "Regione"=>0,
			  "Provincia"=>0,
			  "FreeText"=>"" ,
			  "OrderBy"=>0,
			  "CustomFilter"=>0,
			  "PageSize"=>500  
			  );
						  
 
$call_data = json_encode($data);
$call_data ="{}";
			
$call_headers = array(                                                                            
        'Content-Length: ' . strlen($call_data),
        'Host: residenziale.immobiliarefusina.it',
        'Connection: keep-alive', 
        'Accept: application/json, text/plain, */*',
        'Sec-Fetch-Dest: empty',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
        'DNT: 1',
        'Content-Type: application/json;charset=UTF-8',
        'Origin: https://residenziale.immobiliarefusina.it',
        'Sec-Fetch-Site: same-origin',
        'Sec-Fetch-Mode: cors',
        'Referer: https://residenziale.immobiliarefusina.it/it/cerca?mg=99&l=100023',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7'
         );
 

 
$form_query_domain="https://www.immobiliarefusina.it";
$full_articles_link   ="https://residenziale.immobiliarefusina.it/it/residenziale";
$full_articles_link_n="https://residenziale.immobiliarefusina.it/api/houseandoffice/search";
 
 

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
  
	 
    echo "<br><br> full_articles_link:".$full_articles_link;
    //$sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
    $sUrlHtml =CallAPI("POST", $full_articles_link_n, $call_data,$call_headers);

   

	 $isjson=true;
	 $articles=array(); 
	 if($isjson){
		$sUrlHtml2 =  gzdecode ($sUrlHtml); 
		 //echo $sUrlHtml; exit;
		$articles =  json_decode($sUrlHtml2, true );
		//var_dump($articles);
	 }else{
		 $dom = new DOMDocument();   
		@$dom->loadHTML($sUrlHtml);
		$xpath = new DomXPath($dom); 
		$articles  = $xpath->query('//div[@class="blog_btn"]'); 
	 }

    $article_ind=1;$in=0;
	
    foreach($articles as $article_tr_out)
    {$in++;
		
	  echo "<br>Process article.. ";
      $full_article_link="";
	  if($isjson){
	     //print_r($article_tr_out);
		 $full_article_link=$article_tr_out["SeoUrl"];
	 }else{
	    $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
		 
	 }
	 
 
		 $full_article_link=$form_query_domain  .$full_article_link;
		 
		// $full_article_link="https://residenziale.immobiliarefusina.it/it/residenziale/vendita/quadrilocale/bolzano/appartamento-in-vendita-bolzano-22-3761";
		
         echo '<br>  art '.$in.': ' . $full_article_link ;
		  
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
		 	 continue;
		}
    

         echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
		 
		   
        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 //$article_html = home_getGrabbedDataWithApi($database,$full_article_link,true,null,null); 
		//echo $article_html ;exit;
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = @trim(@$article_xpath->query('//div[@class="intro-body text-uppercase"]')->item(0)->textContent  ); 
		 
		
        //$article["codice"]=trim($article_tr->textContent);
		
		//Agenzia  
		$article["agenzia"]= "IMMOBILIARE FUSINA";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="row row-description"]')->item(0)->textContent; 
         
		          
		  
        //Codice
        $article["codice"]        = "123";
		
		
	    //Various info
		$article_tables= $article_xpath->query('//table[@class="scheda-immobile-table"]');  
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('td')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(1)->nodeValue); 

                //SAnitazion
				if(util_string_contains($art_tab_key,"alit")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"ona")){
					$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"Mq")){
					$art_tab_key="superfice";
				}
                else if(util_string_contains($art_tab_key,"rezzo")){
					$art_tab_key="price";
				}
                else if(util_string_contains($art_tab_key,"ologia")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"amer")){
					$art_tab_key="camere";
				}		
                else if(util_string_contains($art_tab_key,"odice")){
					$art_tab_key="codice";
				}						


			     //echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  
		  } 
		} 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images 
        $article_table= $article_xpath->query('//a[@class="swipebox immobili"]'); 
 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				//echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
               // echo "<br>Table XYZ";
                    $full_img_link= $tr_img->getAttribute('src');
					 
                    
                  //echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
         }
        } 
		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superfice"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["price"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["localita"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]=@$newarticle["ag_localita"].",".@$article["via"];
		//echo "<br>ag_indirizzo=".$newarticle["ag_indirizzo"];
		
		$newarticle["agenzia_nome"]= (@$article["agenzia"]); 
		
        $newarticle["listurl"]=$full_articles_link; 
        $newarticle["ag_domain"]=@$form_query_domain;
        $newarticle["ag_url"]=$full_article_link;    
        $newarticle["ag_id"]=$article_id;
    
        $newarticle["ag_title"]      =@$article["title"];
        $newarticle["ag_description"]=@$article["description"];

        $newarticle["ag_cod"]=@$article["codice"];

        $newarticle["ag_tipoofferta"]=@$article["ss"];
        $newarticle["ag_provincia"]=@$article["localita"];
		
 
        $newarticle["ag_piano"]=extractFirstNumberFromString(@$article["piano"]);
		

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[""]);
		$newarticle["ag_tipologia"]= (@$article["Tipologia"]);
		$newarticle["ag_locali"]= (@$article["locali"]);
		$newarticle["ag_tipoproprieta"]= (@$article[""]);
		$newarticle["ag_infocatastali"]= (@$article[""]);
		$newarticle["ag_annocostruzione"]= (@$article["anno"]);
		$newarticle["ag_statocasa"]= (@$article["Stato"]);
		$newarticle["ag_riscaldamento"]= (@$article["riscaldamento"]);
		$newarticle["ag_climatizzazione"]= (@$article[""]);
		$newarticle["ag_classe_energetica"]= (@$article["energetica"]);  

		 
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		 
        $article_ind++;  
		 
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}






 

 
echo "<br>************* GRABBER IMMOBILIARI FUSINA ************* STOP"; 



?>

