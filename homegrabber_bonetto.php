<?php
 
/*

IMMOBILIARE BONETTO
*/
 
echo "<br>************* GRABBER IMMOBILIARE BONETTO ************* START"; 


$form_query_domain    ="https://www.immobiliarebonetto.it";
 
$full_articles_link   ="https://www.immobiliarebonetto.it/it/residenziale/vendita/tutte-le-tipologie/bolzano/lista?m=1&t=1&ti=&l=100023";
   
for($current_page=0;$current_page<1;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;       
     
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="datitop"]');
    $article_ind=1;
    foreach($articles as $article_tr_out)
    {
        
		
		//Link
		$article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$linkde=trim($article_tr->getAttribute('href'));
        $full_article_link=$form_query_domain .$linkde; 
 
		
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
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
		 
		
		//Agenzia  
		$article["agenzia"]= "IMMOBILIARE BONETTO";
		 
        
        //Description 
		$DESCBLOCK=@$article_xpath->query('//blockquote')->item(0);  
        $article["description"]        = @$DESCBLOCK->textContent;  
       // echo '<br>Description:'.$article["description"] ; 
		
        $article["title"]        = @substr( @$article["description"],0,60)."...";
		 
 
	     //echo '<br>Title:'.$article["title"] ; 
     
		//Various info
		$article_tables= $article_xpath->query('//table[@class="table-scheda"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('th')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(0)->nodeValue); 

                //SAnitazion
				if(util_string_contains($art_tab_key,"Provincia")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"Zona")){
					$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"Dimensioni")){
					$art_tab_key="superficie";
				}
                else if(util_string_contains($art_tab_key,"Prezzo")){
					$art_tab_key="prezzo";
				}
                else if(util_string_contains($art_tab_key,"Tipo Offerta")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"amer")){
					$art_tab_key="camere";
				}		
                else if(util_string_contains($art_tab_key,"nergeti")){
					$art_tab_key="energetica";
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
        $article_table= $article_xpath->query('//div[@class="row carousel-row imgList"]'); 
        if(!is_null( $article_table )){ 
            foreach($article_table as $tr)
            {
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                
                    $full_img_link=$tr_img->getAttribute('src');
					 
                    if(@strlen(@$full_img_link)>3){
						
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
		
		if(strlen($newarticle["ag_prezzo"])<2){
			$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["description"]);   
		}
			
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
		
		//print_r($article); 
		
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
        //exit;
        $article_ind++; 

 
		 if($article_ind>20){
		   $current_page=99999;
		   break;
		 }

    
     
    
    }


}




echo "<br>************* GRABBER IMMOWEB ************* START";  

?>