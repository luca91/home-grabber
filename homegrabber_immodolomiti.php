<?php
 
  
 
echo "<br>************* GRABBER IMMODOLOMITI ************* START";  

$data  =array("aData"=>array( "2", "-1", "-1", "2", "", "", "", "", "", "", "", "", "", "", "-1", 0));
 
//$call_data = json_encode($data);
 
   
$call_data="tx_ttnews%5Bsearch%5D%5Blistingtype%5D=&tx_ttnews%5Bsearch%5D%5Bcode%5D=&tx_ttnews%5Bsearch%5D%5BaddressLocality%5D=z23249&tx_ttnews%5Bsearch%5D%5Btype%5D=";
 
 //Bolzano:z23248   Dintorni:z23249
			
$call_headers = array(
        'Content-Type: application/x-www-form-urlencoded',                                      
        'Content-Length: ' . strlen($call_data)+1,
		"Host: www.dimensionecasabz.it",
		"X-AspNet-Version: 4.0.30319",
		"X-Powered-By: ASP.NET",
		"X-Powered-By-Plesk: PleskWin",  
         ); 
 

$form_query_domain    ="http://www.immobiliare-dolomiti.it";
  
 
$full_articles_link   ="http://www.immobiliare-dolomiti.it/vende/bolzano";
 

