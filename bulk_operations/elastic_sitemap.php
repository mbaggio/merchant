<?php

// call some of our main endpoints to fill elastic search with hits
$categories = [55, 56, 57, 58, 54, 31, 59, 60, 61];


// Add 2hs to current time
$dateTimestamp1 = strtotime(date("Y-m-d H:i:s", strtotime('+2 hours'))); 

do {
    $date2 = date("Y-m-d H:i:s"); 
    $dateTimestamp2 = strtotime($date2); 
  
	// call the endpoint
	$sitemap_id = array_rand(array_flip($categories), 1);
    
    // just let's do a filter
    if ($sitemap_id % 2 != 0 || (rand(0, 100) < 60 && $sitemap_id % 2 == 0)) {
        file_get_contents("http://localhost:5000/sitemap_categories/".$sitemap_id."/merchants/1");
    }

} while ($dateTimestamp1 > $dateTimestamp2);

?>
