<?php
//Grab posted url
$url     = filter_var($_POST['url'], FILTER_SANITIZE_STRING);

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


//fetching all the content from original url
$data    = file_get_contents_curl($url);
$start = preg_quote('<script id="__NEXT_DATA__" type="application/json" crossorigin="anonymous">', '/');
$end = preg_quote('</script>', '/');
preg_match("/$start(.*?)$end/", $data, $matches);

//checking if we got the data or not
if(!$matches){
 $response['status'] = 'fail';	
 echo json_encode($response);
 exit;
}
//getting json from content
$json = $matches[1];
//decoding json to process

$data = json_decode($json, true);


if(!$data['props']['pageProps']['statusCode']){
	
$response['status']="success";
//Getting owners info
$response['name'] =$data['props']['pageProps']['videoData']['authorInfos']['nickName'];
$response['profile_pic_url']=$data['props']['pageProps']['videoData']['authorInfos']['covers'];
$response['profileurl'] = $data['props']['pageProps']['videoObjectPageProps']['videoProps']['creator']['url'];
$response['username'] = $data['props']['pageProps']['videoData']['authorInfos']['uniqueId'];

//getting content urls

$response['flag']="video";
$response['thumbnailUrl'] = $data['props']['pageProps']['shareMeta']['image']['url'];
$response['songurl'] = $data['props']['pageProps']['videoObjectPageProps']['videoProps']['audio']['mainEntityOfPage']['@id'];

$songurl = $data['props']['pageProps']['videoObjectPageProps']['videoProps']['audio']['mainEntityOfPage']['@id'];
$videourl= $data['props']['pageProps']['videoData']['itemInfos']['video']['urls'][0];


//getting video url without watermark
$videodata=file_get_contents_curl($videourl);
$matches = array();
$pattern = '/vid:([a-zA-Z0-9]+)/';
preg_match($pattern, $videodata, $matches);
if(count($matches)>1){
$response['watermark_removed']="yes";	
$response['videourl']='https://api2.musical.ly/aweme/v1/playwm/?video_id='.$matches[1];
$response['ogvideourl']= $videourl;
}else{
	$response['watermark_removed']="no";	
	$response['videourl'] = $videourl;
    $response['ogvideourl']= $videourl;
}


//fetching audio content from audio url
$audiodata    = file_get_contents_curl($songurl);
$start = preg_quote('<script id="__NEXT_DATA__" type="application/json" crossorigin="anonymous">', '/');
$end = preg_quote('</script>', '/');
preg_match("/$start(.*?)$end/", $audiodata, $audiodatamatches);

//checking if we got the data or not
if(count($audiodatamatches)>1){
 //getting json from content
$audiodatajson = $audiodatamatches[1];
//decoding json to process

$musicdata = json_decode($audiodatajson, true);
$response['musictitle'] =$musicdata['props']['pageProps']['musicInfo']['music']['title'];
$response['musicauthor'] =$musicdata['props']['pageProps']['musicInfo']['music']['authorName'];
$response['musiccover'] =$musicdata['props']['pageProps']['musicInfo']['music']['coverThumb'];
$response['musicplayurl'] =$musicdata['props']['pageProps']['musicInfo']['music']['playUrl'];
$response['musicflag'] ='yes';
$response['musicurl'] = $songurl;
}else{
	
$response['musicflag'] ='no';	
}


//Sending data
echo json_encode($response);
}else{
$response['status']="fail";	
//Sending data
echo json_encode($response);
}
}

?>