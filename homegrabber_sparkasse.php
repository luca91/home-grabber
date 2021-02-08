<?php
 
/*

SPARKASSE
*/
 
echo "<br>************* GRABBER SPARKASSE ************* START"; 


$form_query_domain    ="https://www.sparkassehaus.it";
 
$full_articles_link   ="https://www.sparkassehaus.it/cerca?flt-gmaps-autocomple=&flt-type=sell&jsonParams=%7B%7D&pageNumber=";
 
   
for($current_page=1;$current_page<5;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
   
    //$sUrlHtml = getWebsiteContent($full_articles_link,true);  
	$full_articles_link_n=$full_articles_link.$current_page;
	
    echo '<br><br>Main page: ' . $full_articles_link_n ;
	$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link_n,null,$parser=null);

    $dom = new DOMDocument();
    //echo $sUrlHtml."HTMLEND";exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="row location energy-8 item-class-C"]');
    $article_ind=1; 
	 
    foreach($articles as $article_tr_out)
    {
		 echo "<br>ARTICLE!!!";
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$link=trim($article_tr->getAttribute('href'));
		
		//$linkit="/it/".substr($linkde,4);
        $full_article_link=$form_query_domain .$link; 
		 
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
		 
		//echo $article_html;
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
        //Title  
        $article["title"]        = $article_xpath->query('//title')->item(0)->textContent;  
        echo '<br>'.$article_ind.')   title='.@$article["title"];
		
		//Agenzia  
		$article["agenzia"]= "Sparkasse Bolzano";
		 
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="stl-text"]')->item(0)->textContent; 
		
		
		//Prezzo
		$article["prezzo"]            = @$article_xpath->query('//span[@class="price"]')->item(0)->textContent; 
		
		//Superfice
		$article["superficie"]        = @$article_xpath->query('//span[@class="sqm"]')->item(0)->textContent; 
		
		 //Comune
		$article["localita"]        = @$article_xpath->query('//span[@class="comune"]')->item(0)->textContent; 
		
		//Via 		  
		if (strpos($article["title"], ' via ') !== false) {
			 $article["via"] = substr($article["title"], strpos($article["title"], ' via ') + 5);
		}
         
		//Various info
		$article_tables= $article_xpath->query('//ul[@class="info infoappartamento"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			 
			foreach($article_table->getElementsByTagName('li') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('span')->item(0)->textContent);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('span')->item(1)->textContent); 

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
                else if(util_string_contains($art_tab_key,"rife")){
					$art_tab_key="ag_cod";
				}			
				 else if(util_string_contains($art_tab_key,"amer")){
					$art_tab_key="camere";
				}		
                else if(util_string_contains($art_tab_key,"nergeti")){
					$art_tab_key="energetica";
				}						


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
        $article_table= $article_xpath->query('//div[@class="carousel-item"]'); 
        $image_list = $xpath->query("//img[@src]");
		$doubled=array();
         for($i=0;$i<$image_list->length; $i++){
                   // echo "<br>Images ul img";
					
					
                    $full_img_link=$image_list->item($i)->getAttribute("src");
					if (strlen($full_img_link)<4 || !(strpos($full_img_link, 'uploads') !== false) || isset($doubled[$full_img_link])) {
						continue;
				    }
                    $doubled[$full_img_link]=true;
                    echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
         
        }  

        //Rename for output   

		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superficie"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=@(extractFirstNumberFromString(@$article["prezzo"])/1000);
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["localita"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]=@$newarticle["ag_localita"].",".@$article["via"];
		//echo "<br>ag_indirizzo=".$newarticle["ag_indirizzo"];
		
		$newarticle["agenzia_nome"]= (@$article["agenzia"]); 
		
        $newarticle["listurl"]=$full_articles_link_n; 
        $newarticle["ag_domain"]=@$form_query_domain;
        $newarticle["ag_url"]=$full_article_link;    
        $newarticle["ag_id"]=$article_id;
    
        $newarticle["ag_title"]      =@$article["title"];
        $newarticle["ag_description"]=@$article["description"];

        $newarticle["ag_cod"]="123123";

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
		
		 print_r($OUTPUT_ARTICLES); 
		 
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
        //exit;
        $article_ind++; 

 
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER SPARKASSE ************* STOP";  

?>