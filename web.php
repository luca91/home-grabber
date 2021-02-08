<?php

//require_once("lib/phpgrid/conf.php");  

require_once("lib/phpGrid_Lite/conf.php");      
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PHPGrid Image Display</title>
</head>
<body> 

<?php
$mainq="SELECT ag_url,ag_dimensioni,ag_prezzo,ag_description,ag_title,ag_id FROM grabber_case.grabbed_articles ";
$dg = new C_DataGrid($mainq );
$dg -> enable_debug(true);
$dg->set_query_filter('ag_prezzo <330');
//$dg -> set_multiselect(true);
//$dg->set_sql_key("ag_id");

$dg -> set_col_link("ag_url");
$dg -> set_col_title("ag_dimensioni", "Dimensioni");
$dg -> set_col_title("ag_prezzo", "Prezzo");
$dg -> set_col_title("ag_description", "Descrizione");
$dg -> set_col_title("ag_title", "Titolo");

// display static Url
$dg -> set_col_link("productUrl");                                             

// display dynamic url. e.g.http://www.example.com/?productCode=101&foo=bar
//$dg -> set_col_dynalink("productCode", "http://www.example.com/", "productCode", '&foo=bar');                                                                   
// the above line is equivalent to the following helper function                        
//$dg -> set_col_currency("MSRP", "$", '', ",",".", "2", "0.00");    
                                                                                     
// display image
//$dg -> set_col_img("Image");

 
//set detail for products
$detailgrid = new C_DataGrid("SELECT * FROM grabbed_articles_meta ","id","grabbed_articles_meta");
//$detailgrid->set_sql_key("id");

$detailgrid -> set_col_title("a_key", "Chiave");
$detailgrid -> set_col_title("a_value", "Valore");

$dg -> set_masterdetail($detailgrid, 'ag_id', 'ag_id');
//$dg -> set_subgrid($detailgrid, 'ag_id');

$dg -> display();
?>

</body>
</html>
