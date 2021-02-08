<?php
 
/*

IMMOBILIARE BOLZANO PROGETTO
*/
 
echo "<br>************* GRABBER BOLZANO PROGETTO ************* START"; 


$form_query_domain    ="http://bolzanoprogetto.it";
  
$full_articles_link_b   ="http://bolzanoprogetto.it/it/vendite/?&p=";

for($current_page=1;$current_page<11;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    $full_articles_link  = $full_articles_link_b .$current_page;
	
	
         echo '<br>List:'.  $full_articles_link ;
 
	 $sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
 
    $dom = new DOMDocument();
    //echo $sUrlHtml;continue;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="annuncio vanaff"]');
    $article_ind=1;
    foreach($articles as $article_tr_out)
    {
		
		echo "<br>Process article.. ";
		
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=$form_query_domain.trim($article_tr->getAttribute('href'));
		 
		
		
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
		$article["agenzia"]= "BOLZANO PROGETTO";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="col-xs-12 description text-justify"]')->item(0)->textContent; 
        
		//Prezzo
		$princ_node=@$article_xpath->query('//div[@class="car-principali"]')->item(0);
		
        $article["price"]        = @$article_xpath->query('//div[@class="car no-border col-xs-3"]',$princ_node)->item(0)->textContent; 
        echo "<br>Pricee:". $article["price"] ;
	 
 
       //Metriq
	   $article["superfice"]        = @$article_xpath->query('//div[@class="car col-xs-3"]',$princ_node)->item(0)->textContent; 
        echo "<br>Superf:". $article["superfice"] ;
	 
		//Various info
		$article_tables= $article_xpath->query('//div[@class="col-sm-6 car"]');  
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {  
				$art_tab_key = @trim(@$article_table->getElementsByTagName('div')->item(0)->textContent);  
				$art_tab_val = @trim(@$article_table->getElementsByTagName('div')->item(1)->textContent); 		

                //SAnitazion
				if(util_string_contains($art_tab_key,"Citt")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"Localita")){
					$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"Locali")){
					$art_tab_key="locali";
				}
                else if(util_string_contains($art_tab_key,"Piano")){
					$art_tab_key="piano";
				}
                else if(util_string_contains($art_tab_key,"odice")){
					$art_tab_key="codice";
				}			
				 else if(util_string_contains($art_tab_key,"immer")){
					$art_tab_key="camere";
				}	 
				else if(util_string_contains($art_tab_key,"energ")){
					$art_tab_key="energ";
				}
				else if(util_string_contains($art_tab_key,"Riscaldamento")){
					$art_tab_key="riscaldamento";
				} 
				


			    echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				}   
		  }
		} 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images
        $article_table= $article_xpath->query('//img[@class="img-responsive"]'); 
        if(!is_null( $article_table )){ 
            foreach($article_table as $tr)
            { 
                
                    $full_img_link=$form_query_domain.$tr->getAttribute('src');
					 
                    
                    echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
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
		$newarticle["ag_classe_energetica"]= (@$article["energ"]);  

		 
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




echo "<br>************* GRABBER BOLZANO PROGETTO ************* STOP";  

?>