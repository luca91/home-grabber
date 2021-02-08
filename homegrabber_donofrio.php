<?php
 
/*

GRABBER DONOFRIO
*/
 
echo "<br>************* GRABBER DONOFRIO ************* START"; 


$form_query_domain    ="http://www.immobiliaredonofrio.it";
 
$full_articles_link_n   ="http://www.immobiliaredonofrio.it/immobili/bolzano/residenziale";
 
 
 

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
 
   $full_articles_link=$full_articles_link_n ;
	 
    echo "<br><br> full_articles_link:".$full_articles_link;
    //$sUrlHtml = getWebsiteContent($full_articles_link,true);  
	$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
  
  
    $dom = new DOMDocument();
    
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="homes-content"]');
    $article_ind=1;$in=0;
    foreach($articles as $article_tr_out)
    {$in++;
		
		echo "<br>Process article.. ";
		
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
		 
		
		if(  ! util_string_contains($full_article_link,"annuncio/bolzano")){
				 echo "stopped,  ";
				 continue;
					  
		 }	
		  
		 //$full_article_link=$form_query_domain  .$full_article_link;
		 
		// $full_article_link="http://www.immobiliaredonofrio.it/annuncio/bolzano/codice-3048";
		
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
		 //$article_html = home_getGrabbedDataWithApi($database,$full_article_link,true,null,null); 
		//echo $article_html ;exit;
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = @trim($article_xpath->query('//title')->item(0)->textContent); 
		 
		
        //$article["codice"]=trim($article_tr->textContent);
		
		//Agenzia  
		$article["agenzia"]= "IMMOBILIARE DONOFRIO";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="blog-info details"]')->item(0)->textContent; 
         
		         
        //Price
        $article["price"]        = @$article_xpath->query('//div[@class="col-lg-3 col-md-3 col-12 cod-pad"]')->item(0)->textContent; 
		echo "<br>Price".$article["price"];
		
	    //Adress
        $article["via"]          = @$article_xpath->query('//div[@class="col-lg-9 col-md-9 col-12"]')->item(0)->textContent; 
		
		$article_infos_split = @explode(",", $article["via"] ); 
	 
		@$article["localita"]=@$article_infos_split[0];	
		  
        //Codice
        $article["codice"]        = "123";
		
		
	    //Various info
		$article_tables= $article_xpath->query('//ul[@class="homes-list clearfix"]'); 
         if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('li') as $tr)
			{
				$art_tab_key = @trim(@$tr->textContent);  
                $art_tab_val = @trim(@$tr->textContent);  				

                //SAnitazion
			 
                if(util_string_contains($art_tab_key,"mq")){
					$art_tab_key="superfice";
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
 		if(true){
			
            $article_table= $article_xpath->query('//img[@class="img-fluid cover_img"]'); 
			if(!is_null( $article_table )){ 
				$imgindex=0;
				foreach($article_table as $tr)
				{
					  
						$full_img_link=$tr->getAttribute('src');
						
						echo '<br>'.$article_id.')   Image='.$full_img_link;
						array_push($newarticle["ag_images"],$full_img_link);

						$img_lfolder=@downloadImage($article,$full_img_link,"".$imgindex."_");
			 
				}
			} 
		}
		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superfice"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["price"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["localita"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]= @$article["via"];
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
		$newarticle["ag_annocostruzione"]= (@$article["anno"]);
		$newarticle["ag_statocasa"]= (@$article["Stato"]);
		$newarticle["ag_riscaldamento"]= (@$article["riscaldamento"]);
		$newarticle["ag_climatizzazione"]= (@$article[""]);
		$newarticle["ag_classe_energetica"]= (@$article["energetica"]);  

		 
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		 
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		 
        $article_ind++;  
		
		//exit;
 
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}


 

echo "<br>************* GRABBER DONOFRIO ************* STOP";  

?>