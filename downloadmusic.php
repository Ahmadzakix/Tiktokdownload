<?php
$url     = filter_var($_GET['url'], FILTER_SANITIZE_STRING);

if($url){

function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	//browser's user agent string (UA) 
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$output    = file_get_contents_curl($url);
    header("Content-type: application/octet-stream"); 
    header("Content-Disposition: attachment; filename=audio.mp3"); 
    echo $output;


}
?>