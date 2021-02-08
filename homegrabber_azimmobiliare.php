<?php
  
 
echo "<br>************* GRABBER AZ IMMOBILIARE ************* START"; 


$form_query_domain      ="http://azimmobiliare.org";
  
$full_articles_link_b   ="http://azimmobiliare.org";

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    $full_articles_link  = $full_articles_link_b;// .$current_page;      
 
    echo "<br><br> full_articles_link:".$full_articles_link;
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
 
    $dom = new DOMDocument();
     //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//p[@align="justify"]');
    $article_ind=1; $ind=0;
    foreach($articles as $article_tr_out)
    {$ind++;
		echo "<br>Process article.. ".$ind;
		  
		 $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
		  
		
		//$full_article_link="http://www.studiobolzano.com/web/immobile_dettaglio.asp?cod_annuncio=1167089&language=ita";
		echo "<br>Process article.. ".$full_article_link;
		 
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			 echo "<br>Article already processe...... Skip to next<br><br>";
			 //continue;
		}
    

         echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
          continue;
		 
        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = @$article_xpath->query('//div[@class="short_description"]')->item(0)->textContent;  
		  
		//Agenzia  
		$article["agenzia"]= "IMMOBILIEN SUEDTIROL";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="long_description"]')->item(0)->textContent; 
	 
		
		//Various info
		$article_tables = $article_xpath->query('//ul[@class="main"]');  
	  
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
 
			foreach($article_table->getElementsByTagName('li') as $tr)
			{ 
				$art_tab_key = @trim(@$tr->getElementsByTagName('*')->item(0)->nodeValue); 
				$art_tab_val = @trim(@$tr->textContent);

                $art_tab_val=str_replace($art_tab_key,"",$art_tab_val);				

                //SAnitazion
				  if(util_string_contains($art_tab_key,"omun")){
					$art_tab_key="localita";
				}	
                else if(util_string_contains($art_tab_key,"qm")){
					$art_tab_key="superficie";
				}
                else if(util_string_contains($art_tab_key,"ezzo vend")){
					$art_tab_key="prezzo";
				}
                else if(util_string_contains($art_tab_key,"ategor")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"immer")){
					$art_tab_key="camere";
				}	 
				else if(util_string_contains($art_tab_key,"nergeti")){
					$art_tab_key="energ";
				}
				else if(util_string_contains($art_tab_key,"dice")){
					$art_tab_key="Codice";
				}
				else if(util_string_contains($art_tab_key,"ockwerke")){
					$art_tab_key="piano";
				}
				


			   // echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
		 
		    } 
		  }
		} 
 
            //Various info
		$article_tables = $article_xpath->query('//ul[@class="bottom"]');  
	  
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
 
			foreach($article_table->getElementsByTagName('li') as $tr)
			{ 
				$art_tab_key = @trim(@$tr->getElementsByTagName('*')->item(0)->nodeValue); 
				$art_tab_val = @trim(@$tr->textContent);

                $art_tab_val=str_replace($art_tab_key,"",$art_tab_val);				

                //SAnitazion
				if(util_string_contains($art_tab_key,"rea")){
					$art_tab_key="via";
				}	 	
                else if(util_string_contains($art_tab_key,"commerc")){
					$art_tab_key="superficie";
				} 		
				 else if(util_string_contains($art_tab_key,"vani")){
					$art_tab_key="camere";
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
        $article_table= $article_xpath->query('//a[@class="lightbox"]'); 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr_img)
            { 
                    //echo "<br>Table XYZ";
                    $full_img_link=  $tr_img->getAttribute('href');
					 
                    
                   // echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
          
            }
        } 
    

        //Rename for output   
		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superficie"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["prezzo"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["localita"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]=$newarticle["ag_localita"]." , ". @$article["via"];
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
		$newarticle["ag_tipologia"]= (@$article["ategor"]);
		$newarticle["ag_locali"]= (@$article["vani"]);
		$newarticle["ag_tipoproprieta"]= (@$article[""]);
		$newarticle["ag_infocatastali"]= (@$article[""]);
		$newarticle["ag_annocostruzione"]= (@$article[""]);
		$newarticle["ag_statocasa"]= (@$article["xx"]);
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




echo "<br>************* GRABBER AZ IMMOBILIARE ************* STOP"; 

?>