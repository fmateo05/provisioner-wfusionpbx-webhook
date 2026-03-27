<?php

define('__ROOT__', dirname(dirname(__FILE__)));

require_once(__ROOT__.'/env.php');


$json = json_decode(file_get_contents("php://input"),true);


//file_put_contents("/var/www/html/webhook-data.log",print_r($json,true), FILE_APPEND);


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



//$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port ;
$device = $device_id;

$command_dev = "curl -s ". $conn . '/'  . $account_db . '/' . $device . '| python3 -mjson.tool' ;
$document = shell_exec($command_dev);

$result_dev = json_decode($document,true);

function device_value_user($device_key_value,$account_db,$conn){



//$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port ;
//$device_key_value_user = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['value']);  
$users = $device_key_value;

$command_user = "curl -s ". $conn . '/'  . $account_db . '/' . $users . '| python3 -mjson.tool' ;
$document_user = shell_exec($command_user);
$result_user = json_decode($document_user,true);
//file_put_contents('/var/www/html/webhook-data.log',print_r($command_user,true));	

return $result_user['presence_id'];
}


$account = $account_id;


$command_acc = "curl -s ". $conn . '/'  . $account_db . '/' . $account . '| python3 -mjson.tool' ;
$document_acc = shell_exec($command_acc);

$result_acc = json_decode($document_acc,true);


$request_data_account  = $result_acc;
$request_data_user  = $result_user;
$request_data_device  = $result_dev;

$other_uuid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));

// $user = 'fusionpbx';
// $password = '';
// $host ='';
// $database ='fusionpbx';

// $credentials = trim('Your-MD5-Creds');
// $otf_couch_host = 'callmgr1-z100.lxd';
// $otf_couch_port = '8000';
// $otf_conn = "http://" . $otf_couch_host . ':' . $otf_couch_port  . '/v2/';


$account_couchdb_id = $account_id;

$account_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $account_couchdb_id);

function new_uuid(){


$new_uuid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));

return $new_uuid ;

}

function json_patch($account_id,$cmd_json_patch) {
$tmpfile = '/tmp/'  .  $account_id . '.lock';

	if(file_exists($tmpfile)){
	file_put_contents("/var/www/html/webhook-data.log","\n". $cmd_json_patch, FILE_APPEND);
	 shell_exec('touch ' .$tmpfile);
	shell_exec($cmd_json_patch);
	 
	} else {
 	echo 'nothing';
	
	}
//	shell_exec('rm -f ' . $tmpfile);

}

$dbconn = "postgres://" . $user . ":" . $password . "@" . $host . "/" . $database . "?sslmode=require" ;


$prov_url = 'https://' . str_replace('sip','prov',$request_data_account['realm']) . '/app/provision';
$prov_domain =  str_replace('sip','prov',$request_data_account['realm']) ;
$sip_domain =  str_replace('prov','sip',$prov_domain) ;

$auth_doc = '{
  "data": {
      "credentials": "'. $credentials .'",
      "account_name": "master"
  }
}';

$otf_json = '{ 
   "data":{
    "grandstream_config_server_path": "'. $prov_url .'",
    "http_auth_username": "'. $account .'",
    "http_auth_password": "'. $other_uuid .'"
  }
}';

$cmd_json_auth= 'curl -s -H "Content-Type: application/json" -X PUT ' . $otf_conn . 'user_auth -d ' . "'" . $auth_doc . "'" ;
$json_auth = json_decode(shell_exec($cmd_json_auth),true);
$cmd_json_get= 'curl -s -H "Content-Type: application/json" -H "X-Auth-Token: '. $json_auth['auth_token']. '" -X GET ' . $otf_conn . 'accounts/' . $account ;
$cmd_json_patch = 'curl -s -H "Content-Type: application/json" -H "X-Auth-Token: ' . $json_auth['auth_token'] . '"  -X PATCH ' . $otf_conn . 'accounts/' . $account  .  ' -d ' . "'".  $otf_json  . "'";
//$cmd_json_post = 'curl -s -H "Content-Type: application/json" -X PUT ' . $conn . '/accounts/' . $account . '?rev=' . $json_rev  .  ' -d ' . "'".  $otf_json  . "'";
$mac_address = $request_data_device['mac_address'];
  $sql_settings_macaddr_check  = "SELECT device_address from public.v_devices WHERE device_address ='" . $mac_address . "' LIMIT 1;" ;
  $sql_settings_otherdevice_check  = "SELECT device_uuid from public.v_devices WHERE device_address ='" . $mac_address . "' LIMIT 1;" ;
  $sql_settings_otherdomain_check  = "SELECT domain_uuid from public.v_devices WHERE device_address ='" . $mac_address . "' LIMIT 1;" ;

