<?php
 
/*

IMMOBILIARE IMMOBOZEN
*/
 
echo "<br>************* GRABBER IMMOBILIARE IMMOBOZEN ************* START"; 


$form_query_domain    ="https://www.immobil-bozen.com";
 
$full_articles_link   ="https://www.immobil-bozen.com/suchergebnis.xhtml?f[40501-1]=&f[40501-35]=kauf&f[40501-59]=wohnen&f[40501-9]=&f[40501-3]=&f[40501-11]=&f[40501-13]=&p[obj0]=2";
   
for($current_page=1;$current_page<8;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
     $full_articles_link .=$current_page;        
     
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//div[@class="image"]');
    $article_ind=1;
    foreach($articles as $article_tr_out)
    {
        $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$linkit=trim($article_tr->getAttribute('href'));
		 
        $full_article_link=$form_query_domain ."/".$linkit."&language=ITA"; 
		
		
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
		 
		
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
        //Title
        $article["title"]        = $article_xpath->query('//title')->item(0)->textContent; 
		
        $article["codice"]=trim($article_tr->textContent);
		
		//Agenzia 
		$article["agenzia"]          = "Immobil Bozen"; 
		$article["agenzia_alltext"]  = @$article_xpath->query('//div[@class="information"]')->item(1)->textContent; 
		 
        
        //Description
		$descriptionhtml=@$article_xpath->query('//div[@class="information"]')->item(0)->textContent;
		 
		$descriptionhtml_split = @explode("function initialize", $descriptionhtml);
        $article["description"]= @trim(@$descriptionhtml_split[0]); 
			 
		 if(false){
			 $descriptionhtml_split = @explode("new google.maps.LatLng(", $descriptionhtml);
			$descriptionhtml_split_lat = @explode(",",  @$descriptionhtml_split[1]);
			$article["geoc_latitude"]  =@$descriptionhtml_split_lat[0];
			$descriptionhtml_split_long = @explode("), disableDefaultUI", @$descriptionhtml_split_lat[1]);
			$article["geoc_longitude"]  =@trim(@$descriptionhtml_split_long[0]);
			$article["geoc_longitude"]  =str_replace(")","",@$article["geoc_longitude"]);
			
			//echo "<br>LAT=".$article["geoc_latitude"]."  LONG=".$article["geoc_longitude"];
		 }

     
		//Various info
		$article_tables= $article_xpath->query('//div[@class="details-desktop"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
			    
				$art_tab_key = @trim(@$tr->getElementsByTagName('td')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(1)->nodeValue); 
				
				$art_tab_key2 = @trim(@$tr->getElementsByTagName('td')->item(2)->nodeValue);  
				$art_tab_val2 = @trim(@$tr->getElementsByTagName('td')->item(3)->nodeValue); 
 

			    //echo '<br>'.$article_ind.')KV1)   '. $art_tab_key.'='.$art_tab_val;
				//echo '<br>'.$article_ind.')KV2)   '. $art_tab_key2.'='.$art_tab_val2;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
				if(strlen($art_tab_val2)>0){
					$article[$art_tab_key2]=$art_tab_val2;
				} 
			}  
		  } 
		} 
 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images
        $article_table= $article_xpath->query('//div[@class="fotorama"]'); 
        if(!is_null( $article_table )){ 
            foreach($article_table as $tr)
            {
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                
                    $full_img_link= $tr_img->getAttribute('src');
					
					//$full_img_link=str_replace("_pre","_big",$full_img_link);
                    
                   // echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
        }
        } 
    

        //Rename for output   
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Superficie commerciale"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["Prezzo d'acquisto"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["comune/luogo"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]=@$newarticle["ag_localita"].",".@$article["Via"]." ".@$article["Numero civico"];
		//echo "<br>ag_indirizzo=".$newarticle["ag_indirizzo"];
		
		$newarticle["agenzia_nome"]= (@$article["agenzia"]); 
		
        $newarticle["listurl"]=$full_articles_link; 
        $newarticle["ag_domain"]=@$form_query_domain;
        $newarticle["ag_url"]=$full_article_link;    
        $newarticle["ag_id"]=$article_id;
    
        $newarticle["ag_title"]      =@$article["title"];
        $newarticle["ag_description"]=@$article["description"];

        $newarticle["ag_cod"]=@$article[" N. immobilie"];

        $newarticle["ag_tipoofferta"]=@$article["Tipologia"];
        $newarticle["ag_provincia"]=@$article["localita"];
		
 
        $newarticle["ag_piano"]=extractFirstNumberFromString(@$article["Piano"]);
		$newarticle["ag_locali"]= (@$article["Camere/stanze"]);

		$newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

		$newarticle["ag_contratto"]= (@$article[""]);
		$newarticle["ag_tipologia"]= (@$article["Tipologia"]);
	 
		$newarticle["ag_tipoproprieta"]= (@$article[""]);
		$newarticle["ag_infocatastali"]= (@$article[""]);
		$newarticle["ag_annocostruzione"]= (@$article[""]);
		$newarticle["ag_statocasa"]= (@$article[""]);
		$newarticle["ag_riscaldamento"]= (@$article[""]);
		$newarticle["ag_climatizzazione"]= (@$article[""]);
		$newarticle["ag_classe_energetica"]= (@$article[""]);  

		 
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		
		//print_r($article); 
		//echo "<br><br>";
		//var_dump($newarticle); 
		
 	
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
		
        //exit;
        $article_ind++; 

 
		 if($article_ind>100){ 
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER IMMOBOZEN ************* START";  

?>