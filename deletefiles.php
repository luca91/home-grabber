<?php
  

echo "<br>*delete files T";  
$files = glob('cache/*'); // get all file names
foreach($files as $file){ // iterate files
   var_dump($file);echo "<br>";
    unlink($file); // delete file
}

?>