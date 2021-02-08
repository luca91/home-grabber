<?php
 
/*

IMMOBILIARE ABITAREHAUS
*/
 
echo "<br>************* GRABBER IMMOBILIARE ABITAREHAUS ************* START"; 


$form_query_domain="http://www.abitarehaus.it/";
$full_articles_link   ="http://www.abitarehaus.it/immobili/appartamenti/vivere";
   
 
$sUrlHtml = getWebsiteContent($full_articles_link,true);  
$dom = new DOMDocument();
@$dom->loadHTML($sUrlHtml);
$xpath = new DomXPath($dom); 
$articles  = $xpath->query('//*[@class="flat-title-price"]');
$article_ind=1;
foreach($articles as $article_tr_out)
{
    $article_tr=$article_tr_out->getElementsByTagName('a')->item(0); 
	
	//Prezzo in list...
	$article_prezzo  =$article_tr_out->getElementsByTagName('span')->item(0)->textContent; 
    $article_prezzo= @extractFirstNumberFromString( @preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $article_prezzo));
	if(!is_numeric($article_prezzo) || $article_prezzo<1){
		echo "<br>Article has no price...... Skip to next<br><br>";
		continue;
		
	}
 
    $article=array();

    $full_article_link=trim($article_tr->getAttribute('href')); 
 
    $article["codice"]=trim($article_tr->textContent);

    echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;
	
    $ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
	if(is_numeric(@$ARTICLE_DB["id"])){
		echo "<br>Article already processe...... Skip to next<br><br>";
		continue;
	}
		

	//Agenzia  
	$article["agenzia"]= "ABITAREHAUS";
	
    $article_id=md5($full_article_link);

    $article_dom = new DOMDocument();
    $article_html = getWebsiteContent($full_article_link);
    @$article_dom->loadHTML($article_html);
    $article_xpath = new DomXPath($article_dom); 
 
 
    //Title
    $article["title"]         = $article_xpath->query('//title')->item(0)->textContent;  
  
    //Description
    $article["description"]   = $article_xpath->query('//div[@class="pro-details-description mb-50"]')->item(0)->textContent; 
	
	
	//Prezzo
    //$article_prezzo= @$article_xpath->query('//div[@class="pro-details-description mb-50"]')->item(0)->getElementsByTagName('h4')->textContent; 
	echo "<br>Prezzo::".$article_prezzo; 

    
    //Various info
    $article_infos = $article_xpath->query('//ul[@class="condition-list"]/*');  
 
    if(!is_null( $article_infos )){ 
        foreach($article_infos as $tr)
        {
            $article_infos_divs_tr = $tr->textContent; 
              
            $article_infos_split = @explode(" ", $article_infos_divs_tr);
            
            $art_tab_val= @trim(@$article_infos_split[@count(@$article_infos_split)-1]);
            $art_tab_key= @str_replace(@$art_tab_val,'',@$article_infos_divs_tr);
			
 		    if($art_tab_val=="mq"){
				
				$art_tab_val=$art_tab_key;
				$art_tab_key="Metriq";
				
			}
            echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;

            if(strlen($art_tab_val)>1){
                $article[$art_tab_key]=$art_tab_val;
            } 
        }  
    } 

    $article_infos2 = $article_xpath->query('//ul[@class="amenities-list"]/*');  
 
    if(!is_null( $article_infos2 )){ 
        foreach($article_infos2 as $tr)
        {
             $article_infos_divs_tr = $tr->textContent; 
            echo '<br>'.$article_ind.')KV2)   '. $article_infos_divs_tr;
            if(strlen($art_tab_val)>1){
                $article[$article_infos_divs_tr]="YES";
            } 
        }
    }

    $newarticle=array();
    $newarticle["ag_images"]=array();

     //Images
    $article_table= $article_xpath->query('//div[@class="pro-details-big-image"]')->item(0); 
    if(!is_null( $article_table )){ 
        foreach($article_table->getElementsByTagName('img') as $tr)
        {
            $full_img_link=$tr->getAttribute('src');
             
            // echo '<br>'.$article_id.')   Image='.$full_img_link;
            array_push($newarticle["ag_images"],$full_img_link);

             @downloadImage($article,$full_img_link);
 
        }  
    } 
  

		
    //Rename for output  
    $newarticle["listurl"]=$full_articles_link; 
    $newarticle["ag_domain"]=@$form_query_domain;
    $newarticle["ag_url"]=$full_article_link;    
    $newarticle["ag_id"]=$article_id;
 
	$newarticle["agenzia_nome"]= (@$article["agenzia"]); 
    $newarticle["ag_title"]=@$article["title"];
    $newarticle["ag_description"]=@$article["description"];

    $newarticle["ag_cod"]=@$article["Codice"];
    $newarticle["ag_tipoofferta"]=@$article["Tipo trattativa"];
    $newarticle["ag_provincia"]=@$article["Zona"];
    $newarticle["ag_localita"]=@$article["Zona"];
    $newarticle["ag_indirizzo"]=@$article["Indirizzo"];
    $newarticle["ag_piano"]=@$article["Piano"];
    $newarticle["ag_dimensioni"]=@$article["Mq Netti"];
	if(!is_numeric($newarticle["ag_dimensioni"])){
		$newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Metriq"]);
	}
	$newarticle["ag_locali"]= extractFirstNumberFromString(@$article["Camere "]);
	
	
    $newarticle["ag_prezzo"]=@$article["Prezzo Immobile"];
	
	if(!is_numeric($newarticle["ag_prezzo"])){
		$newarticle["ag_prezzo"]=@$article_prezzo;
	}
	if(is_numeric($newarticle["ag_prezzo"])){
		$newarticle["ag_prezzo"]=@(@intval(@$newarticle["ag_prezzo"])/1000);;
	}
	
  
 //Output article
	$OUTPUT_ARTICLES[]=$newarticle;

	echo "<br><br><br>+++VARS";
	print_r($newarticle);
	
	saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
	 
	
	$article_ind++; 

	 if($article_ind>9999){
	   $current_page=99999;
	   break;
	 }
	  
 
  
}







echo "<br>************* GRABBER IMMOBILIARE ABITAREHAUS ************* START";  

?>