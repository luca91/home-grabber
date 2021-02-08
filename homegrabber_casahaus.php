<?php
  
 
echo "<br>************* GRABBER CASA HAUS ************* START"; 


$form_query_domain      ="http://www.casahaus.it";
  
$full_articles_link_b   ="http://www.casahaus.it/elenco_immobili_f.asp?rel=nofollow&start=";

for($current_page=1;$current_page<20;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
	if($current_page>0){
		 $full_articles_link  = $full_articles_link_b .(($current_page*10)+1)."&idcat=R#elenco_imm";  
	}else{
		 $full_articles_link  = $full_articles_link_b ."1&idcat=R#elenco_imm";  
	 }
       
 
    echo "<br><br> full_articles_link:".$full_articles_link;
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
 
    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="about-prop"]');
    $article_ind=1; $ind=0;
    foreach($articles as $article_tr_out)
    {$ind++;
		echo "<br>Process article.. ".$ind;
		  
		$article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		 
		$full_article_link=@$form_query_domain."/".trim(@$article_tr->getAttribute('href'));
		 
		
		//$full_article_link="http://www.studiobolzano.com/web/immobile_dettaglio.asp?cod_annuncio=1167089&language=ita";
		echo "<br>Process article.. ".$full_article_link;
		 
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
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
		//Title
		$article["title"]         = @trim($article_xpath->query('//title')->item(0)->textContent); 
		 
		  
		//Agenzia  
		$article["agenzia"]= "CASA HAUS";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="detail-prop"]')->item(1)->textContent; 
		
        //Price
        $article["price"]              = @$article_xpath->query('//div[@class="detail-prop"]')->item(0)->textContent; 
		
		//via 
	    $article_i_split0 = @explode("rif", strtolower( $article["title"]));
        $variable        = @$article_i_split0[0];
		
		$article_i_split = @explode("endita a", @$variable);
        $article["via"]  = @$article_i_split[1];
		echo "<br>VIA:".$article["via"] ;
          
		
		//Various info
		$article_tables= $article_xpath->query('//ul[@class="detail-prop"]/li'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $tr)
		  {
			   //echo '<br>'.$article_ind.")KV) Table found"; 
			    $article_infos_split = @explode(":", @trim(@$tr->nodeValue) );  
		        $art_tab_key =@$article_infos_split[0];
                $art_tab_val =@$article_infos_split[1];					
 
			     //echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'==='.$art_tab_val;
				    
                
                //SAnitazion
				if(util_string_contains($art_tab_key,"ovin")){
					$art_tab_key="localita";
				}	 
                else if(util_string_contains($art_tab_key,"perficie")){
					$art_tab_key="superfice";
				}
                else if(util_string_contains($art_tab_key,"rezzo")){
					$art_tab_key="price";
				}
                else if(util_string_contains($art_tab_key,"ologia")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"came")){
					$art_tab_key="locali";
				}		
                else if(util_string_contains($art_tab_key,"codice")){
					$art_tab_key="codice";
				}	
                else if(util_string_contains($art_tab_key,"ale Pia")){
					$art_tab_key="piano";
				}	 
                else if(util_string_contains($art_tab_key,"comune")){
					//$art_tab_key="localita";
				}	 	
				
			   // echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  
		  
		} 
 

        $newarticle=array();
        $newarticle["ag_images"]=array();

         //Images 
        $article_table= $article_xpath->query('//a[@data-lightbox="roadtrip"]'); 
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
			
	    $newarticle["ag_localita"]=@$article["via"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]=  @$article["via"];
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




echo "<br>************* GRABBER casa haus ************* STOP"; 

?>