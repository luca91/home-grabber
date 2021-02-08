<?php
 
/*

IMMOBILIARE OBERRRAUCH
*/
 
echo "<br>************* GRABBER IMMOBILIARE OBERRRAUCH ************* START"; 


$form_query_domain    ="https://www.immobilienoberrauch.com";
 
$full_articles_link   ="https://www.immobilienoberrauch.com/suchergebnisse.xhtml?f[40685-3]=kauf&f[40685-5]=wohnung&f[40685-59]=&f[40685-13]=&f[40685-15]=&p[obj0]=";
   
for($current_page=1;$current_page<21;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
     $full_articles_link_new =$full_articles_link.$current_page;  

 echo "<br><br>List link::".$full_articles_link_new;	 
     
    $sUrlHtml = getWebsiteContent($full_articles_link_new,true);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="listobject grid-50 clearfix"]');
    $article_ind=1;
 
    foreach($articles as $article_tr_out)
    {
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$linkit=trim($article_tr->getAttribute('href'));
		 
        $full_article_link=$form_query_domain ."/".$linkit."&language=ITA"; 
		
        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
	 
		
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
			continue;
		}
    


        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = $article_xpath->query('//title')->item(0)->textContent; 
		
		 
		//Agenzia  
		$article["agenzia"]= "AGENZIA IMMOBILIARE OBERRAUCH SAS";
		 
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="objectdetails-freitexte"]')->item(1)->textContent; 
         
		//Various info
		$article_tables= $article_xpath->query('//div[@class="objectdetails objectdetails-desktop grid-100 clearfix width100"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('td')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(1)->nodeValue); 

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
                else if(util_string_contains($art_tab_key,"Prezzo")){
					$art_tab_key="prezzo";
				}
                else if(util_string_contains($art_tab_key,"Tipo di immobile")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"Camere")){
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
	    $images_all=explode(  "\"full\":\"",$article_html); 
		$imgindex=0;
		foreach($images_all as $img_key=>$img_val)
		{    
			//echo "<br>IMGAL:".$img_val;
			
			if(substr_compare($img_val, "https", 0, 5)==0){
				//echo "<br>Image Starts with http!";
				
				 $image_link_arr=explode(  '.jpg',$img_val);  
				 //echo "<br>IMG PRE:".@$image_link_arr[0];
				 $image_link=str_replace('\/',"/",@$image_link_arr[0]);
				 //echo "<br>IMG POST:".$image_link;
                 $full_img_link=trim(@$image_link).".jpg"; 
				  
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

		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Superficie commerciale"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["prezzo"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["comune/luogo"]; 
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
		
		//print_r($article); //$article_ind=20;
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
        //exit;
        $article_ind++; 

 
		 if($article_ind>10){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER OBERRRAUCH ************* START";  

?>