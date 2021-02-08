<?php
 
/*

IMMOBILIARE LIFANDI
*/
 
echo "<br>************* GRABBER IMMOBILIARE LIFANDI ************* START"; 


$form_query_domain    ="https://www.lifandi.it";
$full_articles_link   ="https://www.lifandi.it/it/immobilien-immobili?field_auswahl_andere_tid_i18n=7197&field_n_riferimento_value=&sort_by=created&sort_order=DESC";
   
for($current_page=0;$current_page<28;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    if($current_page>0){
        $full_articles_link ="https://www.lifandi.it/it/immobilien-immobili?field_auswahl_andere_tid_i18n=7197&field_n_riferimento_value=&sort_by=created&sort_order=DESC&page=".$current_page;        
    }
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//h3[@class="field-content"]/*[1]');
    $article_ind=1;
    foreach($articles as $article_tr)
    {
        
        $article=array();

        $full_article_link=$form_query_domain .trim($article_tr->getAttribute('href')); 
		
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
			continue;
		}
    
        $article["codice"]=trim($article_tr->textContent);

        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;

        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link);
		 
		
        @$article_dom->loadHTML($article_html);
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
		//Agenzia 
		$article["agenzia_nome"]         ="Lifandi Immobilien";
    
        //Title
        $article["title"]        = @$article_xpath->query('//title')->item(0)->textContent;  
    
        
        //Description
        $article["description"]        = @$article_xpath->query('//div[@class="immoset2 views-fieldset"]')->item(0)->textContent; 
        
    
        //Various info
        $article_infos = $article_xpath->query('//div[@class="immoset1 views-fieldset"]/*'); 
		$price="";
        if(!is_null( $article_infos )){ 
            foreach($article_infos as $tr)
            {
                $article_infos_divs_tr = $tr->textContent;  
                $article_infos_split = @explode(":", $article_infos_divs_tr);
                
                $art_tab_val= @trim(@$article_infos_split[@count(@$article_infos_split)-1]);
                $art_tab_key= @str_replace(@$art_tab_val,'',@$article_infos_divs_tr);
                $art_tab_key= @trim(@str_replace(":",'',@$art_tab_key));
    
                echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
				
				$price=$art_tab_val;//Price is last value....

                if(strlen($art_tab_val)>0){
                    $article[$art_tab_key]=$art_tab_val;
                } 
            }  
        } 

        $article_infos2 = $article_xpath->query('//div[@class="immoset4 views-fieldset"]/*'); 
        if(!is_null( $article_infos2 )){ 
            foreach($article_infos2 as $tr)
            {
                $art_tab_key = $tr->textContent;
                $art_tab_val = "si";  
                echo '<br>'.$article_ind.')KV2)   '. $art_tab_key.'='.$art_tab_val;

                if(strlen($art_tab_val)>0){
                    $article[$art_tab_key]=$art_tab_val;
                } 
            }  
        } 

        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images
        $article_table= $article_xpath->query('//div[@class="views-field views-field-field-bilder"]/*[1]'); 
        if(!is_null( $article_table )){ 
            foreach($article_table as $tr)
            {
                foreach($tr->getElementsByTagName('img') as $tr_img)
                {
                
                    $full_img_link=$tr_img->getAttribute('src');
                    
                    //echo '<br>'.$article_id.')   Image='.$full_img_link;
                    array_push($newarticle["ag_images"],$full_img_link);
    
                    @downloadImage($article,$full_img_link);
        
                }  
        }
        } 
    

        //Rename for output  
        $newarticle["listurl"]=$full_articles_link; 
        $newarticle["ag_domain"]=@$form_query_domain;
        $newarticle["ag_url"]=$full_article_link;    
        $newarticle["ag_id"]=$article_id;
    
        $newarticle["ag_title"]=@$article["title"];
        $newarticle["ag_description"]=@$article["description"];

        $newarticle["ag_cod"]=@$article["N. riferimento"];

        $newarticle["ag_tipoofferta"]=@$article["Tipologia"];
        $newarticle["ag_provincia"]=@$article["Zona"];
        $newarticle["ag_localita"]=@$article["Comune"];
        $newarticle["ag_indirizzo"]=@$article["Comune"].",".@$article["Zona"];
        $newarticle["ag_piano"]=@$article["Piano"];
        $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Sup. commerciale"]);
        $newarticle["ag_prezzo"]=extractFirstNumberFromString(@$price);
		
		echo "<br>ag_prezzo=".$newarticle["ag_prezzo"];
		
    $newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article[""]);

	$newarticle["ag_contratto"]= (@$article[""]);
	$newarticle["ag_tipologia"]= (@$article[""]);
	$newarticle["ag_locali"]= (@$article["Stanze"]);
	$newarticle["ag_tipoproprieta"]= (@$article[""]);
	$newarticle["ag_infocatastali"]= (@$article[""]);
	$newarticle["ag_annocostruzione"]= (@$article[""]);
	$newarticle["ag_statocasa"]= (@$article[""]);
	$newarticle["ag_riscaldamento"]= (@$article[""]);
	$newarticle["ag_climatizzazione"]= (@$article[""]);
	$newarticle["ag_classe_energetica"]= (@$article[""]);  

	$newarticle["agenzia_nome"]= (@$article["agenzia_nome"]);  
	$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
	$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
    
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,$_GET["limit"]); $OUTPUT_ARTICLES=array();
		
		print_r($newarticle);

        
        $article_ind++; 

		 if($article_ind>10){
		   $current_page=99999;
		   break;
		 }

    
    
    
    }


}




echo "<br>************* GRABBER LIFANDI ************* START";  

?>