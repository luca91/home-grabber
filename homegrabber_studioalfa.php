<?php
 
/*

IMMOBILIARE STUDIOALFA
*/
 
echo "<br>************* GRABBER IMMOBILIARE STUDIOALFA ************* START"; 


$form_query_domain    ="http://www.studioalfa.info";
 
$full_articles_link   ="http://www.studioalfa.info/it/immobili/ricerca";
   
for($current_page=0;$current_page<1;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    // $full_articles_link .=$current_page;        
     
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
 
    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="col-md-4 col-sm-6 col-xs-12 property-details for-sale"]');
    $article_ind=1;
    foreach($articles as $article_tr_out)
    {
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
		 
		
		
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
		$article["agenzia"]= "STUDIO ALFA BOLZANO SRL";
        
        //Description
        $article["description"]        = @$article_xpath->query('//article[@class="article-property-details"]')->item(0)->textContent; 
        
		
		//Localita
		$via_infos_split = @explode("-", @$article["title"]);
		echo "Splitted title -:".count($via_infos_split);
		if($via_infos_split!=null && count($via_infos_split)==1){
			$article["localita"]=$via_infos_split[0];
		}
	    if($via_infos_split!=null && count($via_infos_split)==2){
			 $article["localita"]=$via_infos_split[0]; 
			 
		}
	    if($via_infos_split!=null && count($via_infos_split)>2){
			 $article["localita"]=@$via_infos_split[0];
			 $article["via"]     =@$via_infos_split[2];
		}
		
     
		//Various info
		$article_tables= $article_xpath->query('//table[@class="table table-striped table-property"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('td')->item(0)->getElementsByTagName('span')->item(0)->textContent);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(0)->getElementsByTagName('span')->item(1)->textContent);  
 					
				$art_tab_key=@str_replace(':',"",@$art_tab_key);
				$art_tab_val=@str_replace(':',"",@$art_tab_val);
 
			     echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  
		  } 
		} 
 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images
        $article_table= $article_xpath->query('//a[@class="property-thumbnail"]'); 
        if(!is_null( $article_table )){ 
            foreach($article_table as $tr)
            { 
                
                    $full_img_link=$tr->getAttribute('href');
					 
                    
                    echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
            }  
     
        } 
    

        //Rename for output   

		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Superficie commerciale"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["Prezzo"]);   
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

        $newarticle["ag_cod"]=@$article["ID"];

        $newarticle["ag_tipoofferta"]=@$article["ss"];
        $newarticle["ag_provincia"]=@$article["localita"];
		
 
        $newarticle["ag_piano"]=extractFirstNumberFromString(@$article["Piano"]);
		

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[""]);
		$newarticle["ag_tipologia"]= (@$article["Tipologia"]);
		$newarticle["ag_locali"]= (@$article["Camere"]);
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
		
		print_r($newarticle); 
		//$article_ind=9999999999; exit;
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
        //exit;
        $article_ind++; 

 
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER STUDIOALFA ************* START";  

?>