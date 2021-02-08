<?php
 
/*

IMMOBILIARE IMMOBILIARE.IT
*/
  
echo "<br>************* GRABBER IMMOBILIARE IMMOBILIARE.IT ************* START"; 
 

$form_query_domain="https://www.immobiliare.it";
$full_articles_link ="https://www.immobiliare.it/ricerca.php?idCategoria=1&idContratto=1&idTipologia=4&idNazione=IT&criterio=dataModifica&ordine=desc&vrt=46.23495279600417%2C11.18682861328125%3B46.24824991289168%2C11.369476318359375%3B46.26534147068606%2C11.476593017578127%3B46.50595444552051%2C11.542510986328127%3B46.65697731621612%2C11.410675048828127%3B46.74738913515841%2C11.152496337890627%3B46.66640227857275%2C10.998687744140627%3B46.48515590043433%2C11.050872802734375%3B46.32796494040748%2C10.902557373046875%3B46.22355270220991%2C11.042633056640627"; 


       //Aste
       if(isset($immobiliare_tipograb) && $immobiliare_tipograb==1){
		   echo "<br>Cerco ASTE!!!!!!";
		   $full_articles_link="https://www.immobiliare.it/ricerca.php?idCategoria=1&idContratto=1&idNazione=IT&criterio=dataModifica&ordine=desc&inAsta%5B0%5D=1&inAsta%5B1%5D=10&inAsta%5B2%5D=100&verticaleAste=1&vrt=46.591114%2C11.183395%3B46.583564%2C11.25618%3B46.547685%2C11.317978%3B46.577428%2C11.46286%3B46.459777%2C11.477966%3B46.379307%2C11.335831%3B46.366515%2C11.275406%3B46.371253%2C11.225967%3B46.487677%2C11.212921%3B46.591114%2C11.183395";
	   }	

$article_ind=1; 


