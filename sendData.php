<?php
    ini_set("include_path", '/pathTo/yourPHPfolder/php:' . ini_get("include_path") );
    
    $siteURL = "SITE URL THAT THE IMAGE IS BEING APPENDED"; 
    
    $servername = "localhost";
	$port = "3306";
	$dbuser =  "PROVIDE YOUR DB USERNAME";
	$userpw = "PROVIDE YOUR DB PASSWORD";
	$dbName = "PROVIDE YOUR DB NAME";
	
	$datetime = new DateTime(); 
	$timezone = new DateTimeZone('Asia/Kuala_Lumpur'); 
	$datetime->setTimezone($timezone); 
	
	$USERDATA = $_POST["userData2"];
	$obj = json_decode($USERDATA); 
	
	$EventID = $obj->EventID; 
	$Name = $obj->FullName;
	$Contact = $obj->MobileNo;
	$Email = $obj->EmailAddr;
	$PicturePath = $obj->ExtraData;
	$DTime = $datetime->format('Y-m-d H:i:s'); 
	
	$conn = new mysqli($servername, $dbuser, $userpw, $dbName);

	if(!$conn) 
	{
		die("Connection Failed: ". mysqli_connect_error());
		echo "failed lol";
	}
	$sql = "INSERT INTO imVBoothDB_users (EVENTID,NAME,EMAILADDR,PHONENUM,EXTRADATA,datetime) 
	VALUES ('".$EventID."','".$Name."','".$Email."','".$Contact."', '".$PicturePath."', '".$DTime."')";


	if(mysqli_query($conn, $sql))
	{
		echo "New Record Created Successfully";
	}
	else
	{
		echo "Error";
	}
	
	$DTime = $datetime->format('Y-m-d_H-i-s');
	
	$b64 = $PicturePath; 
	$bin = base64_decode($b64); 
	$im = imageCreateFromString($bin); 
	if (!im) {
	    die('Base64 value is not a valid image');
	}
	
 	$img_file = "./imageUploads/images/vBooth-$DTime.jpg"; 
	imagejpeg($im, $img_file, 100); 
    imagedestroy($im); 	
	
	$hyperlink_file = $siteURL."imageUploads/images/vBooth-$DTime.jpg";
	echo "\n".$hyperlink_file."\n"; 
	
	include('Mail.php'); 
	include('Mail/mime.php'); 
	
	$recipients = $Email; 
	$headers['From'] = 'PROVIDE THE EMAIL THAT IS UNDER YOUR DOMAIN'; 
	$headers['To'] = $Email; 
	$headers['Subject'] = 'Thanks for playing! '; 
	$crlf = "\r\n"; 
	
	$mime = new Mail_mime($crlf);
	
	$body = "Here's the most beautiful, you: "; 
	$message = "<b>Here's the most beautiful, you: </b>";
	$message .= "<a href='$hyperlink_file'>click here to view</a><br>";
    $message .= "<p>with love, <br>BRAND NAME</p>"; 
	
	$mime->setTXTBody($body); 
	$mime->setHTMLBody($message); 
	
	$body = $mime->get(); 
	$headers = $mime->headers($headers); 
	
	$params['sendmail_path'] = '/usr/lib/sendmail'; 
	
	$mail_object =& Mail::factory('sendmail', $params); 
	$mail_object->send($recipients, $headers, $body); 
	
	mysqli_close($conn);
	
	header('Content-Type:application/json'); 
	$arr = ["path" => '123']; 
	echo json_encode($arr); 
?>