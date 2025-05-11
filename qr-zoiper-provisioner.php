<?php

//use chillerlan\QRCode\{QRCode, QROptions};
//use chillerlan\QRCode\Data\QRMatrix;
//use chillerlan\QRCode\Output\QRGdImagePNG;

//require_once __DIR__.'/vendor/autoload.php';

//require 'vendor/autoload.php';
//require_once __DIR__.'/vendor/autoload.php';

$json = json_decode(file_get_contents("php://input"),true);


//file_put_contents("/var/www/html/webhook-data-qr.log",print_r($json,true));


$wh_action = $json['action'];
$wh_type = $json['type'];



if($wh_type === 'device'){
$device_id = $json['id'];

}



		
$account_id = $json['account_id'];


function _get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, "/", 2, 0), "/", 5, 0);
    }


$account_db = str_replace('/','%2F',_get_account_db($account_id));



$couch_user = '';
$couch_pass = '';
$couch_host = '';
$couch_port = '15984';

$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port ;
$device = $device_id;

$command_dev = "curl -s ". $conn . '/'  . $account_db . '/' . $device . '| python3 -mjson.tool' ;
$document = shell_exec($command_dev);

$result_dev = json_decode($document,true);

function device_value_user($device_key_value,$account_db){
$couch_user = '';
$couch_pass = '';
$couch_host = '';
$couch_port = '15984';

$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port ;
$users = $device_key_value;

$command_user = "curl -s ". $conn . '/'  . $account_db . '/' . $users . '| python3 -mjson.tool' ;
$document_user = shell_exec($command_user);
$result_user = json_decode($document_user,true);

return $result_user;
}


$account = $account_id;


$command_acc = "curl -s ". $conn . '/'  . $account_db . '/' . $account . '| python3 -mjson.tool' ;
$document_acc = shell_exec($command_acc);

$result_acc = json_decode($document_acc,true);

$result_user = device_value_user($result_dev['owner_id'],$account_db);
//file_put_contents('/var/www/html/webhook-data-qr.log',print_r($result_dev,true),FILE_APPEND);	

$app_id = '';
$prov_id = '';


$request_data_account  = $result_acc;
$request_data_user  = $result_user;
$request_data_device  = $result_dev;

$zoiper_qr_data = 'https://oem.zoiper.com/qr.php?provider_id='. $prov_id . '&u='. $request_data_device['sip']['username']. '&h='$request_data_account['realm']'&p='. $request_data_device['sip']['password'] .'&o=&t=&x=&a='.$request_data_device['sip']['username'] .'&tr=';






	if ($json['action'] === 'doc_created' && $json['type'] === 'device' && $request_data_device['device_type'] === 'sip_device'){
	       $ecc = 'L';
$pixel_Size = 10;
$frame_Size = 5;
$ext = 'svg+xml';
$path = 'images';
$file =  $path.uniqid().".png";



// header('Content-type: image/png'); 

               $to      = '"' . $request_data_user['email'] . '"';
	       

               $subject = 'Your PortSIP / Kazoo QR Credentials';
	       // message with attachment
               $headers = 'From: qrcode@example.com'       . "\r\n" .
                 'Reply-To: info@example.com' . "\r\n" .
                 'Content-Type: text/html;charset=utf-8' . "\r\n" .
                 'X-Mailer: PHP/' . phpversion();
	       $imgstring = trim( str_replace('data:image/'.$ext.';base64,', "", $qrcode) );
        $imgstring = str_replace( ' ', '+', $imgstring );
        $data = base64_decode( $imgstring );

	       $printftestsvg = sprintf('<p><img src="%s" width="300" height="280"/></p>',$zoiper_qr_data);

$html = "<!DOCTYPE html>
<html>
<head>
  <title>QR Code</title>
</head>
<body>
 <td>
        <h3>
  Scan your QR Code for Kazoo PBX Login with Zoiper
  \r\n
            </h3>
". $printftestsvg . "
        <br />
            </td>
</body>
</html>";
	       mail($to,$subject,$html,$headers);
	       

	} else {
			echo "No action or event from webhook performed";
		}