if((rand(10, 20)%2)==0){
   // $current_page=35;
    //echo '<br>E uscito pari!';
}  
$current_page=0;
for($current_page;$current_page<70;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page;
    if($current_page>0){
 
		$full_articles_link ="https://www.immobiliare.it/ricerca.php?idCategoria=1&idContratto=1&idTipologia=4&idNazione=IT&criterio=dataModifica&ordine=desc&vrt=46.23495279600417%2C11.18682861328125%3B46.24824991289168%2C11.369476318359375%3B46.26534147068606%2C11.476593017578127%3B46.50595444552051%2C11.542510986328127%3B46.65697731621612%2C11.410675048828127%3B46.74738913515841%2C11.152496337890627%3B46.66640227857275%2C10.998687744140627%3B46.48515590043433%2C11.050872802734375%3B46.32796494040748%2C10.902557373046875%3B46.22355270220991%2C11.042633056640627&pag=".$current_page; 

       //Aste
       if(isset($immobiliare_tipograb) && $immobiliare_tipograb==1){
		   $full_articles_link ="https://www.immobiliare.it/ricerca.php?idCategoria=1&idContratto=1&idNazione=IT&criterio=dataModifica&ordine=desc&inAsta%5B0%5D=1&inAsta%5B1%5D=10&inAsta%5B2%5D=100&verticaleAste=1&vrt=46.591114%2C11.183395%3B46.583564%2C11.25618%3B46.547685%2C11.317978%3B46.577428%2C11.46286%3B46.459777%2C11.477966%3B46.379307%2C11.335831%3B46.366515%2C11.275406%3B46.371253%2C11.225967%3B46.487677%2C11.212921%3B46.591114%2C11.183395&pag=".$current_page; 
	   }				
    }
	
	echo "<br>Open url:".$full_articles_link;
    $sUrlHtml = getWebsiteContent($full_articles_link,true);  
    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
    $articles  = $xpath->query('//p[@class="titolo text-primary"]/*[1]');
    
    foreach($articles as $article_tr)
    {
        
        $article=array();

        $full_article_link=trim($article_tr->getAttribute('href')); 
    
	    $ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
			continue;
		}
    
        $article["codice"]=trim($article_tr->textContent);

        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;

        $article_id=md5($full_article_link);
        $article["ag_id"]=$article_id;

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link);
        @$article_dom->loadHTML($article_html);
        $article_xpath = new DomXPath($article_dom); 
    
    
        //Title
        $article["title"]        = $article_xpath->query('//title')->item(0)->textContent;  
        echo '<br>'.$article_ind.')   title='.@$article["title"];

        //Description
        $article["description"]        = $article_xpath->query('//div[@class="col-xs-12 description-text text-compressed"]')->item(0)->textContent; 
        echo '<br>'.$article_ind.')   description='.@$article["description"];

        //Indirizzo
        $article["indirizzo"]        = @$article_xpath->query('//span [@class="im-address__content js-map-address"]')->item(0)->textContent; 
        echo '<br>'.$article_ind.')  indirizzo='.@$article["indirizzo"];
		
		//Agenzia 
		$article["agenzia_nome"]         = @trim(@$article_xpath->query('//p[@class="contact-data__name"]')->item(0)->textContent);  
		 echo '<br>'.$article_ind.')   agenzia_nome='.@$article["agenzia_nome"];
        

        //Various info 
        $article_infos = $article_xpath->query('//dl[@class="col-xs-12"]/*');   
        if(!is_null( $article_infos )){  
            for($prop_ind=0; $prop_ind< ($article_infos->length);$prop_ind=$prop_ind+2)
            {
                $art_tab_key = @trim(@$article_infos->item((@$prop_ind))->textContent);   
                $art_tab_val = @trim(@$article_infos->item((@$prop_ind+1))->textContent); 

                echo '<br>'.$article_ind.')KV)   '. @$art_tab_key.'='.@$art_tab_val;

                if(strlen($art_tab_val)>0){
                    $article[$art_tab_key]=$art_tab_val;
                } 
            }  
        } 
        
        $newarticle=array();
        $newarticle["ag_images"]=array();

        //Images   
		$imgindex=0;
		$article_table = explode('"medium":"', $article_html);
		foreach($article_table as $tr)
		{
			if((@substr( @$tr, 0, 5 ) === "https")){
				$imgindex++;
				$article_table_split = explode('","large":"', $tr); 
				$full_img_link=@str_replace("\/","\\",@$article_table_split[0]); 
				$full_img_link = preg_replace('/(\/+)/','/',$full_img_link);
				
				echo '<br>'.$article_id.')   Image='.$full_img_link;
				array_push($newarticle["ag_images"],$full_img_link);

				$img_lfolder=@downloadImage($article,$full_img_link,"".$imgindex."_");
  
			}
		} 
		
		if(false){
			$article_table= $article_xpath->query('//div[@class="image-cutter"]/*'); 
			if(!is_null( $article_table )){ 
				$imgindex=0;
				foreach($article_table as $tr)
				{
					$tr_text=trim($tr->textContent);
					echo '<br>'.$article_ind.')TRTEXTIMG)   '. @$tr_text;
					$imgindex++;

					foreach($tr->getElementsByTagName('img') as $tr_img)
					{
					
						$full_img_link=$tr_img->getAttribute('data-src');
						
						echo '<br>'.$article_id.')   Image='.$full_img_link;
						array_push($newarticle["ag_images"],$full_img_link);

						$img_lfolder=@downloadImage($article,$full_img_link,"".$imgindex."_");
			
					}  
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

        $newarticle["ag_cod"]= (@$article["Riferimento e Data annuncio"]);
        $newarticle["ag_tipoofferta"]=@$article["Tipologia"];
        $newarticle["ag_provincia"]=@$article["indirizzo"];
        $newarticle["ag_localita"]=@$article["indirizzo"];
        $newarticle["ag_indirizzo"]=@$article["indirizzo"];
        $newarticle["ag_piano"]=extractFirstNumberFromString(@$article["Piano"]); 
        $newarticle["ag_dimensioni"]=extractFirstNumberFromString(@$article["Superficie"]);
        $newarticle["ag_prezzo"]=extractFirstNumberFromString(@$article["Prezzo"]);

        $newarticle["ag_dataannuncio"]=extractDateFromMixedString(@$article["Riferimento e Data annuncio"]);

        $newarticle["ag_contratto"]= (@$article["Contratto"]);
        $newarticle["ag_tipologia"]= (@$article["Tipologia"]);
        $newarticle["ag_locali"]= extractFirstNumberFromString(@$article["Locali"]);
        $newarticle["ag_tipoproprieta"]= (@$article["Tipo proprietà"]);
        $newarticle["ag_infocatastali"]= (@$article["Informazioni catastali"]);
        $newarticle["ag_annocostruzione"]= (@$article["Anno di costruzione"]);
        $newarticle["ag_statocasa"]= (@$article["Stato"]);
        $newarticle["ag_riscaldamento"]= (@$article["Riscaldamento"]);
        $newarticle["ag_climatizzazione"]= (@$article["Climatizzatore"]);
        $newarticle["ag_classe_energetica"]= (@$article["Classe energetica"]);  
		
		$newarticle["agenzia_nome"]= (@$article["agenzia_nome"]);  
		$newarticle["agenzia_alltext"]= (@$article["agenzia_alltext"]);  
		$newarticle["agenzia_tel"]= (@$article["agenzia_tel"]);  
		
    
        //Output article
        $OUTPUT_ARTICLES[]=$newarticle;
		
		saveGrabbedArticles($database,$OUTPUT_ARTICLES,@$_GET["limit"]); $OUTPUT_ARTICLES=array();
 
        $article_ind++; 

		 if($article_ind>20){
		   $current_page=99999;
		   break;
		 }
  
    }
 


}



echo "<br>************* GRABBER IMMOBILIARE IMMOBILIARE.IT ************* STOP";  

?>