<?php
 
/*

GIGLIO
*/
 
echo "<br>************* GRABBER GIGLIO ************* START"; 


$form_query_domain    ="https://www.immobiliaregiglio.com";
  
$full_articles_link   ="https://www.immobiliaregiglio.com/it/residenziale";

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
 
	 
    echo "<br><br> full_articles_link:".$full_articles_link;
    //$sUrlHtml = getWebsiteContent($full_articles_link,true);  
	$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
  
 
    $dom = new DOMDocument();
    //echo $sUrlHtml; 
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="immobile"]');
    $article_ind=1;$in=0;
    foreach($articles as $article_tr_out)
    {$in++;
		
		echo "<br>Process article.. ";
		
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=$form_query_domain.trim($article_tr->getAttribute('href'));
		 
		
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
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = @trim($article_xpath->query('//title')->item(0)->textContent); 
		 
		
        //$article["codice"]=trim($article_tr->textContent);
		
		//Agenzia  
		$article["agenzia"]= "IMMOBILIARE GIGLIO";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="inner-immobile-txt"]')->item(0)->textContent; 
         
		 @$article["codice"]="123";
		
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
				if(util_string_contains($art_tab_key,"ocalit")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"Zona")){
					$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"Mq")){
					$art_tab_key="superfice";
				}
                else if(util_string_contains($art_tab_key,"rezzo")){
					$art_tab_key="price";
					//$art_tab_val=@($art_tab_val/1000);
				}
                else if(util_string_contains($art_tab_key,"Tipologia dell")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"amer")){
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
        $article_table= $article_xpath->query('//a[@class="swipebox thumb-immobili"]'); 
 
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
		$newarticle["ag_annocostruzione"]= (@$article[""]);
		$newarticle["ag_statocasa"]= (@$article["Stato"]);
		$newarticle["ag_riscaldamento"]= (@$article["riscaldamento"]);
		$newarticle["ag_climatizzazione"]= (@$article[""]);
		$newarticle["ag_classe_energetica"]= (@$article["energetica"]);  

		 
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
        //exit;
        $article_ind++; 
 
 
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER GIGLIO ************* STOP";  

?>