for($current_page=1;$current_page<3;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
  
  if($current_page==1){ //Bolzano:z23248   
			 
	$call_data="tx_ttnews%5Bsearch%5D%5Blistingtype%5D=&tx_ttnews%5Bsearch%5D%5Bcode%5D=&tx_ttnews%5Bsearch%5D%5BaddressLocality%5D=z23248&tx_ttnews%5Bsearch%5D%5Btype%5D=";
	
	$full_articles_link   ="http://www.immobiliare-dolomiti.it/vende/bolzano";
  
  }
    if($current_page==2){//Dintorni:z23249
			 
	$call_data="tx_ttnews%5Bsearch%5D%5Blistingtype%5D=&tx_ttnews%5Bsearch%5D%5Bcode%5D=&tx_ttnews%5Bsearch%5D%5BaddressLocality%5D=z23249&tx_ttnews%5Bsearch%5D%5Btype%5D=";
	
	$full_articles_link   ="http://www.immobiliare-dolomiti.it/vende/dintorni-di-bolzano";
 
 
  }
	 
    echo "<br><br> full_articles_link:".$full_articles_link;
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
    //$sUrlHtml =CallAPI("POST", $full_articles_link, $call_data,$call_headers);
 

	 $isjson=false;
	 $articles=array(); 
	 //echo $sUrlHtml; exit;
	 if($isjson){
		$sUrlHtml =  gzdecode ($sUrlHtml); 
		
		$articles =  json_decode($sUrlHtml, true );
		//var_dump($articles);
	 }else{
		 $dom = new DOMDocument();   
		@$dom->loadHTML($sUrlHtml);
		$xpath = new DomXPath($dom); 
		$articles  = $xpath->query('//div[@class="col-md-4"]'); 
	 }
     
	
	$article_ind=1;$in=0;
	 
    foreach($articles as $article_tr_out)	{	
		$in++;
		
	  echo "<br>Process article.. ";
      $full_article_link="";
	  if($isjson){
	      //print_r($article_tr_out);
		 $full_article_link="https://ehrenstein.it/detail_deu.aspx?id=".$article_tr_out["IMB_IMB_ID"];
	 }else{
	    $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
		$full_article_link=trim($article_tr->getAttribute('href'));
		 
	 }
		
		 echo "<br>Process article.. ";
		  
		 $full_article_link= $form_query_domain ."/".$full_article_link;
	 
		// $full_article_link="https://www.dimensionecasabz.it/1749547/ita/vendita-appartamento-nalles-bolzano-1749547.html";
		
         echo '<br>  art '.$in.': ' . $full_article_link ;
		  
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
		    continue;
			echo "<br>Article already processe...... Skip to next<br><br>";
		}
        

         echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
		 // continue;
		 
        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		 //$article_html = home_getGrabbedDataWithApi($database,$full_article_link,true,null,null); 
		 //echo $article_html ;exit;
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		 
	
		//Agenzia  
		$article["agenzia"]= "IMMOBILIARE DOLOMITI";
        
		
		//Title
        $article["title"]        = @$article_xpath->query('//div[@class="span8"]')->item(0)->textContent; 
		
		
        //Via
        $article["localita"]      = @$article_xpath->query('//h3[@class="no-btm"]')->item(0)->textContent;  
		
		//localita
        $article["viax"]        = @$article_xpath->query('//div[@class="box-desc"]/h1')->item(0)->textContent; 
          
	    //Description 
		 $article["description"]        = $article["viax"]." --- ".@$article_xpath->query('//div[@class="bg-gray hidden-sm hidden-xs"]')->item(0)->textContent; 
		echo "<br>description:".$article["description"];
		
        //price
        $article["price"]        = @$article_xpath->query('//span[@class="price colore1 right"]')->item(0)->textContent; 
		echo "<br>Price:".$article["price"];
		
	   
        //superfice
        $article["superfice"]        = @$article_xpath->query('//li[@id="li_superficie"]')->item(0)->textContent; 
		echo "<br>Mq:".$article["superfice"];
		   

        //Codice   
		$article["codice"]=    @$article_xpath->query('//div[@class="sfondo_colore3 colore1 right padder"]')->item(0)->textContent;
		
		//Via
		/*
		$startsAt = @strpos($out, 'var indirizzo = "') + strlen('var indirizzo = "');
		$endsAt   = @strpos($out, '"', $startsAt);
		$result   = @substr($out, $startsAt, $endsAt - $startsAt);
        $article["via"]= $result;
		*/

       //Various info 1
		$article_tables = $article_xpath->query('//div[@class="box-desc"]');  
	  
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
 
			foreach($article_table->getElementsByTagName('li') as $tr)
			{ 
			     //echo '<br>'.$article_ind.")KV) Table found"; 
			    $article_infos_split = @explode(":", @trim(@$tr->nodeValue) );  
		        $art_tab_key =@$article_infos_split[0];
                $art_tab_val =@$article_infos_split[1];			

                //SAnitazion
				  if(util_string_contains($art_tab_key,"ona")){
					$art_tab_key="localita";
				}	
                else if(util_string_contains($art_tab_key,"qm")){
					$art_tab_key="superficie";
				}
                else if(util_string_contains($art_tab_key,"ezzo")){
					$art_tab_key="prezzo";
				} 
				 
			   //echo '<br>'.$article_ind.')KV1)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
		 
		    } 
			
			break;
		  }
		} 
		
		
		//Various info 2
		$article_tables= $article_xpath->query('//div[@class="principal-desc row"]');  
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			//echo '<br>'.$article_ind.")KV) Table found2";
			
			foreach($article_table->getElementsByTagName('dl') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('dt')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('dd')->item(0)->nodeValue); 

                //SAnitazion
				 if(util_string_contains($art_tab_key,"commercia")){
					$art_tab_key="superfice";
				} 		
				 else if(util_string_contains($art_tab_key,"Vani")){
					$art_tab_key="locali";
				}	
                else if(util_string_contains($art_tab_key,"Piano")){
					$art_tab_key="piano";
				}  				


			     // echo '<br>'.$article_ind.')KV2)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  
		  } 
		} 
 
 
		
        $newarticle=array();
        $newarticle["ag_images"]=array();

 
		//Images
        $article_table= $article_xpath->query('//div[@class="gallery"]');  
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				//echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
               // echo "<br>Table XYZ";
                    $full_img_link= $tr_img->getAttribute('src');
					 
                    if(strlen($full_img_link)>4 && !isset($full_img_link[$full_img_link])){
				     echo '<br>'.$article_id.')   Image='.$full_img_link;
                     array_push($newarticle["ag_images"],$full_img_link);
    
                     @downloadImage($article,$full_img_link);
						
					}

        
                }  
         }
        }  
		
	    $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["superfice"]); 
		//echo "<br>ag_dimensioni=".$newarticle["ag_dimensioni"];
		
		$newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["prezzo"]);   
		//echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
			
	    $newarticle["ag_localita"]=@$article["localita"]; 
		//echo "<br>ag_localita=".$newarticle["ag_localita"];
		
        $newarticle["ag_indirizzo"]= @$article["localita"].",".@$article["via"];
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
		  
		 if($article_ind>15){
		   $current_page=99999;
		   break;
		 }

    
    
      }
     


}


 

echo "<br>************* GRABBER IMMODOLOMITI ************* STOP";  

?>