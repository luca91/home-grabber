<?php
 error_reporting(E_ALL);
/*

aste tribunale
*/
 
echo "<br>************* GRABBER aste ************* START"; 


$form_query_domain    ="http://www.tribunale.bolzano.it";
 
$full_articles_link   ="http://www.tribunale.bolzano.it/it/Aste/SearchImmobile";
   
for($current_page=1;$current_page<2;$current_page++){
    echo "<br><br>++++ PAGE ANAL:".$current_page; 
	
	$post = array(
    'ResultPerPage' => '120',
    'SortingType' => 'DataPubblicazioneDesc',
	'LocationsStrings' => 'Provincia: Bolzano,',//Provincia%3A+Bolzano%2C
	'SoloConFoto' => 'false',
    'AstePassate'   => 'false',
	'Page'   => $current_page,
	'NewView'=>'false',
	'cerca'=>'Cerca'
    );
     
    $sUrlHtml = getWebsiteContentPost($full_articles_link,true,$post);  

    $dom = new DOMDocument();
    //echo $sUrlHtml;exit;
    @$dom->loadHTML($sUrlHtml);
    $xpath = new DomXPath($dom); 
	
    $articles  = $xpath->query('//a[@class="icon lente detailAsta"]');
    $article_ind=1;
    foreach($articles as $article_tr)
    { 
		$full_article_link= trim($article_tr->getAttribute('href'));
		  
		//continue;
		
		//$full_article_link="http://www.tribunale.bolzano.it/it/Aste/DetailImmobile/B1744421-Abitazione-di-tipo-civile-Via-Orazio-44-Bolzano";
		
		$article=array();
		$ARTICLE_DB=getRecordByTableAndField($database,"grabbed_articles",$full_article_link,"ag_url"  );
		if(is_numeric(@$ARTICLE_DB["id"])){
			echo "<br>Article already processe...... Skip to next<br><br>";
			continue;
		}
    

		//$full_article_link=substr($full_article_link, 0, -3);
		
		
        echo '<br><br>'.$article_ind.') Link: ' . $full_article_link ;

        $article_id=md5($full_article_link);

        $article_dom = new DOMDocument();
        $article_html = getWebsiteContent($full_article_link,true);
		  
        @$article_dom->loadHTML($article_html);
	
        $article_xpath = new DomXPath($article_dom); 
    
        $article["ag_id"]=$article_id;
		
    
        $article["codice"]=trim($article_tr->textContent);
		
		
        //Title
        $article["title"]        = $article_xpath->query('//div[@class="testoIntro"]/h2')->item(0)->textContent;  
        echo '<br>'.$article_ind.')   title='.@$article["title"];
        $article["via"]=$article["title"] ;
        
		
		//Agenzia 
		$article["agenzia_nome"]         ="ASTA TRIBUNALE BOLZANO";

     
	 
        $article_tables= $article_xpath->query('//table[@summary="Dati del bene"]'); 
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('th')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(0)->nodeValue); 
				
				
				 if(!util_string_contains($art_tab_key,"Descrizione")){
				   $article["description"].=";".$art_tab_key.":".$art_tab_val.";";
				}
 
                //SAnitazion
				if(util_string_contains($art_tab_key,"Tipologia")){
					$art_tab_key="tipologia";
				}	
				else if(util_string_contains($art_tab_key,"Descrizione")){
				//	 $article["description"].="<br>".$art_tab_val;
				}	
                else if(util_string_contains($art_tab_key,"Indirizzo")){
					$art_tab_key="indirizzo";
				}
				 else if(util_string_contains($art_tab_key,"Foglio")){
					break;
				}
				else if(util_string_contains($art_tab_key,"Particella")){
					break;
				}
                				


			    echo '<br>'.$article_ind.')KV)   '. $art_tab_key.'='.$art_tab_val;
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}  		
		  } 
		} 
		
		
		
		//Various info
		//$article_tables= $article_xpath->query('//table[@summary="Dati dell\' asta con riferimento B1809454"]'); 
		$article_tables= $article_xpath->query("//table[contains(@summary,'asta con riferimento')]"); 
		
		if(!is_null( $article_tables )){ 
		  foreach($article_tables as $article_table)
		  {
			echo '<br>'.$article_ind.")KV) Table found";
			
			foreach($article_table->getElementsByTagName('tr') as $tr)
			{
				$art_tab_key = @trim(@$tr->getElementsByTagName('th')->item(0)->nodeValue);  
				$art_tab_val = @trim(@$tr->getElementsByTagName('td')->item(0)->nodeValue); 
				
				 $article["description"].=";".@$art_tab_key.":".@$art_tab_val.";";
/*
1)KV) Table found
1)KV) Tipologia=Senza incanto
1)KV) Data asta=22/05/2020 - 09:00
1)KV) Indirizzo=Piazza Tribunale, 1 AULA F 39100 Bolzano (BZ)
1)KV) Prezzo=248.994,00 â‚¬
1)KV) Offerta minima=186.750,00
1)KV) Rialzo minimo=2.500,00
1)KV) Termine presentazione offerte=21/05/2020 - 12:00
1)KV) localita=Altra modalitÃ 
*/
                //SAnitazion
				if(util_string_contains($art_tab_key,"alit")){
					$art_tab_key="localita";
				}	
				else if(util_string_contains($art_tab_key,"Via,")){
					$art_tab_key="via";
				}	 
                else if(util_string_contains($art_tab_key,"Offerta minima")){
					$art_tab_key="prezzo";
				} 				


			    echo '<br>'.$article_ind.')KV0)   '. $art_tab_key.'='.$art_tab_val;
				
			
				if(strlen($art_tab_val)>0){
					$article[$art_tab_key]=$art_tab_val;
				} 
			}
			break;			
		  } 
		} 
		
		
 
        
        $newarticle=array();
        $newarticle["ag_images"]=array();

 
         
		if(true){
			$article_table= $article_xpath->query('//img[@class="borderimg"]'); 
			if(!is_null( $article_table )){ 
				$imgindex=0;
				foreach($article_table as $tr)
				{
					 
					
						$full_img_link=$tr->getAttribute('src');
						
						echo '<br>'.$article_id.')   Image='.$full_img_link;
						array_push($newarticle["ag_images"],$full_img_link);

						$img_lfolder=@downloadImage($article,$full_img_link,"".$imgindex."_");
			 
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

        $newarticle["ag_cod"]= (@$article["procedura"]);
        $newarticle["ag_tipoofferta"]=@$article["Tipologia"];
        $newarticle["ag_provincia"]="Bolzano";
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



echo "<br>************* GRABBER aste************* STOP";  

?>