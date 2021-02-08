<?php
 
/*

IMMOBILIARE Gutzmer
*/
 
echo "<br>************* GRABBER IMMOBILIARE Gutzmer ************* START"; 


$form_query_domain    ="https://www.gutzmerupartner.it/";
 
$full_articles_link   ="https://www.gutzmerupartner.it/wohnung-kaufen-suedtirol.htm";
   
for($current_page=0;$current_page<1;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page; 
     
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//a[@class="link"]');
    $article_ind=1;
    foreach($articles as $article_tr)
    { 
		$full_article_link= trim($article_tr->getAttribute('href'));
		  
		
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
			continue;
		}
    

		$full_article_link=substr($full_article_link, 0, -3);
        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;

        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		  
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
        //Article node 
		$article_xpath_node = $article_xpath->query('//article[@class="article"]')->item(0); 
	 
		  //Title
        $article["title"]  = @$article_xpath->query('//h1',$article_xpath_node)->item(0)->textContent;  
	 
		 
		
		//Agenzia  
		$article["agenzia"]= "GUTZMER U. PARTNER IMMOBILIEN IMMOBILIARE REAL ESTATE";
		 
        
        //Description
        $article["description"]  = @$article_xpath->query('//div[@class="long_description"]',$article_xpath_node)->item(0)->textContent; 
        
     
		//Various info
		$article_tables= $article_xpath->query('.//ul',$article_xpath_node); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			   $classlist=$article_table->getAttribute('class');
			echo '<br>'.$article_ind.")KV) List found:class:".$classlist;
			if($classlist=="main" || $classlist=="bottom"){
			foreach($article_table->getElementsByTagName('li') as $tr)
			{ 
				$art_tab_key = @trim(@$tr->getElementsByTagName('*')->item(0)->nodeValue); 
				$art_tab_val = @trim(@$tr->textContent);

                $art_tab_val=str_replace($art_tab_key,"",$art_tab_val);				

                //SAnitazion
				if(util_string_contains($art_tab_key,"Gemeinde")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"Localita")){
					$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"qm")){
					$art_tab_key="superficie";
				}
                else if(util_string_contains($art_tab_key,"ndpreis")){
					$art_tab_key="prezzo";
				}
                else if(util_string_contains($art_tab_key,"ategorie")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"immer")){
					$art_tab_key="camere";
				}	 
				else if(util_string_contains($art_tab_key,"nergeti")){
					$art_tab_key="energ";
				}
				else if(util_string_contains($art_tab_key,"ummer")){
					$art_tab_key="Codice";
				}
				else if(util_string_contains($art_tab_key,"ockwerke")){
					$art_tab_key="piano";
				}
				


			    echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  
		  } 
		  }
		} 
 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images
        $article_table= $article_xpath->query('//li[@class="scale toheight"]'); 
        if(!is_null( $article_table )){ 
		echo "<br>Images found:".count($article_table);
            foreach($article_table as $tr)
            {
				
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                
                    $full_img_link=$tr_img->getAttribute('src');
					 
                    if(strlen(@$full_img_link)>3){
						    echo '<br>'.$article_id.')   Image='.$full_img_link;
							array_push($newarticle["ag_images"],$full_img_link);
			
							@downloadImage($article,$full_img_link);
					}
                  
        
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
		
        $newarticle["ag_indirizzo"]=@$newarticle["ag_localita"].",".@$article["via"];
		//echo "<br>ag_indirizzo=".$newarticle["ag_indirizzo"];
		
		$newarticle["agenzia_nome"]= (@$article["agenzia"]); 
		
        $newarticle["listurl"]=$full_articles_link; 
        $newarticle["ag_domain"]=@$form_query_domain;
        $newarticle["ag_url"]=$full_article_link;    
        $newarticle["ag_id"]=$article_id;
    
        $newarticle["ag_title"]      =@$article["title"];
        $newarticle["ag_description"]=@$article["description"];

        $newarticle["ag_cod"]=@$article["Codice"];

        $newarticle["ag_tipoofferta"]=@$article["Tipo Offerta"];
        $newarticle["ag_provincia"]=@$article["localita"];
		
 
        $newarticle["ag_piano"]=(@$article["piano"]);
		

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[" "]);
		$newarticle["ag_tipologia"]= (@$article["tipologia"]);
		$newarticle["ag_locali"]= (@$article["camere"]);
		$newarticle["ag_tipoproprieta"]= (@$article["tipologia"]);
		$newarticle["ag_infocatastali"]= (@$article["Catasto"]);
		$newarticle["ag_annocostruzione"]= (@$article["Anno"]);
		$newarticle["ag_statocasa"]= (@$article["Stato"]);
		$newarticle["ag_riscaldamento"]= (@$article["Riscaldamento"]);
		$newarticle["ag_climatizzazione"]= (@$article[" "]);
		$newarticle["ag_classe_energetica"]= (@$article["energetica"]);  

		 
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		
		//print_r($article); 
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
        //exit;
        $article_ind++; 

 
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }
   
    }


}




echo "<br>************* GRABBER IMMOWEB ************* START";  

?>