//$cmd_json_del= 'curl -s -H "Content-Type: application/json" -X DELETE ' . $otf_conn . '/' . $otf_couch_schema . '/'  . $account . '?rev=' . $json_rev  ; 
	$sql_settings_prov_check  = "SELECT * from public.v_domain_settings WHERE domain_uuid ='" . $account_uuid . "' AND domain_setting_category='provision' AND domain_setting_subcategory='enabled' LIMIT 1;" ;

	$sql_settings_auth_type = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'auth_type', 'text','basic', 0, true, 'added from webhook');";
        $sql_settings_prov_enable = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'enabled', 'boolean',true, 0, true, 'added from webhook');";
        $sql_settings_httpauth_enable = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'http_auth_enabled', 'boolean',true, 0, true, 'added from webhook');";
        $sql_settings_httpauth_username = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'http_auth_username', 'text','". $account_id ."', 0, true, 'added from webhook');";
        $sql_settings_httpauth_password = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'http_auth_password', 'array','". $other_uuid  ."', 0, true, 'added from webhook');";
        $sql_settings_gs_url_path = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'grandstream_config_server_path', 'text','". $prov_domain . "/app/provision/', 0, true, 'added from webhook');";
        $sql_settings_gs_pb_url_path = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'grandstream_phonebook_xml_server_path', 'text','". $prov_domain . "/app/provision/', 0, true, 'added from webhook');";
        $sql_settings_gs_pb_download = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'grandstream_phonebook_download', 'text','3', 0, true, 'added from webhook');";
        $sql_settings_gs_pb_interval= "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'grandstream_phonebook_interval', 'text','5', 0, true, 'added from webhook');";
        $sql_settings_gs_contact_gs= "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'contact_grandstream', 'boolean','1', 0, true, 'added from webhook');";
         $sql_settings_yealink_provision_url = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'yealink_provision_url', 'text','". $prov_url ."', 0, true, 'added from webhook');";
        $sql_settings_yealink_trust_ctrl = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'yealink_trust_ctrl', 'text','0', 0, true, 'added from webhook');"; 
        $sql_settings_yealink_trust_certs = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'yealink_trust_certificates', 'text','0', 0, true, 'added from webhook');";
	$sql_settings_remove = "DELETE FROM public.v_domain_settings WHERE domain_uuid='". $account_uuid  ."';";





	if ($json['action'] === 'doc_created' && $json['type'] === 'account'){
//	$sql = "INSERT INTO public.v_domains (domain_uuid, domain_parent_uuid, domain_name, domain_enabled, domain_description) VALUES(" . "'" . trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "'" .   ', null ,' . "'" . $request_data_account['realm'] . "'" . ',true,' . "'" .  $request_data_account['name'] . "'" . ");";
	$sql = "INSERT INTO public.v_domains (domain_uuid, domain_name, domain_enabled, domain_description) VALUES('". $account_uuid ."', '" . $prov_domain  .  "', true , '". $request_data_account['name'] ."');";
	$sql_settings_check = shell_exec("sudo psql -qtAx -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_prov_check . '"'  );
	file_put_contents("/var/www/html/webhook-data.log",$sql_settings_check, FILE_APPEND);

	if(empty($sql_settings_check)) { 

	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_prov_enable . '"'  );
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_auth_type . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_enable . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_username . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_password . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_url_path . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_pb_url_path . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_pb_download . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_pb_interval . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_contact_gs . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_provision_url . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_trust_ctrl . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_trust_certs . '"'  );
	shell_exec($cmd_json_patch);
	} else {
		echo "do nothing";
	}


	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );

	} else if ($json['action'] === 'doc_edited'&& $json['type'] === 'account'){
	$sel_query_acc = "SELECT domain_uuid from public.v_domains WHERE domain_name='". $prov_domain ."';";
	$query_account =  trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_acc . '"'  ));
	if(!$query_account){
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);
	$sql_ins = "INSERT INTO public.v_domains (domain_uuid, domain_name, domain_enabled, domain_description) VALUES('". $account_uuid ."', '" .  $prov_domain .  "', true , '". $request_data_account['name'] ."');";
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_ins . '"'  );
	} else if(isset($query_account)){
	$sql = "UPDATE public.v_domains SET domain_name='" . $prov_domain . "', domain_description='". $request_data_account['name'] ."' WHERE domain_uuid='" . $account_uuid .   "';"; 
	file_put_contents("/var/www/html/webhook-data.log",$sql, FILE_APPEND);
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
	} else {

	echo "Do Nothing";
	
	}



	
	
