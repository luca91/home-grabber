
<?php 

error_reporting(E_ALL);
ini_set("display_errors", 1);
require("vendor/autoload.php"); 



echo "<br>Renderer start...";
putenv('WEBDRIVER_CHROME_DRIVER=/bin/chromedriver');
$capabilities = DesiredCapabilities::chrome(); 
$driver = ChromeDriver::start();
 
$url = '';
 

 
echo "<br>Renderer stop..."
?>
