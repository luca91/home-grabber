<?php
 
/*

IMMOBILIARE IMMOS
*/
 
echo "<br>************* GRABBER IMMOS ************* START"; 


$form_query_domain    ="https://www.immos.it";
  
$full_articles_link_b   ="https://www.immos.it/index.php?id=101&L=0";

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    $full_articles_link  = $full_articles_link_b;// .$current_page;      
 
    echo "<br><br> full_articles_link:".$full_articles_link;
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
 
    $dom = new DOMDocument();
    //echo $sUrlHtml;continue;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//a[@class="listing-object-teaser"]');
    $article_ind=1; $ind=0;
    foreach($articles as $article_tr_out)
    {$ind++;
		echo "<br>Process article.. ".$ind;
		  
			 
		$full_article_link=@$form_query_domain."/".trim(@$article_tr_out->getAttribute('href'));
		 
		
		//$full_article_link="http://www.studiobolzano.com/web/immobile_dettaglio.asp?cod_annuncio=1167089&language=ita";
		echo "<br>Process article.. ".$full_article_link;
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			 echo "<br>Article already processe...... Skip to next<br><br>";
			 continue;
		}
    

         echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
         //continue;
		 
        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = @trim($article_xpath->query('//title')->item(0)->textContent); 
		 
		
        //$article["codice"]=trim($article_tr->textContent);
		
		//Agenzia  
		$article["agenzia"]= "IMMOS";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@id="object-detail-description"]')->item(0)->textContent; 
         
     
		//Various info
		$article_tables= $article_xpath->query('//div[@id="object-detail-infobox"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('h1') as $tr)
			{ 
                
			   //echo '<br>'.$article_ind.")KV) Table found";
			  
				$art_tab_val = @trim(@$tr->nodeValue); 
				 
                $article_infos_split = @explode(":", $art_tab_val); 
				$art_tab_key= @trim(@$article_infos_split[0]);
                $art_tab_val= @trim(@$article_infos_split[@count(@$article_infos_split)-1]); 
				  
               //SAnitazion
				if(util_string_contains($art_tab_key,"ovin")){
					$art_tab_key="localita";
				}	
				
                else if(util_string_contains($art_tab_key,"läch")){
					$art_tab_key="superfice";
				}
                else if(util_string_contains($art_tab_key,"reis")){
					$art_tab_key="price";
				}
                else if(util_string_contains($art_tab_key,"bjekta")){
					$art_tab_key="title";
				}			
				 else if(util_string_contains($art_tab_key,"immer")){
					$art_tab_key="locali";
				}		
                else if(util_string_contains($art_tab_key,"rif")){
					$art_tab_key="codice";
				}	
                else if(util_string_contains($art_tab_key,"iano")){
					$art_tab_key="piano";
				}	 
                else if(util_string_contains($art_tab_key,"ezir")){
					$art_tab_key="localita";
				}	
                else if(util_string_contains($art_tab_key,"adt")){
					$art_tab_key="via";
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
        $article_table= $article_xpath->query('//a[@class="jqfancybox"]'); 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				// echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                    //echo "<br>Table XYZ";
                    $full_img_link= $form_query_domain."/".$tr_img->getAttribute('src');
					 
                    
                   //echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
         }
        } 
    

        //Rename for output   
		
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

        $newarticle["ag_cod"]=@$article["riferimento_annuncio"];

        $newarticle["ag_tipoofferta"]=@$article["ss"];
        $newarticle["ag_provincia"]=@$article["ag_localita"];
		
 
        $newarticle["ag_piano"]= (@$article["piani"]);
		

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[""]);
		$newarticle["ag_tipologia"]= (@$article["des_tipologia"]);
		$newarticle["ag_locali"]= (@$article["vani"]);
		$newarticle["ag_tipoproprieta"]= (@$article[""]);
		$newarticle["ag_infocatastali"]= (@$article[""]);
		$newarticle["ag_annocostruzione"]= (@$article[""]);
		$newarticle["ag_statocasa"]= (@$article["Stato"]);
		$newarticle["ag_riscaldamento"]= (@$article["Riscaldamento"]);
		$newarticle["ag_climatizzazione"]= (@$article[""]);
		$newarticle["ag_classe_energetica"]= (@$article["Classe energetica"]);  

		 
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		 
		//$article_ind=9999999999; exit;
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
       //  exit;
        $article_ind++; 
  
		 if($article_ind>15){
			 echo "<br>Max ind raggiunto";
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER IMMOS ************* STOP"; 

?>