//	file_put_contents("/var/www/html/webhook-data.log",print_r($cmd_json_post,true), FILE_APPEND);

//	shell_exec($cmd_json_post);
//	shell_exec($cmd_json_del);
//	shell_exec($cmd_json_put);
	


        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );



        } else if ($json['action'] === 'doc_deleted' && $json['type'] === 'account'){
	$sql = "DELETE from public.v_domains WHERE domain_name='" .  $prov_domain . "';"; 
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
	} else {
			echo "No action or event from webhook performed";
		}


$device_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $device_id);
$account_couch_id = $request_data_device['pvt_account_id'];
$account_couch_uuid = "(SELECT domain_uuid FROM public.v_domains WHERE domain_name='". $prov_domain ."')";
//$account_couch_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $account_couch_id);
$mac_address = $request_data_device['mac_address'];
$alllinesck = array_values(array_keys($request_data_device['provision']['combo_keys'])) ;
$alllinesfk = array_values(array_keys($request_data_device['provision']['feature_keys']))  ;

if(isset($alllinesfk)) {
$countfk = 16;
} else {
$countfk = 16;
}

if(isset($alllinesck)){
$countck =  16;
} else {
$countck =  16;
}

if(isset($alllinesek)){
$countek =  16;
} else {
$countek =  16;
}

if(isset($alllinespk)){
$countpk =  16;
} else {
$countpk =  16;
}

