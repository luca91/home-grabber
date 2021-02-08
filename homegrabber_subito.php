<?php
 
/*

IMMOBILIARE SUBITO
*/
  
echo "<br>************* GRABBER IMMOBILIARE SUBITO ************* START"; 


$form_query_domain="https://www.subito.it";
$full_articles_link ="https://www.subito.it/annunci-trentino-alto-adige/vendita/appartamenti/bolzano/?order=priceasc&ps=100000&pe=350000"; 
$article_ind=1; 

 
$current_page=1;
for($current_page;$current_page<10;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    if($current_page>1){ 
         $full_articles_link ="https://www.subito.it/annunci-trentino-alto-adige/vendita/appartamenti/bolzano/?order=priceasc&o=".$current_page."&ps=100000&pe=350000";
    }
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
 
	$articles_big=explode(  "\"default\":\"",$sUrlHtml);
	 
	foreach($articles_big as $art_key=>$art_val)
	{    
	    //echo "<br>ARTVAL:".$art_val;
		
		if(substr_compare($art_val, "http", 0, 4)==0){
			//echo "<br>Starts with http!";
		}else{
			//echo "<br>N http!";
			continue;
		}
		$articles_link_arr=explode(  '.htm',$art_val); 
		
        $full_article_link=trim(@$articles_link_arr[0]).".htm"; 
    
        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
	    $ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			 echo "<br>Article already processe...... Skip to next<br><br>";
			 continue;
		}
     

		

        $article_id=md5($full_article_link);
        $article["ag_id"]=$article_id;

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link);
        @$article_dom->loadHTML($article_html);
        $article_xpath = new DomXPath($article_dom); 
    
	 
        //Title
        $article["title"]        = $article_xpath->query('//title')->item(0)->textContent;  
        echo '<br>'.$article_ind.')   title='.@$article["title"];


        //Description
        $article["description"]        = @$article_xpath->query("//div[contains(@class, 'description-container')]/*")->item(1)->textContent; 
        echo '<br>'.$article_ind.')   description='.@$article["description"];
		
	    //Prezzo
        $article["price"]        = @$article_xpath->query("//div[contains(@class, 'ad-info__price')]/*")->item(0)->textContent; 
        echo '<br>'.$article_ind.')   price='.@$article["price"];
		
		//Location
        $article["indirizzo"]        = @$article_xpath->query("//div[contains(@class, 'ad-info__location')]/*")->item(1)->textContent; 
        echo '<br>'.$article_ind.')   indirizzo='.@$article["indirizzo"];

		//Agenzia
        $article["agenzia_nome"]        = @$article_xpath->query("//p[contains(@class, 'user-name')]")->item(0)->textContent; 
        echo '<br>'.$article_ind.')   agenzia_nome='.@$article["agenzia_nome"];

         

        //Various info 
        $article_infos = $article_xpath->query("//li[contains(@class, 'feature')]/*");   
        if(!is_null( $article_infos )){  
            for($prop_ind=0; $prop_ind< ($article_infos->length);$prop_ind=$prop_ind+2)
            {
                $art_tab_key = @trim(@$article_infos->item((@$prop_ind))->textContent);   
                $art_tab_val = @trim(@$article_infos->item((@$prop_ind+1))->textContent); 

                echo '<br>'.$article_ind.')KV)   '. @$art_tab_key.'='.@$art_tab_val;

                if(strlen($art_tab_val)>0){
                    $article[$art_tab_key]=$art_tab_val;
                } 
            }  
        } 
        
        $newarticle=array();
        $newarticle["ag_images"]=array();
		
		//Images
	    $images_all=explode(  "secureuri\":\"",$article_html); 
		$imgindex=0;
		foreach($images_all as $img_key=>$img_val)
		{    
			//echo "<br>IMGAL:".$img_val;
			
			if(substr_compare($img_val, "https", 0, 5)==0){
				//echo "<br>Image Starts with http!";
				
				 $image_link_arr=explode(  '.jpg',$img_val);  
                 $full_img_link=trim(@$image_link_arr[0]).".jpg"; 
				 
				 if (strpos($full_img_link, 'thumbs') !== false) {
					continue;
				 }
				 
				 $imgindex++;
				
				echo '<br>'.$article_id.')   Image='.$full_img_link;
				array_push($newarticle["ag_images"],$full_img_link);

				$img_lfolder=@downloadImage($article,$full_img_link,"".$imgindex."_");
				
			}else{
				//echo "<br>Image NOT http!";
				continue;
			}
		}
  
    

        //Rename for output  
        $newarticle["listurl"]=$full_articles_link; 
        $newarticle["ag_domain"]=@$form_query_domain;
        $newarticle["ag_url"]=$full_article_link;    
        $newarticle["ag_id"]=$article_id;
    
        $newarticle["ag_title"]=@$article["title"];
        $newarticle["ag_description"]=@$article["description"];

        $newarticle["ag_cod"]= (@$article["Riferimento e Data annuncio"]);
        $newarticle["ag_tipoofferta"]=@$article["Tipologia"];
        $newarticle["ag_provincia"]=@$article["indirizzo"];
        $newarticle["ag_localita"]=@$article["indirizzo"];
        $newarticle["ag_indirizzo"]=@$article["indirizzo"];
        $newarticle["ag_piano"]=extractFirstNumberFromString(@$article["Piano"]); 
        $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Superficie"]);
        $newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["price"]);

        $newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article["Riferimento e Data annuncio"]);

        $newarticle["ag_contratto"]= (@$article["Contratto"]);
        $newarticle["ag_tipologia"]= (@$article["Tipologia"]);
        $newarticle["ag_locali"]= extractFirstNumberFromString(@$article["Locali"]);
        $newarticle["ag_tipoproprieta"]= (@$article["Tipo proprietà"]);
        $newarticle["ag_infocatastali"]= (@$article["Informazioni catastali"]);
        $newarticle["ag_annocostruzione"]= (@$article["Anno di costruzione"]);
        $newarticle["ag_statocasa"]= (@$article["Stato"]);
        $newarticle["ag_riscaldamento"]= (@$article["Riscaldamento"]);
        $newarticle["ag_climatizzazione"]= (@$article["Climatizzatore"]);
        $newarticle["ag_classe_energetica"]= (@$article["Classe energetica"]);  
		
		$newarticle["agenzia_nome"]= (@$article["agenzia_nome"]);  
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
    
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
 
        $article_ind++; 

		 if($article_ind>20){
		   $current_page=99999;
		   break;
		 }
		  
  
    }
 


}



echo "<br>************* GRABBER IMMOBILIARE SUBITO ************* STOP";  

?>