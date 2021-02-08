<?php
  
 
echo "<br>************* GRABBER BZIMMOBILIARE ************* START"; 


$form_query_domain      ="http://www.bzimmobiliare.com";
  
$full_articles_link_b   ="http://www.bzimmobiliare.com/it/Vendite/?&p=";

for($current_page=1;$current_page<9;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
 
     $full_articles_link  = $full_articles_link_b .(($current_page)); 
  
    echo "<br><br> full_articles_link:".$full_articles_link;
    //$sUrlHtml = getWebsiteContent($full_articles_link,true);  
	$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);
    //echo "<br>";
      
	 //  echo $sUrlHtml;exit;
    $dom = new DOMDocument();
      
     
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query("//div[contains(@class, 'annuncio')]");
    $article_ind=1; $ind=0;
    foreach($articles as $article_tr_out)
    {$ind++;
		echo "<br>Process article.. ".$ind;
		   
		$article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=@$form_query_domain. trim(@$article_tr->getAttribute('href'));
		 
		
		//$full_article_link="http://www.studiobolzano.com/web/immobile_dettaglio.asp?cod_annuncio=1167089&language=ita";
		echo "<br>Process article.. ".$full_article_link;
	 
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			 echo "<br>Article already processe...... Skip to next<br><br>";
			  continue;
		}
    

         echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
         // continue;
        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 
		 //echo $article_html; exit;
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		   
		//Agenzia  
		$article["agenzia"]= "BZ IMMOBILIARE";
         
		//Title
		$article["title"]         = @trim($article_xpath->query('//title')->item(0)->textContent);
		
		//Description
		$article["description"] = @trim($article_xpath->query('//div[@class="desc"]')->item(0)->textContent);
	 
 
 		 
		 //Via
		$article["via"] = @trim($article_xpath->query('//div[@class="mappaIndirizzo"]')->item(0)->textContent);
		
		//Various info //div[contains(@class, 'description-container')]/*
		$article_tables= $article_xpath->query("//div[contains(@class, 'sCar')]");  
		$supaggiunta=false;
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
		 
				$art_tab_key = @trim(@$article_table->getElementsByTagName('div')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$article_table->getElementsByTagName('div')->item(1)->nodeValue); 

                //SAnitazion
				 if(util_string_contains($art_tab_key,"ocali")){
					$art_tab_key="locali";
				}	
                else if(util_string_contains($art_tab_key,"Mq") && $supaggiunta==false){
					$art_tab_key="superfice";
					$supaggiunta=true;
				}
                else if(util_string_contains($art_tab_key,"rezzo")){
					$art_tab_key="price";
				}
				else if(util_string_contains($art_tab_key,"Codice")){
					$art_tab_key="codice";
				}
                 				


			     // echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			   
		  } 
		} 



        $newarticle=array();
        $newarticle["ag_images"]=array(); 
  
         //Images 
        $article_table= $article_xpath->query('//div[@class="fotorama"]'); 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				 // echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                // echo "<br>Table XYZ";
                    $full_img_link= $form_query_domain . $tr_img->getAttribute('src');
					 
                    
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
		
        $newarticle["ag_indirizzo"]=  @$article["via"];
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
		 
		//$article_ind=9999999999; exit;
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		 
        $article_ind++; 
		
		  //exit;
  
		 if($article_ind>999){
		    echo "<br>Max ind raggiunto";
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER BZIMMOBILIARE************* STOP"; 

?>