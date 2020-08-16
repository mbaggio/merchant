<?php

// call some of our main endpoints to fill elastic search with hits
$categories = [8, 18, 19, 20, 17, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31 ];

// Add 2hs to current time
$dateTimestamp1 = strtotime(date("Y-m-d H:i:s", strtotime('+2 hours'))); 

do {
    $date2 = date("Y-m-d H:i:s"); 
    $dateTimestamp2 = strtotime($date2); 
  
	// call the endpoint
	$ob_id = array_rand(array_flip($categories), 1);
    
    // just let's do a filter
    if ($ob_id % 2 != 0 || (rand(0, 100) < 60 && $ob_id % 2 == 0)) {
        file_get_contents("http://localhost:5000/merchants-details/".$ob_id);    
        sleep(1);
    }

} while ($dateTimestamp1 > $dateTimestamp2);

?>
