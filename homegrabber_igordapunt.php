<?php
 
  
 
echo "<br>************* GRABBER IGORDAPUNT  ************* START"; 

 
 
$form_query_domain="https://www.igordapunt.com"; 
$full_articles_link_n="https://www.igordapunt.com/r/bolzano/verkaufen-bolzano.html?Provincia=-111759&Comune=0&Motivazione%5B%5D=1&macro=1&Tipologia%5B%5D=0&Codice=&Prezzo_da=0&Prezzo_a=12.500.000&cf=yes&p=";
 
 

for($current_page=0;$current_page<8;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    $full_articles_link=$full_articles_link_n.$current_page;
	 echo "<br><br>++++Link:". $full_articles_link; 
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
    $sUrlHtml =CallAPI("GET", $full_articles_link, null,null);

    //echo $sUrlHtml; exit;
 
	$dom = new DOMDocument();   
	@$dom->loadHTML($sUrlHtml);
	$xpath = new DomXPath($dom); 
	$articles  = $xpath->query('//section'); 


    $article_ind=1;$in=0;
	
    foreach($articles as $article_tr_out)
    {$in++;
		
	  echo "<br>Process article.. ";
      $full_article_link="";
 
	    $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
	 
	 
        //$full_article_link="https://www.igordapunt.com/i/7001158--verkaufen-wohnung-bolzano---bozen.html";
		
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
		$article["title"]         = @trim(@$article_xpath->query('//h1[@class="titoloscheda"]')->item(0)->textContent  ); 
		  
		//Agenzia  
		$article["agenzia"]= "IGOR DAPUNT";
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@id="sezTesto"]')->item(0)->textContent; 
      
  	    //price
        $article["price"]        = @$article_xpath->query('//div[@class="prezzo"]')->item(0)->textContent; 
		
	    //superfice
        $article["superfice"]        = @$article_xpath->query('//div[@class="ico-36-mq"]')->item(0)->textContent; 
		          
		  
       //codice
        $article["codice"]        = @$article_xpath->query('//div[@class="codice"]')->item(0)->textContent; 
		
	    //localita
        $article["localita"]        = @$article_xpath->query('//div[@class="dove_schimmo"]')->item(0)->textContent; 
		
		
	    //Various info
		$article_tables= $article_xpath->query('//div[@id="sezInformazioni"]') ;  
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('div') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('span')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('span')->item(1)->nodeValue); 

                //SAnitazion
				if(util_string_contains($art_tab_key,"alit")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"ona")){
					$art_tab_key="via";
				} 
                else if(util_string_contains($art_tab_key,"rezzo")){
					$art_tab_key="price";
				}
                else if(util_string_contains($art_tab_key,"ockwerke ges")){
					$art_tab_key="piano";
				}			
				 else if(util_string_contains($art_tab_key,"imme")){
					$art_tab_key="camere";
				}		
                else if(util_string_contains($art_tab_key,"odex	")){
					$art_tab_key="codice";
				}						


			    // echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  
		  } 
		} 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images 
        $article_table= $article_xpath->query('//a[@class="swipebox"]'); 
 
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				//echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
               // echo "<br>Table XYZ";
                    $full_img_link= $tr_img->getAttribute('data-src');
					 
                    
                 //  echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
         }
        } 
		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superfice"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["price"]);   
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






 

 
echo "<br>************* GRABBER IGORDAPUNT ************* STOP"; 



?>

