<?php

//use chillerlan\QRCode\{QRCode, QROptions};
//use chillerlan\QRCode\Data\QRMatrix;
//use chillerlan\QRCode\Output\QRGdImagePNG;

require_once __DIR__ . '/vendor/autoload.php';

require 'vendor/autoload.php';
//require_once __DIR__.'/vendor/autoload.php';

$json = json_decode(file_get_contents("php://input"), true);

//file_put_contents("/var/www/html/webhook-data-qr.log",print_r($json,true));


$wh_action = $json['action'];
$wh_type = $json['type'];

if ($wh_type === 'user') {
    $user_id = $json['id'];
}




$account_id = $json['account_id'];

function _get_account_db($account_id) {
    // account/xx/xx/xxxxxxxxxxxxxxxx
    return "account/" . substr_replace(substr_replace($account_id, "/", 2, 0), "/", 5, 0);
}

$account_db = str_replace('/', '%2F', _get_account_db($account_id));

$couch_user = '';
$couch_pass = '';
$couch_host = '';
$couch_port = '15984';

$pbxuser = 'fusionpbx';
$password = '';
$host = ':5432';
$database = 'fusionpbx';
$account_couchdb_id = $account_id;

$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port;

$dbconn = "postgres://" . $pbxuser . ":" . $password . "@" . $host . "/" . $database . "?sslmode=require";

$user = $user_id;

$command_user = "curl -s " . $conn . '/' . $account_db . '/' . $user . '| python3 -mjson.tool';
$document = shell_exec($command_user);

$result_user = json_decode($document, true);






$account = $account_id;

$command_acc = "curl -s " . $conn . '/' . $account_db . '/' . $account . '| python3 -mjson.tool';
$document_acc = shell_exec($command_acc);

$result_acc = json_decode($document_acc, true);

//$result_user = device_value_user($result_dev['owner_id'],$account_db);
//file_put_contents('/var/www/html/webhook-data-qr.log',print_r($result_dev,true),FILE_APPEND);	


$request_data_account = $result_acc;
$request_data_user = $result_user;
//$request_data_device  = $result_dev;

$number =   $request_data_user['presence_id'] ?? $request_data_account['caller_id']['external']['number']   ;

$user_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $user_id);
$account_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $account_id);

// $portsip_qr_data = '{    "name": "'. $request_data_user['caller_id']['internal']['name'] . '",    "dn": "' . $request_data_account['realm'] . '",    "wdn": "' . $request_data_account['realm'] . '",    "ts": [        {            "pn": "UDP",            "port": "5060"        }   ],        "ext": "' . $request_data_device['sip']['username'] . '",    "pwd": "'. $request_data_device['sip']['password'] .'",     "v": 1 }';


$contact_uuid_new = trim(file_get_contents('/proc/sys/kernel/random/uuid'));


$sel_contact = "SELECT contact_uuid FROM public.v_contacts where domain_uuid='" . $account_uuid . "' and contact_name_given='" . $request_data_user['first_name'] . "' and v_contacts.contact_name_family='" . $request_data_user['last_name'] . "'" ;
$sel_cmd = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_contact . '"');
$sel_contact_phones = "SELECT contact_phone_uuid FROM public.v_contact_phones where contact_uuid='" . $sel_cmd . "';";
$sel_contact_cmd = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_contact_phones . '"'));

