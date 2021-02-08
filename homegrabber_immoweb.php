<?php
 
/*

IMMOBILIARE IMMOWEB
*/
 
echo "<br>************* GRABBER IMMOBILIARE IMMOWEB ************* START"; 


$form_query_domain    ="https://www.immoweb.it";
 
$full_articles_link   ="https://www.immoweb.it/immo/search?do=search&search_location=Bolzano%3BSalto-Sciliar%3BBurgraviato%3BOltradige-Bassa+Atesina&search_object=wohnung&search_type=&objektart=1&location%5B3%5D=3%3B16&location%5B6%5D=131%3B164&location%5B4%5D=17%3B90&location%5B5%5D=91%3B130&location_input=&mietekauf=1&zimmer_von=&zimmer_bis=&m2_von=&m2_bis=&preis_von=&preis_bis=&alter=&konventioniert=&user_type=&alloptions=0&page=";
   
for($current_page=0;$current_page<44;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
     $full_articles_link .=$current_page;        
     
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="inserat fshadow"]');
    $article_ind=1;
    foreach($articles as $article_tr_out)
    {
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$linkde=trim($article_tr->getAttribute('href'));
		
		$linkit="/it/".substr($linkde,4);
        $full_article_link=$form_query_domain .$linkit; 
		
		
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
			continue;
		}
    

        //echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;

        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
        //Title
        $article["title"]        = @$article_xpath->query('//h1')->item(0)->textContent;  
		
		
		
        $article["codice"]=trim($article_tr->textContent);
		
		//Agenzia 
		$agenzia_html = $article_dom->saveHTML(@$article_xpath->query('//div[@class="address"]')->item(0));
		$agenzia_html_split = @explode("<br>", $agenzia_html);
		$article["agenzia"]= @trim(@$agenzia_html_split[0]);
		 
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="inserat-detail-table right"]')->item(1)->textContent; 
        
     
		//Various info
		$article_tables= $article_xpath->query('//table[@class="detail-table"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('th')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(0)->nodeValue); 

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

        $newarticle["ag_cod"]=@$article["ID"];

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

 
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER IMMOWEB ************* START";  

?>