$model = $request_data_device['provision']['endpoint_model'];
$brand =  $request_data_device['provision']['endpoint_brand'];
switch($brand){
    case "avaya":
       $modelup = strtoupper($model);
       break;
    case "snom":
        $modelup = strtoupper($model);
       break;
    default:
        $modelup = $model;
        break;
}


	if ($json['action'] === 'doc_created' && $json['type'] === 'device'){
		 $macaddr_check = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_macaddr_check . '"'  );   
		 $otherdomain_check = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_otherdomain_check . '"'  );   
		 $otherdevice_check = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_otherdevice_check . '"'  );   
		$sel_query_devices = "SELECT device_uuid FROM public.v_devices WHERE domain_uuid='". $otherdomain_check ."' AND device_address='". $mac_address ."';";
		$sel_query_device_line = "SELECT device_line_uuid FROM v_device_lines WHERE device_uuid='". $otherdevice_check ."' AND domain_uuid='". $otherdomain_check ."' LIMIT 1;";
		$sel_query_device_otherlines = "SELECT COUNT(device_line_uuid) FROM v_device_lines WHERE device_uuid='". trim($otherdevice_check) ."' AND domain_uuid='". trim($otherdomain_check) ."';";
		$query_devices =  trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_devices . '"'  ));
		$query_lines =  trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_device_line . '"'  ));
		$query_otherlines =  trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_device_otherlines . '"'  ));
		$countline = count($query_otherlines) ;
	file_put_contents("/var/www/html/webhook-data.log","Line Count :" . $query_otherlines, FILE_APPEND);

	  if (!$macaddr_check) {
	$sql = "INSERT INTO public.v_devices (device_uuid, domain_uuid, device_address, device_label, device_vendor, device_model, device_enabled, device_template, device_username, device_password, device_description) VALUES('" . $device_uuid . "'," . $account_couch_uuid . ",'" . $mac_address  . "','" . $request_data_device['name'] . "','" . $request_data_device['provision']['endpoint_brand'] . "','" . $modelup . "', true ,'" . $request_data_device['provision']['endpoint_brand'] . "/" . $modelup . "','" . $request_data_device['sip']['username'] .  "','"  . $request_data_device['sip']['password'] . "','" . $request_data_device['name'] . "');";
	 	$sql_line= "INSERT INTO public.v_device_lines (domain_uuid, device_line_uuid, device_uuid, line_number, display_name, user_id, auth_id,password, sip_port, sip_transport, register_expires, enabled,server_address) VALUES(" . $account_couch_uuid . ",'". trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "','" . $device_uuid .  "',1,'" . $request_data_device['name'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['password'] . "',5060, 'udp', 300,  true,'" . $sip_domain.  "');";
	 	$sql_line_domain= "UPDATE public.v_device_lines set server_address = '" . $sip_domain . "'  WHERE domain_uuid=". $account_couch_uuid  ." AND device_uuid='". $device_uuid  ."';";
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );
//	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line_domain . '"'  );
	  } else {
		$sql_line= "INSERT INTO public.v_device_lines (domain_uuid, device_line_uuid, device_uuid, line_number, display_name, user_id, auth_id,password, sip_port, sip_transport, register_expires, enabled,server_address) VALUES('" . trim($otherdomain_check) . "','". trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "','" . trim($otherdevice_check) .  "',". trim(($query_otherlines+1)) .",'" . $request_data_device['name'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['password'] . "',5060, 'udp', 300,  true,'" . $sip_domain.  "');";   
	file_put_contents("/var/www/html/webhook-data.log", $sel_query_device_otherlines, FILE_APPEND);
	file_put_contents("/var/www/html/webhook-data.log",$sql_line, FILE_APPEND);
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );
                $sql_line_domain= "UPDATE public.v_device_lines set label='". $user_id ."' , display_name='". $user_id ."',user_id='". $request_data_device['sip']['username']."',auth_id='". $request_data_device['sip']['username'] ."', password='". $request_data_device['sip']['password'] ."', server_address='". $sip_domain . "'  WHERE domain_uuid='". $otherdomain_check  ."' AND device_uuid='". $otherdevice_check  ."' AND device_line_uuid='". $query_lines ."';";
//	file_put_contents("/var/www/html/webhook-data.log",$sql_line_domain, FILE_APPEND);
//                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );

	  }

	$sql_settings_check = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_prov_check . '"'  );
	file_put_contents("/var/www/html/webhook-data.log",$sql_settings_prov_check, FILE_APPEND);
	if(!isset($sql_settings_check)) { 
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_prov_enable . '"'  );
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_auth_type . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_enable . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_username . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_password . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_url_path . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_pb_url_path . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_pb_download . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_pb_interval . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_contact_gs . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_provision_url . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_trust_ctrl . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_trust_certs . '"'  );
	shell_exec($cmd_json_patch);
        
	} else {
		echo "do nothing";

	}

	} if ($json['action'] === 'doc_deleted' && $json['type'] === 'device'){
		 $otherdomain_check = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_otherdomain_check . '"'  );   
		 $otherdevice_check = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_otherdevice_check . '"'  );   
	$sel_query_device_line = "SELECT device_line_uuid FROM v_device_lines WHERE device_uuid='". trim($otherdevice_check) ."' AND domain_uuid='". trim($otherdomain_check) ."' AND user_id='". $request_data_device['sip']['username'] ."';";
	$device_line_uuid = shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_device_line . '"'  );

	$sql_del_line = "DELETE FROM public.v_device_lines WHERE device_uuid ='" . trim($otherdevice_check)  . "' AND device_line_uuid='". trim($device_line_uuid) ."';"; 
	$sql = "DELETE FROM public.v_devices WHERE device_uuid ='" . $device_uuid  . "';"; 

file_put_contents("/var/www/html/webhook-data.log",print_r($sel_query_device_line,true), FILE_APPEND);
file_put_contents("/var/www/html/webhook-data.log",print_r($sql_del_line,true), FILE_APPEND);

	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_del_line . '"'  );
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );

	} else if  ($json['action'] === 'doc_edited' && $json['type'] === 'device'){
		$sel_query_devices = "SELECT device_uuid FROM public.v_devices WHERE domain_uuid='". $account_uuid ."' AND device_address='". $mac_address ."';";
		$sel_query_device_line = "SELECT device_line_uuid FROM v_device_lines WHERE device_uuid='". $device_uuid ."' AND domain_uuid='". $account_uuid ."' AND line_number='1';";
		$query_devices =  trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_devices . '"'  ));
		$query_lines =  trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_device_line . '"'  ));