if ($json['action'] === 'doc_created' && $json['type'] === 'user') {
        file_put_contents('/var/www/html/webhook-user.log', $sel_contact . "\n" . $sel_contact_cmd);
    $ins_user = "INSERT INTO public.v_contacts (contact_uuid, domain_uuid, contact_organization,contact_name_given,contact_name_family) VALUES('" . $user_uuid . "','" . $account_uuid . "','" . $request_data_account['name'] . "','" . $request_data_user['first_name'] . "','" . $request_data_user['last_name'] . "');";
    $ins_contact_num = "INSERT INTO public.v_contact_phones (contact_phone_uuid, domain_uuid, contact_uuid,phone_label, phone_type_voice, phone_type_fax, phone_type_video, phone_type_text, phone_number, phone_extension, phone_primary, phone_description) VALUES('" . $contact_uuid_new . "','" . $account_uuid . "',(" . $sel_contact . "), 'Work', 1, 0, 0, 0,'". $number  ."','" . $request_data_user['presence_id'] . "', 1, 'Work');";
    $cmd = "sudo psql -d " . $dbconn . "<< EOF\n" . $ins_user . "\n" . $ins_contact_num . "\n" . "EOF" . "\n";
    shell_exec($cmd);
} else if ($json['action'] === 'doc_edited' && $json['type'] === 'user') {
    
    $del_contat_phones = "DELETE FROM public.v_contact_phones where domain_uuid='" . $account_uuid . "' and contact_phone_uuid=(". $sel_contact .");";
    $del_contact = "DELETE FROM public.v_contacts WHERE contact_uuid=(" . $sel_contact . ");";
    $cmd_del = "sudo psql -d " . $dbconn . " -c ' " . $del_contact_phones . "';";
    $cmd_del_contact = "sudo psql -d " . $dbconn . " -c ' " . $del_contact . "';";
     file_put_contents('/var/www/html/webhook-user.log', $del_contact_phone . "\n" . $del_contact, FILE_APPEND);
     
    shell_exec($cmd_del);
    shell_exec($cmd_del_contact);

    $ins_user = "INSERT INTO public.v_contacts (contact_uuid, domain_uuid, contact_organization,contact_name_given,contact_name_family) VALUES('" . $user_uuid . "','" . $account_uuid . "','" . $request_data_account['name'] . "','" . $request_data_user['first_name'] . "','" . $request_data_user['last_name'] . "');";
    $ins_contact_num = "INSERT INTO public.v_contact_phones (contact_phone_uuid, domain_uuid, contact_uuid,phone_label, phone_type_voice, phone_type_fax, phone_type_video, phone_type_text, phone_number, phone_extension, phone_primary, phone_description) VALUES('" . $contact_uuid_new . "','" . $account_uuid . "',(" . $sel_contact . "), 'Work', 1, 0, 0, 0,'". $number . "' ,'" . $request_data_user['presence_id'] . "', 1, 'Work');";
    $cmd_ins = "sudo psql -d " . $dbconn . "<< EOF\n" . $ins_user . "\n" . $ins_contact_num . "\n" . "EOF" . "\n";
    shell_exec($cmd_ins);
    file_put_contents('/var/www/html/webhook-user.log', $ins_user . "\n" . $ins_contact_num, FILE_APPEND);

//    $upd_user = "UPDATE public.v_contacts SET domain_uuid='" . $user_uuid . "', contact_organization='" . $request_data_account['name'] . "', contact_name_given='" . $request_data_user['first_name'] . "', contact_name_family='" . $request_data_user['last_name'] . "'
//    WHERE contact_uuid='" . $sel_cmd . "';";
//    $upd_contact = "UPDATE public.v_contact_phones AS tgt SET domain_uuid='" . $user_uuid . "', phone_type_voice=src.phone_type_voice, phone_type_fax=src.phone_type_fax, phone_type_video=src.phone_type_video, phone_type_text=src.phone_type_text, phone_number='" . $request_data_user['presence_id'] . "', phone_extension='" . $request_data_user['presence_id'] . "' FROM v_contacts WHERE contact_phone_uuid='" . $sel_contact_cmd . "';";
//    
//    $cmd_upd = "sudo psql -d " . $dbconn . "<< EOF\n" . $upd_user . "\n" . $upd_contact . "\n" . "EOF" . "\n";
//    shell_exec($cmd_upd);
    
} else if ($json['action'] === 'doc_deleted' && $json['type'] === 'user') {

    $del_contat_phones = "DELETE FROM public.v_contact_phones where domain_uuid='" . $account_uuid . "' and contact_phone_uuid=(". $sel_contact .");";
    $del_contact = "DELETE FROM public.v_contacts WHERE contact_uuid=(" . $sel_contact . ") AND domain_uuid='". $account_uuid . "';";
    $cmd_del = "sudo psql -d " . $dbconn . " -c ' " . $del_contact_phones . "';";
    $cmd_del_contact = "sudo psql -d " . $dbconn . " -c ' " . $del_contact . "';";
    shell_exec($cmd_del);
    shell_exec($cmd_del_contact);
} else {

    echo "No action or event from webhook performed";
}
