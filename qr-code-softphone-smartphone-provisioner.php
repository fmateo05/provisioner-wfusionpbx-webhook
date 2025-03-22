<?php

//use chillerlan\QRCode\{QRCode, QROptions};
//use chillerlan\QRCode\Data\QRMatrix;
//use chillerlan\QRCode\Output\QRGdImagePNG;

require_once __DIR__.'/vendor/autoload.php';

require 'vendor/autoload.php';
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


$request_data_account  = $result_acc;
$request_data_user  = $result_user;
$request_data_device  = $result_dev;

$portsip_qr_data = '{    "name": "'. $request_data_user['caller_id']['internal']['name'] . '",    "dn": "' . $request_data_account['realm'] . '",    "wdn": "' . $request_data_account['realm'] . '",    "ts": [        {            "pn": "UDP",            "port": "5060"        }   ],        "ext": "' . $request_data_device['sip']['username'] . '",    "pwd": "'. $request_data_device['sip']['password'] .'",     "v": 1 }';






	if ($json['action'] === 'doc_created' && $json['type'] === 'device' || $request_data_user['device_type'] === 'softphone' || $request_data_user['device_type'] === 'smartphone'){
	       $ecc = 'L';
$pixel_Size = 10;
$frame_Size = 5;
$ext = 'svg+xml';
$path = 'images';
$file =  $path.uniqid().".png";



header('Content-type: image/png'); 


	       $qrcode = QRcode::png($portsip_qr_data, $file, QR_ECLEVEL_H, 10, 0);

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

	       $printftestsvg = sprintf('<p><img src="%s" width="300" height="280"/></p>','https://portal.example.com/qr/' . $file);

$html = "<!DOCTYPE html>
<html>
<head>
  <title>QR Code</title>
</head>
<body>
 <td>
        <h3>
  Don't Forget to Scan your QR Code for Kazoo/PortSIP PBX Login
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
