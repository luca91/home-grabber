<?php
 
/*

GRABBER LAMAISON
*/
 
echo "<br>************* GRABBER LAMAISON ************* START"; 


$form_query_domain    ="https://www.lamaison-immobilien.it";
 
$full_articles_link   ="https://www.lamaison-immobilien.it/listings/for-sale/page/1/?orderby=date&order=desc";
 
   
for($current_page=1;$current_page<44;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
     $full_articles_link .=$current_page;        
     
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="wpsight-listing-image"]');
    $article_ind=1;
    foreach($articles as $article_tr_out)
    {
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
		  
		 $full_article_link="https://www.lamaison-immobilien.it/listing/terrazza-vista-lago-mozzafiato-albisano-torri-del-benaco/";
		
		
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
			//continue;
		}
    

        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;

        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
        //Title
        $article["title"]        = @$article_xpath->query('//h1')->item(0)->textContent;  
		
		$titexp=explode("“",$article["title"] );
		print_r($titexp);
		
		
		
        $article["codice"]=trim($article_tr->textContent);
		
		//Agenzia 
		$agenzia_html = $article_dom->saveHTML(@$article_xpath->query('//div[@class="address"]')->item(0));
		$agenzia_html_split = @explode("<br>", $agenzia_html);
		$article["agenzia"]= @trim(@$agenzia_html_split[0]);
		  
		 
	    //Price
        $article["prezzo"]        = @$article_xpath->query('//span[@class="listing-price-value"]')->item(0)->textContent; 
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="inserat-detail-table right"]')->item(1)->textContent; 
        
     
		//Various info
		$article_tables= $article_xpath->query('//div[@class="wpsight-listing-details clearfix"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			$indexint=1;
			foreach($article_table->getElementsByTagName('span') as $tr)
			{
				$indexint++;
				
				$art_tab_key = @trim(@$tr->getElementsByTagName('span')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('span')->item(1)->nodeValue); 

                //SAnitazion
				if(util_string_contains($art_tab_key,"alit")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"Via,")){
					$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"Superficie m")){
					$art_tab_key="superficie";
				}
                else if(util_string_contains($art_tab_key,"acquisto")){
					$art_tab_key="prezzo";
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
 
				if(strlen($art_tab_key)>0 && strlen($art_tab_val)>0){
					echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
					$article[$art_tab_key]=$art_tab_val;
				}  
			}  
		  } 
		} 
 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images
        $article_table= $article_xpath->query('//div[@class="items"]'); 
        if(!is_null( $article_table )){ 
            foreach($article_table as $tr)
            {
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                
                    $full_img_link=$form_query_domain .$tr_img->getAttribute('src');
					
					$full_img_link=str_replace("_pre","_big",$full_img_link);
                    
                    echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
        }
        } 
    
        //Rename for output   
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Superficie commerciale"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["prezzo"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["Stanze"]; 
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

        $newarticle["ag_tipoofferta"]=@$article["tipologia"];
        $newarticle["ag_provincia"]=@$article["localita"];
		
 
        $newarticle["ag_piano"]=extractFirstNumberFromString(@$article["Piano"]);
		

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[""]);
		$newarticle["ag_tipologia"]= (@$article["tipologia"]);
		$newarticle["ag_locali"]= (@$article["Stanze"]);
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

         $article_ind=999999999;
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER GRABBER LAMAISON ************* START";  

?>