//	file_put_contents("/var/www/html/webhook-data.log",print_r($query_devices,true), FILE_APPEND);
//	file_put_contents("/var/www/html/webhook-data.log",print_r($query_lines,true), FILE_APPEND);
		
		if( !$query_devices){
		$sql_ins = "INSERT INTO public.v_devices (device_uuid, domain_uuid, device_address, device_label, device_vendor, device_model, device_enabled, device_template, device_username, device_password) VALUES('". $device_uuid ."','" . $account_uuid . "','".$mac_address."', '".$request_data_device['name'] ."', '". $request_data_device['provision']['endpoint_brand']  ."','". $modelup ."', true ,'". $request_data_device['provision']['endpoint_brand'] . '/' . $modelup . "', '". $request_data_device['sip']['username'] ."', '" . $request_data_device['sip']['password'] . "') ;";
		
                $sql_line= "INSERT INTO public.v_device_lines (domain_uuid, device_line_uuid, device_uuid, line_number, label, display_name, user_id, auth_id,password, sip_port, sip_transport, register_expires, enabled, server_address) VALUES('" . $account_uuid . "','". trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "','" . $device_uuid .  "','1','" . $request_data_device['name'] . "','" . $request_data_device['name'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['password'] . "',5060, 'udp', 300,  true,'". $sip_domain  . "');";

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_ins . '"'  );
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql_ins,true), FILE_APPEND);

		
		
		} else if(isset($query_devices) && isset($query_lines)){
                $user_id = device_value_user($request_data_device['owner_id'], $account_db,$conn);
	       $sql = "UPDATE public.v_devices SET domain_uuid='".$account_uuid."', device_address='".$mac_address."', device_label='".$user_id ."', device_vendor='". $request_data_device['provision']['endpoint_brand'] ."', device_model='".$modelup ."', device_enabled=true, device_template='".$request_data_device['provision']['endpoint_brand'] . "/" . $modelup  ."', device_username='".$request_data_device['sip']['username']."', device_password='".$request_data_device['sip']['password']."' WHERE device_uuid='".$device_uuid ."' AND device_address='". $mac_address . "';";
	 	
                $sql_line_domain= "UPDATE public.v_device_lines set line_number='1', label='". $user_id ."' , display_name='". $user_id ."',user_id='". $request_data_device['sip']['username']."',auth_id='". $request_data_device['sip']['username'] ."', password='". $request_data_device['sip']['password'] ."', server_address='". $sip_domain . "'  WHERE domain_uuid='". $account_uuid  ."' AND device_uuid='". $device_uuid  ."' AND device_line_uuid='". $query_lines ."';";
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql_line_domain,true), FILE_APPEND);

//	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);
//	file_put_contents("/var/www/html/webhook-data.log",print_r($sql_line_domain,true), FILE_APPEND);
	       
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line_domain . '"'  );
		
		} else { 

		echo "do nothing";

		}




                $sql_lines_ck_del= "DELETE FROM public.v_device_keys WHERE device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='". $request_data_device['mac_address']."') ;" ;

		shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck_del . '"'  );

                $key_none_ck = range(0,($countck - 1));
		$sel_query = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='none';";
                $sel_query_call_park = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='monitored call park';";
                $sel_query_presence = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='blf';";
                $sel_query_speed_dial = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='speed_dial';";
                $sel_query_line = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='line';";
                $sel_query_call_return = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='call_return';";
                $sel_query_transfer = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='transfer';";

                $none = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query . '"'  ));
                $call_park = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_call_park . '"'  ));
                $presence = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_presence . '"'  ));
                $speed_dial = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_speed_dial . '"'  ));
                $lineline = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_line . '"'  ));
                $transfer = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_transfer . '"'  ));
                $call_return = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_call_return . '"'  ));
		

		if ($request_data_device['provision']['endpoint_brand'] === 'cisco'){ 

		$csc = 2;	
		
		
		} else {
		$csc = 0;
		}
		
                for ($h = $csc ; $h < $countfk; $h++){


                $device_key_value_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['value'])  ; 
                
                $device_key_label_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['label']);
		 
                $device_key_line_ck = '0';
		
		$device_key_id_ck = $alllinesck[$i] +1;
		$device_key_id_none_ck = $key_none_ck[$i];    
                    
                $sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$h."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', '".$none ."', '','".$device_key_line_ck."', '', '', '');";
		$sql_lines_placeholder_fk[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$h."' , 'memory', '".$request_data_device['provision']['endpoint_brand']."', '".$none ."', '','".$device_key_line_ck."', '', '', '');";
     
                $sql_lines_placeholder_ek[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$h."' , 'expansion', '".$request_data_device['provision']['endpoint_brand']."', '".$none ."', '','".$device_key_line_ck."', '', '', '');";
		$sql_lines_placeholder_pk[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$h."' , 'programmable', '".$request_data_device['provision']['endpoint_brand']."', '".$none ."', '','".$device_key_line_ck."', '', '', '');";
     
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_fk[$h] . '"'  );
                
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ek[$h] . '"'  );
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_pk[$h] . '"'  );
                    
