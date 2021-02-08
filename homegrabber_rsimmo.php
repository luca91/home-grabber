<?php
 
  
 
echo "<br>************* GRABBER RSIMMO ************* START";  

$data  =array("aData"=>array( "2", "-1", "-1", "2", "", "", "", "", "", "", "", "", "", "", "-1", 0));
 
//$call_data = json_encode($data);
 
   
$call_data="action=search&lang=it&s_bezirk=&s_typ=apartment&s_verkaufmiete=1&s_zimmer_a=&s_zimmer_b=&s_parkm=&s_aufzug=&s_konv=&s_preis_a=&s_preis_b=&s_flaeche_a=&s_flaeche_b=";
 
			
$call_headers = array(
        'content-type: application/x-www-form-urlencoded;',                                      
        'Content-Length: ' . strlen($call_data), 
		"X-AspNet-Version: 4.0.30319",
		"X-Powered-By: ASP.NET",
		"X-Powered-By-Plesk: PleskWin",  
         ); 
 

$form_query_domain    ="https://rsimmo.it";
  
 
$full_articles_link_n   ="https://rsimmo.it/.it/suche-ajax.php";
 

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
 
   $call_data_n=$call_data;//.$current_page ;
	 
    echo "<br><br> full_articles_link:".$full_articles_link_n;
    //$sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
    $sUrlHtml =CallAPI("POST", $full_articles_link_n, $call_data_n,$call_headers);
     //echo $sUrlHtml; exit;

	 $isjson=false;
	 $articles=array(); 
	 if($isjson){
		$sUrlHtml =  gzdecode ($sUrlHtml); 
		
		$articles =  json_decode($sUrlHtml, true );
		//var_dump($articles);
	 }else{
		 $dom = new DOMDocument();   
		@$dom->loadHTML($sUrlHtml);
		$xpath = new DomXPath($dom); 
		$articles  = $xpath->query('//div[@class="section group"]'); 
	 }
    
    
	
	$article_ind=1;$in=0;
	 $articles_saved=array();
    foreach($articles as $article_tr_out)	{	
		$in++;
		
	  echo "<br>Process article.. ";
      $full_article_link="";
	  if($isjson){
	      //print_r($article_tr_out);
		 $full_article_link="https://ehrenstein.it/detail_deu.aspx?id=".$article_tr_out["IMB_IMB_ID"];
	 }else{
	    $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
		 
	 }
		
		echo "<br>Process article.. ";
		 
		 
		 $full_article_link= $form_query_domain .$full_article_link;
	    
		// $full_article_link="https://www.dimensionecasabz.it/1749547/ita/vendita-appartamento-nalles-bolzano-1749547.html";
		
		if(isset($articles_saved[$full_article_link])){
			echo "<br>Article already processe in this cycle...... Skip to next<br><br>";
			continue;
		}
		$articles_saved[$full_article_link]=true;
		
         echo '<br>  art '.$in.': ' . $full_article_link ;
	 
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
		    continue;
		}
        
 
        $article_id=md5($full_article_link);

		echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
		  

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		// $article_html = home_getGrabbedDataWithApi($database,$full_article_link,true,null,null); 
		// echo $article_html ;exit;
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		 
	
		//Agenzia  
		$article["agenzia"]= "RS IMMO";
        
		
		//Title
        $article["title"]        = @$article_xpath->query('//div[@class="col span_8_of_8 immovable"]')->item(0)->textContent; 
		
		//Description 
		$article["description"]        = @$article_xpath->query('//div[@class="col span_8_of_8"]')->item(2)->textContent; 
		echo "<br>description:".$article["description"];
		
		
        //Codice   
		$article["codice"]=    @$article_xpath->query('//div[@class="sfondo_colore3 colore1 right padder"]')->item(0)->textContent;

		
        $newarticle=array();
        $newarticle["ag_images"]=array();

 
 
        //Various info
		$article_tables= $article_xpath->query('//div[@class="col span_3_of_8"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			  
			foreach($article_table->getElementsByTagName('p') as $tr)
			{
				
			   //echo '<br>'.$article_ind.")KV) Table found";
			  
				$art_tab_val = @trim(@$tr->nodeValue); 
				 
                $article_infos_split = @explode(":", $art_tab_val); 
				$art_tab_key= @trim(@$article_infos_split[0]);
                $art_tab_val= @trim(@$article_infos_split[@count(@$article_infos_split)-1]); 
     

               //SAnitazion
				if(util_string_contains($art_tab_key,"luogo")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"zona")){
					$art_tab_key="via";
				} 
                else if(util_string_contains($art_tab_key,"rezzo")){
					$art_tab_key="price";
				}
                else if(util_string_contains($art_tab_key,"commerciale")){
					$art_tab_key="superfice";
				}			
				 else if(util_string_contains($art_tab_key,"amer")){
					$art_tab_key="camere";
				}		
                else if(util_string_contains($art_tab_key,"piano")){
					$art_tab_key="piano";
				}				


			     //echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				}
	         }				
		    }
		  } 

		//Images
        $article_table= $article_xpath->query('//a[@class="immovable_pics"]');  
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            { 
               // echo "<br>Table XYZ";
                    $full_img_link= $form_query_domain .$tr->getAttribute('href');
					 
                    if(strlen($full_img_link)>4 && !isset($full_img_link[$full_img_link])){
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
		
        $newarticle["ag_indirizzo"]= @$article["localita"].",".@$article["via"];
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


 

echo "<br>************* GRABBER RSIMMO ************* STOP";  

?>