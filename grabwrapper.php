<?php  
set_time_limit(10);
error_reporting(E_ALL);
ini_set("display_errors", 1);
$url=base64_decode($_GET["u"]);
//$url=base64_decode($url);

if(strlen(@$url)>4){
	//Clean processes
	//$output1=""; $output2="";
	//exec("killall /bin/chromedriver", $output1);
	//exec("killall /opt/google/chrome-beta/chrome", $output2);	
		
	$command='/usr/bin/python /var/www/papin/homegrabber/grabdata.py "'.$url.'" 2>&1';
	//echo "<br>Command:".$command;
	//$command = escapeshellcmd($command);

	//$output = shell_exec($command);exit();
	exec($command, $output);
	$output = implode("", $output);
	//var_dump( $output); 
	
}else{
	  exit("grabwrapper error url length");
}

exit($output);
?>