<?php
//phpinfo();
//print_r(ini_get_all());
//print_r(ini_get_all(null, false));
//echo ini_get('allow_url_fopen');

$imgurl = 'http://s3.amazonaws.com/scrnshots.com/screenshots/283317/godmotherbackupjpg';

/* if (function_exists('curl_init')) { // Try with Curl 
	$curl = curl_init();
	$localimage = fopen("test.jpg", "wb");
	curl_setopt($curl, CURLOPT_URL, $imgurl);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($curl, CURLOPT_FILE, $localimage);
	curl_exec($curl);
	curl_close($curl);
} else { // Try with files functions
	$filedata = "";
	$remoteimage = fopen($imgurl, 'rb');
	if ($remoteimage) {
		 while(!feof($remoteimage)) {
			$filedata.= fread($remoteimage, 1024*8);
		 }
	}
	fclose($remoteimage);
	$localimage = fopen("test.jpg", 'wb');
	fwrite($localimage, $filedata);
	fclose($localimage);
} */

$full = imagecreatefromjpeg($imgurl);
//$full = imagecreatefromjpeg('test.jpg');
if (!$full)
	echo 'Failed';

// Get image size
$w = imagesx($full);
$h = imagesy($full);
$aspectRatio = (float)$w / (float)$h;
$tnSize = 240; // Pixels
//echo $width, $height;
if ($aspectRatio > 1.0) { // Landscape format
	$tnW = $tnSize;
	$tnH = $tnSize / $aspectRatio;
}
else { // Portrait format
	$tnH = $tnSize;
	$tnW = $tnSize * $aspectRatio;
}

$tn = imagecreatetruecolor($tnW, $tnH);
imagecopyresampled($tn, $full, 0, 0, 0, 0, $tnW, $tnH, $w, $h); 

imagejpeg($tn, 'tn.jpg');
imagedestroy($tn);
imagedestroy($full);
	
/*
// Fetch full size shot from scrnshots.com and copy it to the cache folder
$im = imagecreatefromjpeg('http://s3.amazonaws.com/scrnshots.com/screenshots/283317/godmotherbackupjpg');
if (!$im)
	echo 'Failed';
echo 'saving';
imagejpeg($im, './tn.jpg');
//imagejpeg($im, '../../scrnshots-com/tn.jpg');
imagedestroy($im);
echo 'done';
*/
?>