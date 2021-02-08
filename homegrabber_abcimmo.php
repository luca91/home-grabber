<?php
  
 
echo "<br>************* GRABBER ABC IMMOBILIARE ************* START"; 


$form_query_domain      ="http://www.immobiliareabc.it";
  
$full_articles_link_b   ="http://www.immobiliareabc.it/web/immobili.asp?cod_categoria=R&cod_provincia=15&cod_ordine=O02&num_page=";
 

for($current_page=1;$current_page<12;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
     $full_articles_link  = $full_articles_link_b .(($current_page)); 
 
 
    echo "<br><br> full_articles_link:".$full_articles_link;
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
 
    $dom = new DOMDocument();
     //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//strong[@class="color2"]');
    $article_ind=1; $ind=0;
    foreach($articles as $article_tr_out)
    {$ind++;
		echo "<br>Process article.. ".$ind;
		  
		$article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		 
		$full_article_link=@$form_query_domain."/".trim(@$article_tr->getAttribute('href'));
		 
		
		//$full_article_link="http://www.studiobolzano.com/web/immobile_dettaglio.asp?cod_annuncio=1167089&language=ita";
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
		
    
		//Title
		$article["title"]         = @trim($article_xpath->query('//title')->item(0)->textContent); 
		 
		  
		//Agenzia  
		$article["agenzia"]= "ABC IMMOBILIARE";
        
        //Description
        $article["description"]        = @$article_xpath->query('//meta[@property="og:description"]')->item(0)->getAttribute('content');
		
        //Codice
        $article["codice"]              = @$article_xpath->query('//div[@id="det_rif"]')->item(0)->getAttribute('data-valore');
 
        //Prezzo
        $article["price"]              = @$article_xpath->query('//div[@id="det_prezzo"]')->item(0)->getAttribute('data-valore');
		
	    //Superfice
        $article["superfice"]              = @$article_xpath->query('//div[@id="det_superficie"]')->item(0)->getAttribute('data-valore');
		
		//Locali
        $article["locali"]              = @$article_xpath->query('//div[@id="det_vani"]')->item(0)->getAttribute('data-valore');
          
	    //Provincia
        $article["localita"]              = @$article_xpath->query('//div[@id="det_comune"]')->item(0)->getAttribute('data-valore');
		 
        $newarticle=array();
        $newarticle["ag_images"]=array();

         //Images 
        $article_table= $article_xpath->query('//a[@class="slideshow"]'); 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            { 
                    //echo "<br>Table XYZ";
                    $full_img_link=  $tr->getAttribute('href');
					 
                    
                   //echo '<br>'.$article_id.')   Image='.$full_img_link;
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
		
        $newarticle["ag_indirizzo"]=  @$article["localita"].",".@$article["via"];
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
		
		 //exit;
  
		 if($article_ind>15){
			 echo "<br>Max ind raggiunto";
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER ABC ************* STOP"; 

?>