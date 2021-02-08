<?php
 
 
/*

GRABBER EHRENSTEIN
*/
 
echo "<br>************* GRABBER EHRENSTEIN ************* START";  
$data  =array("aData"=>array( "2", "-1", "-1", "2", "", "", "", "", "", "", "", "", "", "", "-1", 0));
 
$call_data = json_encode($data);
			
$call_headers = array(
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($call_data),
		"Server: Microsoft-IIS/8.5",
		"X-AspNet-Version: 4.0.30319",
		"X-Powered-By: ASP.NET",
		"X-Powered-By-Plesk: PleskWin",  
         );
 

$form_query_domain    ="https://ehrenstein.it/";
 
$full_articles_link_n   ="https://ehrenstein.it/search_deu.aspx?ext=1";
 
$full_articles_link_n   ="https://ehrenstein.it/data.asmx/getSearchHome";
 

for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
 
   $full_articles_link=$full_articles_link_n ;
	 
    echo "<br><br> full_articles_link:".$full_articles_link;
    //$sUrlHtml = getWebsiteContent($full_articles_link,true);  
	//$sUrlHtml = home_getGrabbedDataWithApi($database,$full_articles_link,true,null,null);  
    $sUrlHtml =CallAPI("POST", $full_articles_link, $call_data,$call_headers);


	 $isjson=true;
	 $articles=array(); 
	 if($isjson){
		//$sUrlHtml =  gzdecode ($sUrlHtml); 
		 //echo $sUrlHtml; exit;
		$articles =  json_decode($sUrlHtml, true );
		//var_dump($articles);
	 }else{
		 $dom = new DOMDocument();   
		@$dom->loadHTML($sUrlHtml);
		$xpath = new DomXPath($dom); 
		$articles  = $xpath->query('//div[@class="blog_btn"]'); 
	 }

    $article_ind=1;$in=0;
	
    foreach($articles  as $article_tr )
    {
      foreach($article_tr as $article_tr_out)	{	
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
		 
		 
		 
	 
		 $full_article_link="https://ehrenstein.it/detail_ita.aspx?id=38791";
		
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
		 
	
		//Agenzia  
		$article["agenzia"]= "EHRENSTEIN";
        
		
		//Codice
        $article["codice"]        = @$article_xpath->query('//span[@id="ContentPlaceHolder1_lblRifDet"]')->item(0)->textContent; 
		
		
        //Via
        $article["via"]        = @$article_xpath->query('//span[@id="ContentPlaceHolder1_lblViertel"]')->item(0)->textContent; 
		
		//localita
        $article["localita"]        = @$article_xpath->query('//span[@id="ContentPlaceHolder1_lblGemeinde"]')->item(0)->textContent; 
         
		         
	    //Title
		$article["title"]         = "Apartamento ". $article["codice"]."- ".$article["via"];
		
	    //Description
		$article["description"]    = "";
        //price
        $article["price"]        = @$article_xpath->query('//span[@id="ContentPlaceHolder1_lblPreisTitle"]')->item(0)->textContent; 
		echo "<br>Price:".$article["price"];
		
	   
        //superfice
        $article["superfice"]        = @$article_xpath->query('//span[@id="ContentPlaceHolder1_lblMq"]')->item(0)->textContent; 
		echo "<br>Mq:".$article["superfice"];
		   

       //Various info
		$article_tables= $article_xpath->query('//table[@class="table table-striped"]');  
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
					//$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"ona")){
					//$art_tab_key="via";
				}	
                else if(util_string_contains($art_tab_key,"Mq")){
					//$art_tab_key="superfice";
				}
                else if(util_string_contains($art_tab_key,"rezzo")){
					//$art_tab_key="price";
				}
                else if(util_string_contains($art_tab_key,"ologia")){
					$art_tab_key="tipologia";
				}			
				 else if(util_string_contains($art_tab_key,"amer")){
					$art_tab_key="camere";
				}		
                else if(util_string_contains($art_tab_key,"nerget")){
					$art_tab_key="energetica";
				}	
                else if(util_string_contains($art_tab_key,"iano")){
					$art_tab_key="piano";
				}						

			     //echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
					
					$article["description"] .=$art_tab_key.'='.$art_tab_val.";";
				} 
			}  
		  } 
		} 

		
        $newarticle=array();
        $newarticle["ag_images"]=array();

 
		//Images
        $article_table= $article_xpath->query('//figure');  
        if(!is_null( $article_table )){ 
		  //echo "<br>Table X";
            foreach($article_table as $tr)
            {
				//echo "<br>Table XY";
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
               // echo "<br>Table XYZ";
                    $full_img_link= $form_query_domain.$tr_img->getAttribute('src');
					 
                    
                  // echo '<br>'.$article_id.')   Image='.$full_img_link;
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


}


 

echo "<br>************* GRABBER EHRENSTEIN ************* STOP";  

?>