//                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_placeholder_ck,true), FILE_APPEND);    
                    
                }
                
                for($i = 0 ; $i < $countck ; $i++ ){

                
                 $device_key_type_ck = str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type']) ; 

                

                $device_key_value_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['value'])  ; 
                
                $device_key_label_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['label']);
		 
                $device_key_line_ck = '0';
		
		$device_key_id_ck = $alllinesck[$i] +1;
		$device_key_id_none_ck = $key_none_ck[$i];
                
// file_put_contents("/var/www/html/webhook-data.log",$device_key_type_ck, FILE_APPEND);
                if($device_key_type_ck === "personal parking"){
		$user_id = device_value_user($device_key_value_ck, $account_db, $conn);
		$sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";
		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_park."' , device_key_line='".$device_key_line_ck."', device_key_value='*3".$user_id."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
                //$cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;
                 shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                } 
                else if($device_key_type_ck === "parking"){	
		$user_id = device_value_user($device_key_value_ck, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_ck."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='". $call_park ."' , device_key_line='".$device_key_line_ck."', device_key_value='*3".$device_key_value_ck."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
                // $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                }
                else if ($device_key_type_ck === 'transfer'){
		$user_id = device_value_user($device_key_value_ck, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$transfer ."' , device_key_line='".$device_key_line_ck."', device_key_value='".$user_id."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."' and  device_key_category='line' and device_key_type='". $none ."' and device_key_id='".$device_key_id_ck."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                 
                }
                else if ($device_key_type_ck === 'call return'){
		$user_id = device_value_user($device_key_value_ck, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_return ."' , device_key_line='".$device_key_line_ck."', device_key_value='".$user_id."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."' and  device_key_category='line' and device_key_type='". $none ."' and device_key_id='".$device_key_id_ck."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                 
                }
                else if ($device_key_type_ck === 'presence'){
		$user_id = device_value_user($device_key_value_ck, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$presence."' , device_key_line='".$device_key_line_ck."', device_key_value='".$user_id."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."' and  device_key_category='line' and device_key_type='". $none ."' and device_key_id='".$device_key_id_ck."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                 
                }
                else if($device_key_type_ck === "speed dial"){
		$user_id = device_value_user($device_key_value_ck, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$speed_dial."' , device_key_line='".$device_key_line_ck."', device_key_value='".$device_key_value_ck."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
//                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_ck, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );

                
                } else if($device_key_type_ck === "line"){
		$user_id = device_value_user($device_key_value_ck, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$lineline."' , device_key_line='".$device_key_line_ck."', device_key_value='' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
//                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_ck, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );

                
                }

               
                }
                
                
		$key_none_fk = range(0,($countfk - 1));
                
                
		
		
	        for ($j = 0 ; $j < $countfk  ; $j++){ 

		
                $device_key_type_fk = str_replace('_',' ',$request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['type']) ?? 'none' ;

                $device_key_value_fk = trim($request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['value'])  ; 
		 
                $device_key_line_fk = '0';
		
		$device_key_id_fk = $alllinesfk[$j] +1;
		$device_key_id_none_fk = $key_none_fk[$j];

                  if($device_key_type_fk === "personal parking"){
		$user_id = device_value_user($device_key_value_fk, $account_db, $conn);
		$sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";
		$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_park."' , device_key_line='".$device_key_line_fk."', device_key_value='*3".$user_id."'  WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
                //$cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;
                 shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                } 
                else if($device_key_type_fk === "parking"){	
		$user_id = device_value_user($device_key_value_fk, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_ck."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='". $call_park ."' , device_key_line='".$device_key_line_fk."', device_key_value='*3".$device_key_value_fk."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
                // $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_fk[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                }
                else if ($device_key_type_fk === 'call return'){
		$user_id = device_value_user($device_key_value_fk, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_return  ."' , device_key_line='".$device_key_line_fk."', device_key_value='".$user_id."' WHERE device_uuid='".$device_uuid."' and  device_key_category='memory' and device_key_type='". $none ."' and device_key_id='".$device_key_id_fk."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                 
                }
                else if ($device_key_type_fk === 'transfer'){
		$user_id = device_value_user($device_key_value_fk, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$transfer ."' , device_key_line='".$device_key_line_fk."', device_key_value='".$user_id."' WHERE device_uuid='".$device_uuid."' and  device_key_category='memory' and device_key_type='". $none ."' and device_key_id='".$device_key_id_fk."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                 
                }
                else if ($device_key_type_fk === 'presence'){
		$user_id = device_value_user($device_key_value_fk, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$presence."' , device_key_line='".$device_key_line_fk."', device_key_value='".$user_id."' WHERE device_uuid='".$device_uuid."' and  device_key_category='memory' and device_key_type='". $none ."' and device_key_id='".$device_key_id_fk."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                 
                }
                else if($device_key_type_fk === "speed dial"){
		$user_id = device_value_user($device_key_value_fk, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$speed_dial."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
//                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_fk, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );

                
                } else if($device_key_type_fk === "line"){
		$user_id = device_value_user($device_key_value_fk, $account_db, $conn);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$lineline."' , device_key_line='".$device_key_line_fk."', device_key_value='' WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
//                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_fk, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );

                
                }
               
                 else {
                    break ;
                }
                }
		$key_none_ek = range(0,($countfk - 1));
		for($k = 0 ; $k < $countek ; $k++ ){
                
                 $device_key_type_ek = str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesek[$k]]['type']) ; 
                

                $device_key_value_ek = trim($request_data_device['provision']['combo_keys'][$alllinesck[$k]]['value']['value'])  ; 
                
                $device_key_label_ek = trim($request_data_device['provision']['combo_keys'][$alllinesck[$k]]['value']['label']);
		 
                $device_key_line_ek = '0';
		
		$device_key_id_ek = $alllinesek[$k] +1;
		$device_key_id_none_ek = $key_none_ek[$k];
                

                if($device_key_type_ek === "personal parking"){
		$user_id = device_value_user($device_key_value_ek, $account_db, $conn);
		$sql_lines_ek[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_park."' , device_key_line='".$device_key_line_ek."', device_key_value='*3".$user_id."', device_key_label='".$device_key_label_ek."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='expansion' and device_key_type='".$none."' and device_key_id='".$device_key_id_ek."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ek[$k] . '"'  );
                } 
                else if($device_key_type_ek === "parking"){	
		$user_id = device_value_user($device_key_value_ek, $account_db, $conn);
                $sql_lines_ek[$k] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='". $call_park ."' , device_key_line='".$device_key_line_ek."', device_key_value='*3".$device_key_value_ek."', device_key_label='".$device_key_label_ek."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='expansion' and device_key_type='".$none."' and device_key_id='".$device_key_id_ek."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ek[$k] . '"'  );
                }
                else if ($device_key_type_ek === 'transfer'){
		$user_id = device_value_user($device_key_value_ek, $account_db, $conn);
                $sql_lines_ek[$k] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$transfer ."' , device_key_line='".$device_key_line_ek."', device_key_value='".$user_id."', device_key_label='".$device_key_label_ek."' WHERE device_uuid='".$device_uuid."' and  device_key_category='expansion' and device_key_type='". $none ."' and device_key_id='".$device_key_id_ek."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ek[$k] . '"'  );
                 
                }
                else if ($device_key_type_ek === 'call return'){
		$user_id = device_value_user($device_key_value_ek, $account_db, $conn);
                $sql_lines_ek[$k] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_return ."' , device_key_line='".$device_key_line_ek."', device_key_value='".$user_id."', device_key_label='".$device_key_label_ek."' WHERE device_uuid='".$device_uuid."' and  device_key_category='expansion' and device_key_type='". $none ."' and device_key_id='".$device_key_id_ek."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ek[$k] . '"'  );
                 
                }
                else if ($device_key_type_ek === 'presence'){
		$user_id = device_value_user($device_key_value_ek, $account_db, $conn);
                $sql_lines_ek[$k] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$presence."' , device_key_line='".$device_key_line_ek."', device_key_value='".$user_id."', device_key_label='".$device_key_label_ek."' WHERE device_uuid='".$device_uuid."' and  device_key_category='expansion' and device_key_type='". $none ."' and device_key_id='".$device_key_id_ek."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ek[$k] . '"'  );
                 
                }
                else if($device_key_type_ek === "speed dial"){
		$user_id = device_value_user($device_key_value_ek, $account_db, $conn);
                $sql_lines_ek[$k] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$speed_dial."' , device_key_line='".$device_key_line_ek."', device_key_value='".$device_key_value_ek."', device_key_label='".$device_key_label_ek."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ek."' ;"; 
//                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_ck, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ek[$k] . '"'  );

                
                } else if($device_key_type_ek === "line"){
		$user_id = device_value_user($device_key_value_ek, $account_db, $conn);
                $sql_lines_ek[$k] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$lineline."' , device_key_line='".$device_key_line_ek."', device_key_value='' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ek."' ;"; 

                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ek[$k] . '"'  );

                
                }
                
                }

		$key_none_pk = range(0,($countpk - 1));
		for($l = 0 ; $l < $countpk ; $l++ ){
                
                 $device_key_type_pk = str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinespk[$l]]['type']) ; 
                

                $device_key_value_pk = trim($request_data_device['provision']['combo_keys'][$alllinespk[$l]]['value']['value'])  ; 
                
                $device_key_label_pk = trim($request_data_device['provision']['combo_keys'][$alllinespk[$l]]['value']['label']);
		 
                $device_key_line_pk = '0';
		
		$device_key_id_pk = $alllinespk[$l] +1;
		$device_key_id_none_pk = $key_none_pk[$l];
                

                if($device_key_type_pk === "personal parking"){
		$user_id = device_value_user($device_key_value_pk, $account_db, $conn);
		$sql_lines_pk[$l] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_park."' , device_key_line='".$device_key_line_pk."', device_key_value='*3".$user_id."', device_key_label='".$device_key_label_pk."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='programmable' and device_key_type='".$none."' and device_key_id='".$device_key_id_pk."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_pk[$l] . '"'  );
                } 
                else if($device_key_type_pk === "parking"){	
		$user_id = device_value_user($device_key_value_pk, $account_db, $conn);
                $sql_lines_pk[$l] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='". $call_park ."' , device_key_line='".$device_key_line_pk."', device_key_value='*3".$device_key_value_pk."', device_key_label='".$device_key_label_pk."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='programmable' and device_key_type='".$none."' and device_key_id='".$device_key_id_pk."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_pk[$l] . '"'  );
                }
                else if ($device_key_type_pk === 'transfer'){
		$user_id = device_value_user($device_key_value_pk, $account_db, $conn);
                $sql_lines_pk[$l] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$transfer ."' , device_key_line='".$device_key_line_pk."', device_key_value='".$user_id."', device_key_label='".$device_key_label_pk."' WHERE device_uuid='".$device_uuid."' and  device_key_category='programmable' and device_key_type='". $none ."' and device_key_id='".$device_key_id_pk."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_pk[$l] . '"'  );
                 
                }
                else if ($device_key_type_pk === 'call return'){
		$user_id = device_value_user($device_key_value_pk, $account_db, $conn);
                $sql_lines_pk[$l] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_return ."' , device_key_line='".$device_key_line_pk."', device_key_value='".$user_id."', device_key_label='".$device_key_label_pk."' WHERE device_uuid='".$device_uuid."' and  device_key_category='programmable' and device_key_type='". $none ."' and device_key_id='".$device_key_id_pk."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_pk[$l] . '"'  );
                 
                }
                else if ($device_key_type_pk === 'presence'){
		$user_id = device_value_user($device_key_value_pk, $account_db, $conn);
                $sql_lines_pk[$l] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$presence."' , device_key_line='".$device_key_line_pk."', device_key_value='".$user_id."', device_key_label='".$device_key_label_pk."' WHERE device_uuid='".$device_uuid."' and  device_key_category='programmable' and device_key_type='". $none ."' and device_key_id='".$device_key_id_pk."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_pk[$l] . '"'  );
                 
                }
                else if($device_key_type_pk === "speed dial"){
		$user_id = device_value_user($device_key_value_pk, $account_db, $conn);
                $sql_lines_pk[$l] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$speed_dial."' , device_key_line='".$device_key_line_pk."', device_key_value='".$device_key_value_pk."', device_key_label='".$device_key_label_pk."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='programmable' and device_key_type='".$none."' and device_key_id='".$device_key_id_pk."' ;"; 
//                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_ck, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_pk[$l] . '"'  );

                
                } else if($device_key_type_pk === "line"){
		$user_id = device_value_user($device_key_value_pk, $account_db, $conn);
                $sql_lines_pk[$l] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$lineline."' , device_key_line='".$device_key_line_pk."', device_key_value='' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_pk."' ;"; 

                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_pk[$l] . '"'  );

                
                }
                
                }

		} else {
			echo "No action or event from webhook performed";
		
		}
