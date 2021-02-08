<?php
  
 
echo "<br>************* GRABBER ROSSI ************* START"; 


$form_query_domain      ="https://www.immobiliarerossi.bz";
  
$full_articles_link   ="https://www.immobiliarerossi.bz/it/residenziale/vendita/tutte-le-tipologie/tutte-le-localita/lista?m=1&t=1&ti=&l=";
 

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    // $full_articles_link  = $full_articles_link_b .(($current_page)); 
 
 
    echo "<br><br> full_articles_link:".$full_articles_link;
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
 
    $dom = new DOMDocument();
     // echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="datitop"]');
    $article_ind=1; $ind=0;
    foreach($articles as $article_tr_out)
    {$ind++;
		echo "<br>Process article.. ".$ind;
		  
		$article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		 
		 
		$full_article_link=@$form_query_domain."/".trim(@$article_tr->getAttribute('href'));
		 
		
		//$full_article_link="https://www.immobiliarerossi.bz/it/residenziale/vendita/bilocale/laives/appartamento-in-vendita-laives-184-2339";
		echo "<br>Process article.. ".$full_article_link;
		 //continue;
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
		 
		 //echo $article_html; exit;
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		 
		//Agenzia  
		$article["agenzia"]= "ROSSI";
          
		//Title
		$article["title"]         = @trim(@$article_xpath->query('//div[@class="title-scheda"]')->item(0)->textContent); 
		
	    //Description
		$article["description"]         = @trim(@$article_xpath->query('//div[@class="scheda-descrizione"]')->item(0)->textContent); 
		
	    $sup_txt = @explode("mq.", $article["description"]);
				 
		
		//Superfice
		$article["superfice"]= @$sup_txt[1] ;
		   
   
		//Localtion
		$article["via"]         = @trim(@$article_xpath->query('//div[@class="title-scheda"]/h2')->item(0)->textContent); 

         //Images 
        $article_table= $article_xpath->query('//div[@class="row carousel-row"]'); 
        $newarticle=array();
        $newarticle["ag_images"]=array();
 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				// echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                // echo "<br>Table XYZ";
                    $full_img_link=   $tr_img->getAttribute('src');
					 
				   if(util_string_contains($full_img_link,"mini")){
					continue;
				   }
                    
                   //echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
         }
        } 
    

        //Rename for output   
		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superfice"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["title"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["localita"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]=  @$article["localita"].",".@$article["via"].",".$article["provincia"];
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
        $newarticle["ag_provincia"]=@$article["ag_localita"];
		
 
        $newarticle["ag_piano"]= (@$article["piani"]);
		

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[""]);
		$newarticle["ag_tipologia"]= (@$article["des_tipologia"]);
		$newarticle["ag_locali"]= (@$article["locali"]);
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
		 
        $article_ind++; 
		 
		  
		 if($article_ind>8){
			 echo "<br>Max ind raggiunto";
		   $current_page=99999;// exit;
		   break;
		 }

    
    
    
    }


}




 
echo "<br>************* GRABBER ROSSI ************* STOP"; 

?>