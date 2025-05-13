<?php


$json = json_decode(file_get_contents("php://input"),true);


file_put_contents("/var/www/html/webhook-data.log",print_r($json,true), FILE_APPEND);


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
/*
$couch_user = '';
$couch_pass = '';
$couch_host = '';
$couch_port = '';
*/
$couch_user = '';
$couch_pass = '';
$couch_host = '';
$couch_port = '15984';

$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port ;
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


$user = 'fusionpbx';
$password = '';
$host =':5432';
$database ='fusionpbx';

$account_couchdb_id = $account_id;

$account_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $account_couchdb_id);

function new_uuid(){


$new_uuid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));

return $new_uuid ;

}

$dbconn = "postgres://" . $user . ":" . $password . "@" . $host . "/" . $database . "?sslmode=require" ;


$prov_url = 'https://' . str_replace('sip','prov',$request_data_account['realm']) . '/app/provision';
$prov_domain =  str_replace('sip','prov',$request_data_account['realm']) ;


        $sql_settings_prov_enable = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'enabled', 'boolean',true, 0, true, 'added from webhook');";
        $sql_settings_httpauth_enable = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'http_auth_enabled', 'boolean',true, 0, true, 'added from webhook');";
        $sql_settings_httpauth_username = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'http_auth_username', 'text','". $account_id ."', 0, true, 'added from webhook');";
        $sql_settings_httpauth_password = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'http_auth_password', 'array','". new_uuid() ."', 0, true, 'added from webhook');";
        $sql_settings_gs_url_path = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'grandstream_config_server_path', 'text','". $prov_url ."', 0, true, 'added from webhook');";
         $sql_settings_yealink_provision_url = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'yealink_provision_url', 'text','". $prov_url ."', 0, true, 'added from webhook');";
        $sql_settings_yealink_trust_ctrl = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'yealink_trust_ctrl', 'text','0', 0, true, 'added from webhook');"; 
        $sql_settings_yealink_trust_certs = "INSERT INTO public.v_domain_settings (domain_uuid, domain_setting_uuid, domain_setting_category, domain_setting_subcategory, domain_setting_name, domain_setting_value, domain_setting_order, domain_setting_enabled, domain_setting_description) VALUES('". $account_uuid ."','". new_uuid() ."','provision', 'yealink_trust_certificates', 'text','0', 0, true, 'added from webhook');";
	$sql_settings_remove = "DELETE FROM public.v_domain_settings WHERE domain_uuid='". $account_uuid  ."';";





	if ($json['action'] === 'doc_created' && $json['type'] === 'account'){
//	$sql = "INSERT INTO public.v_domains (domain_uuid, domain_parent_uuid, domain_name, domain_enabled, domain_description) VALUES(" . "'" . trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "'" .   ', null ,' . "'" . $request_data_account['realm'] . "'" . ',true,' . "'" .  $request_data_account['name'] . "'" . ");";
	$sql = "INSERT INTO public.v_domains (domain_uuid, domain_name, domain_enabled, domain_description) VALUES('". $account_uuid ."', '" .  $request_data_account['realm'] .  "', true , '". $request_data_account['name'] ."');";
	file_put_contents("/var/www/html/webhook-data.log",$sql, FILE_APPEND);
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_prov_enable . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_enable . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_username . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_password . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_url_path . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_provision_url . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_yealink_trust_ctrl . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_yealink_trust_certs . '"'  );



	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );

	} else if ($json['action'] === 'doc_edited'&& $json['type'] === 'account'){
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);
	$sql_ins = "INSERT INTO public.v_domains (domain_uuid, domain_name, domain_enabled, domain_description) VALUES('". $account_uuid ."', '" .  $request_data_account['realm'] .  "', true , '". $request_data_account['name'] ."');";
	$sql = "UPDATE public.v_domains SET domain_name='" .$request_data_account['realm']. "', domain_description='". $request_data_account['name'] ."' WHERE domain_uuid='" . $account_uuid .   "';"; 
	file_put_contents("/var/www/html/webhook-data.log",$sql, FILE_APPEND);
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_ins . '"'  );


        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_remove . '"'  );
	
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_prov_enable . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_enable . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_username . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_httpauth_password . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_gs_url_path . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_settings_yealink_provision_url . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_yealink_trust_ctrl . '"'  );
        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_yealink_trust_certs . '"'  );



        shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );



        } else if ($json['action'] === 'doc_deleted' && $json['type'] === 'account'){
	$sql = "DELETE from public.v_domains WHERE domain_name='" .  $request_data_account['realm'] . "';"; 
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
	} else {
			echo "No action or event from webhook performed";
		}


$device_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $device_id);
$account_couch_id = $request_data_device['pvt_account_id'];
$account_couch_uuid = "(SELECT domain_uuid FROM public.v_domains WHERE domain_name='". $request_data_account['realm'] ."')";
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
	$sql = "INSERT INTO public.v_devices (device_uuid, domain_uuid, device_address, device_label, device_vendor, device_model, device_enabled, device_template, device_username, device_password, device_description) VALUES('" . $device_uuid . "'," . $account_couch_uuid . ",'" . $mac_address  . "','" . $request_data_device['name'] . "','" . $request_data_device['provision']['endpoint_brand'] . "','" . $request_data_device['provision']['endpoint_model'] . "', true ,'" . $request_data_device['provision']['endpoint_brand'] . "/" . $request_data_device['provision']['endpoint_model'] . "','" . $request_data_device['sip']['username'] .  "','"  . $request_data_device['sip']['password'] . "','" . $request_data_device['name'] . "');";
	 	$sql_line= "INSERT INTO public.v_device_lines (domain_uuid, device_line_uuid, device_uuid, line_number, display_name, user_id, auth_id,password, sip_port, sip_transport, register_expires, enabled) VALUES(" . $account_couch_uuid . ",'". trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "','" . $device_uuid .  "',1,'" . $request_data_device['name'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['password'] . "',5060, 'udp', 300,  true);";
	 	$sql_line_domain= "UPDATE public.v_device_lines set server_address = (SELECT domain_name FROM public.v_domains WHERE domain_uuid=" . $account_couch_uuid ." ) WHERE domain_uuid=". $account_couch_uuid  ." AND device_uuid='". $device_uuid  ."';";
	file_put_contents("/var/www/html/webhook-data.log",$sql, FILE_APPEND);
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );
	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line_domain . '"'  );

	} if ($json['action'] === 'doc_deleted' && $json['type'] === 'device'){
	$sql = "DELETE FROM public.v_devices WHERE device_uuid ='" . $device_uuid  . "';"; 

	shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );

	} else if  ($json['action'] === 'doc_edited' && $json['type'] === 'device'){
		$sql_ins = "INSERT INTO public.v_devices (device_uuid, domain_uuid, device_address, device_label, device_vendor, device_model, device_enabled, device_template, device_username, device_password) VALUES('". $device_uuid ."'," . $account_couch_uuid . ",'".$mac_address."', '".$request_data_device['name'] ."', '". $request_data_device['provision']['endpoint_brand']  ."','". $request_data_device['provision']['endpoint_model'] ."', true ,'". $request_data_device['provision']['endpoint_brand'] . '/' . $request_data_device['provision']['endpoint_model'] . "', '". $request_data_device['sip']['username'] ."', '" . $request_data_device['sip']['password'] . "') ;";
	       // $sql = "UPDATE public.v_devices SET domain_uuid=".$account_couch_uuid.", device_address='".$mac_address."', device_label='".$request_data_device['name']."', device_vendor='". $request_data_device['provision']['endpoint_brand'] ."', device_model='".$request_data_device['provision']['endpoint_model']."', device_enabled=true, device_template='".$request_data_device['provision']['endpoint_brand'] . "/" . $request_data_device['provision']['endpoint_model']  ."', device_username='".$request_data_device['sip']['username']."', device_password='".$request_data_device['sip']['password']."',server_address=(SELECT domian_name FROM public.v_domains WHERE domain_uuid='" . $account_couch_uuid . "')  WHERE device_uuid='".$device_uuid."';";
	 	
//                $sql_line_domain= "UPDATE public.v_device_lines set server_address = (SELECT domain_name FROM public.v_domains WHERE domain_uuid=" . $account_couch_uuid ." ) WHERE domain_uuid=". $account_couch_uuid  ." AND device_uuid='". $device_uuid  ."';";
//		$sql_lines_del= "DELETE FROM public.v_device_keys WHERE device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='". $request_data_device['mac_address']."') ;" ;
                $sql_line= "INSERT INTO public.v_device_lines (domain_uuid, device_line_uuid, device_uuid, line_number, label, display_name, user_id, auth_id,password, sip_port, sip_transport, register_expires, enabled, server_address) VALUES(" . $account_couch_uuid . ",'". trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "','" . $device_uuid .  "','1','" . $request_data_device['name'] . "','" . $request_data_device['name'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['password'] . "',5060, 'udp', 300,  true, (SELECT domain_name FROM public.v_domains WHERE domain_uuid=" . $account_couch_uuid ." ));";
		
                $sql_lines_del= "DELETE FROM public.v_device_lines WHERE device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='". $request_data_device['mac_address']."') ;" ;

                $sql_lines_ck_del= "DELETE FROM public.v_device_keys WHERE device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='". $request_data_device['mac_address']."') ;" ;

	//file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);

		
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_ins . '"'  );
                
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_del . '"'  );
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );
//                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
//                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line_domain . '"'  );
		shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck_del . '"'  );
//		shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line_domain . '"'  );
//		shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );

		
//		for ($h = 0 ; $h < $countck ; $h++){ 
//
////		$device_key_type =  trim(str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type'])) ?? $none ;
////		$device_key_type =  $none ;
//		$device_key_type =  trim(str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$h]]['type']))  ;
//                $device_key_value = trim($request_data_device['provision']['combo_keys'][$alllinesck[$h]]['value']['value']);
//                $device_key_label = trim($request_data_device['provision']['combo_keys'][$alllinesck[$h]]['value']['label']);
//                $device_key_line = '0';
//                $device_key_id= $alllinesck[$h] +1   ;
//		$device_key_id_none_ck = $key_none_ck[$h]  ;
//		
//               if($device_key_type === "parking"){
//		$user_id = device_value_user($device_key_value, $account_db);
//                $sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id."' , 'line', '".$request_data_device['provision']['endpoint_brand']."',(select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='monitored call park'), '', '".$device_key_line."', '*3".trim($device_key_value) ."', '', '".$device_key_label."');";
////                file_put_contents("/var/www/html/webhook-data.log",$sql_lines_placeholder_ck[$i], FILE_APPEND);
//		  shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
//	       }
//		else if($device_key_type === "personal parking"){
//		$user_id = device_value_user($device_key_value, $account_db);
//                $sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id."' , 'line', '".$request_data_device['provision']['endpoint_brand']."',(select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='monitored call park'), '', '".$device_key_line."', '*3".trim($user_id) ."', '', '".$device_key_label."');";
//		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id."', device_key_category='line', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='monitored call park' , device_key_line='".$device_key_line."', device_key_value='*3".trim($user_id)."', device_key_label='".$device_key_label."' WHERE device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='". $request_data_device['mac_address']."') AND device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."') ;";
////                file_put_contents("/var/www/html/webhook-data.log",$sql_lines_placeholder_ck[$i], FILE_APPEND);
//		  shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
//		}
//		else if($device_key_type === "line"){
//		$sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id."' , 'line', '".$request_data_device['provision']['endpoint_brand']."',(select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='line') , '', '".$device_key_line."', '', '', '".$device_key_label."');";
//		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id."', device_key_category='line', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='monitored call park' , device_key_line='".$device_key_line."', device_key_value='*3".$device_key_value."', device_key_label='".$device_key_label."' WHERE device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='". $request_data_device['mac_address']."') AND device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."') ;";
////		file_put_contents("/var/www/html/webhook-data.log",$sql_lines_placeholder_ck[$i], FILE_APPEND);
//                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
//            	}
//		else if($device_key_type === "speed dial"){
//		$sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id."' , 'line', '".$request_data_device['provision']['endpoint_brand']."',(select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='speed_dial'), '', '".$device_key_line."', '".$device_key_value."', '', '".$device_key_label."');";
//		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id."', device_key_category='line', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='speed dial' , device_key_line='".$device_key_line."', device_key_value='".$device_key_value."', device_key_label='".$device_key_label."' WHERE device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='". $request_data_device['mac_address']."') AND device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."') ;";
////	file_put_contents("/var/www/html/webhook-data.log",$sql_lines_placeholder_ck[$i], FILE_APPEND);
//		  shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
//		}
//
//		else if($device_key_type === "presence"){
//		$user_id = device_value_user($device_key_value, $account_db);
//		$sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='blf') , '', '".$device_key_line."', '".trim($user_id) ."', '', '".$device_key_label."');";
//		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id."', device_key_category='line', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='speed dial' , device_key_line='".$device_key_line."', device_key_value='".trim($user_id)."', device_key_label='".$device_key_label."' WHERE device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='". $request_data_device['mac_address']."') AND device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."') ;";
////	file_put_contents("/var/www/html/webhook-data.log","\n". $user_id, FILE_APPEND);
//		  shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
//		}
//		else if($device_key_type === "none") {
//		$user_id = device_value_user($device_key_value, $account_db);
//                $sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_none_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line."', '', '', '');";
////		$sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_none_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line."', '', '', '');";
////		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_none_ck."', device_key_category='line', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='speed dial' , device_key_line='".$device_key_line."', device_key_value='".trim($user_id)."', device_key_label='".$device_key_label."' WHERE device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='". $request_data_device['mac_address']."') AND device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."') ;";
////	file_put_contents("/var/www/html/webhook-data.log","\n". $user_id, FILE_APPEND);
//file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_placeholder_ck,true), FILE_APPEND);
//		  shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
//		}
//		}
                $key_none_ck = range(0,($countck - 1));
		$sel_query = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='none';";
                $sel_query_call_park = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='monitored call park';";
                $sel_query_presence = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='blf';";
                $sel_query_speed_dial = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='speed_dial';";
                $sel_query_line = "SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(SELECT device_vendor_uuid from v_device_vendors where name='".$request_data_device['provision']['endpoint_brand']."') and type='line';";

                $none = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query . '"'  ));
                $call_park = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_call_park . '"'  ));
                $presence = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_presence . '"'  ));
                $speed_dial = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_speed_dial . '"'  ));
                $lineline = trim(shell_exec("sudo psql -qtAX -d " . '"' . $dbconn . '" -c ' . '"' . $sel_query_line . '"'  ));
                for ($h = 0 ; $h < $countfk; $h++){
//                $device_key_type_ck = str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type']) ; 
//                 if(empty($device_key_type_ck)){
//                     $device_key_type_ck = $none;
//                 } else {
//                     $device_key_type_ck = trim(str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type'])) ; 
//
//                 }
                // 

                $device_key_value_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['value'])  ; 
                
                $device_key_label_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['label']);
		 
                $device_key_line_ck = '0';
		
		$device_key_id_ck = $alllinesck[$i] +1;
		$device_key_id_none_ck = $key_none_ck[$i];    
                    
                $sql_lines_placeholder_ck[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$h."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', '".$none ."', '','".$device_key_line_ck."', '', '', '');";
		$sql_lines_placeholder_fk[$h] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$h."' , 'memory', '".$request_data_device['provision']['endpoint_brand']."', '".$none ."', '','".$device_key_line_ck."', '', '', '');";
        //	$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='monitored call park' , device_key_line='".$device_key_line_ck."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='".$device_uuid."' and device_key_type='none' and  device_key_category='line' and device_key_id='".$device_key_id_ck."' ;"; 
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$h] . '"'  );
                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_fk[$h] . '"'  );
                    
                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_placeholder_ck,true), FILE_APPEND);    
                    
                }
                
                for($i = 0 ; $i < $countck ; $i++ ){
//                $device_key_type_fk =  str_replace('_',' ',$request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['type']) ;
//                $device_key_value_fk = trim($request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['value']) ?? null;
		
//                $device_key_type_fk =  str_replace('_',' ',$request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['type'])  ;
//                $device_key_type_ck = str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type']); 
//                 $device_key_type_ck = str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type']); 
                
                 $device_key_type_ck = str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type']) ; 
//                 if(empty($device_key_type_ck)){
//                     $device_key_type_ck = $none;
//                 } else {
//                     $device_key_type_ck = trim(str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type'])) ; 
//
//                 }
                

                $device_key_value_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['value'])  ; 
                
                $device_key_label_ck = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['label']);
		 
                $device_key_line_ck = '0';
		
		$device_key_id_ck = $alllinesck[$i] +1;
		$device_key_id_none_ck = $key_none_ck[$i];
                
 file_put_contents("/var/www/html/webhook-data.log",$device_key_type_ck, FILE_APPEND);
                if($device_key_type_ck === "personal parking"){
		$user_id = device_value_user($device_key_value_ck, $account_db);
		$sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";
		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_park."' , device_key_line='".$device_key_line_ck."', device_key_value='*3".$user_id."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
                //$cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;
                 shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                } 
                else if($device_key_type_ck === "parking"){	
		$user_id = device_value_user($device_key_value_ck, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_ck."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='". $call_park ."' , device_key_line='".$device_key_line_ck."', device_key_value='*3".$device_key_value_ck."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
                // $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                }
                else if ($device_key_type_ck === 'presence'){
		$user_id = device_value_user($device_key_value_ck, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$presence."' , device_key_line='".$device_key_line_ck."', device_key_value='".$user_id."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."' and  device_key_category='line' and device_key_type='". $none ."' and device_key_id='".$device_key_id_ck."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );
                 
                }
                else if($device_key_type_ck === "speed dial"){
		$user_id = device_value_user($device_key_value_ck, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$speed_dial."' , device_key_line='".$device_key_line_ck."', device_key_value='".$device_key_value_ck."', device_key_label='".$device_key_label_ck."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_ck, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );

                
                } else if($device_key_type_ck === "line"){
		$user_id = device_value_user($device_key_value_ck, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$lineline."' , device_key_line='".$device_key_line_ck."', device_key_value='' WHERE device_uuid='".$device_uuid."'  and  device_key_category='line' and device_key_type='".$none."' and device_key_id='".$device_key_id_ck."' ;"; 
                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_ck, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_ck[$i] . '"'  );

                
                }
//                else {
//
//		$user_id = device_value_user($device_key_value_ck, $account_db);
//                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_none_ck."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_ck."', '', '', '');";
////////		$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_none_fk[$j]."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$request_data_user['presence_id']."' WHERE device_uuid='". $device_uuid. "';";  
//                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$i] . '"'  );
////                break ;
//		}
               
                }
                
                
		$key_none_fk = range(0,($countfk - 1));
                
                
		
		
	        for ($j = 0 ; $j < $countfk  ; $j++){ 
//                $device_key_type_fk =  str_replace('_',' ',$request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['type']) ;
//                $device_key_value_fk = trim($request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['value']) ?? null;
		
//                $device_key_type_fk =  str_replace('_',' ',$request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['type'])  ;
		
                $device_key_type_fk = str_replace('_',' ',$request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['type']) ?? 'none' ;

                $device_key_value_fk = trim($request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['value'])  ; 
		 
                $device_key_line_fk = '0';
		
		$device_key_id_fk = $alllinesfk[$j] +1;
		$device_key_id_none_fk = $key_none_fk[$j];

                  if($device_key_type_fk === "personal parking"){
		$user_id = device_value_user($device_key_value_fk, $account_db);
		$sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";
		$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$call_park."' , device_key_line='".$device_key_line_fk."', device_key_value='*3".$user_id."'  WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
                //$cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;
                 shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                } 
                else if($device_key_type_fk === "parking"){	
		$user_id = device_value_user($device_key_value_fk, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_ck."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='". $call_park ."' , device_key_line='".$device_key_line_fk."', device_key_value='*3".$device_key_value_fk."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
                // $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_fk[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                }
                else if ($device_key_type_fk === 'presence'){
		$user_id = device_value_user($device_key_value_fk, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_ck."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$presence."' , device_key_line='".$device_key_line_fk."', device_key_value='".$user_id."' WHERE device_uuid='".$device_uuid."' and  device_key_category='memory' and device_key_type='". $none ."' and device_key_id='".$device_key_id_fk."' ;"; 
                //                 $cmd = "sudo psql -d   $dbconn  << EOF \n " .  $sql_lines_ck[$i]  . " \n" . 'EOF' . "\n" ;

                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );
                 
                }
                else if($device_key_type_fk === "speed dial"){
		$user_id = device_value_user($device_key_value_fk, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type='none'), '', '".$device_key_line_fk."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$speed_dial."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_fk, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );

                
                } else if($device_key_type_fk === "line"){
		$user_id = device_value_user($device_key_value_fk, $account_db);
                $sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES(".$account_couch_uuid.", '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', (select value from public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from public.v_device_vendors where name='". $request_data_device['provision']['endpoint_brand'] ."') and type=(SELECT value FROM public.v_device_vendor_functions where device_vendor_uuid=(select device_vendor_uuid from v_device_vendors where name='yealink') and type='none')) , '', '".$device_key_line_ck."', '', '', '');";		//$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";  
                $sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid=".$account_couch_uuid.", device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$lineline."' , device_key_line='".$device_key_line_fk."', device_key_value='' WHERE device_uuid='".$device_uuid."'  and  device_key_category='memory' and device_key_type='".$none."' and device_key_id='".$device_key_id_fk."' ;"; 
                file_put_contents("/var/www/html/webhook-data.log",print_r($sql_lines_fk, FILE_APPEND));
                                shell_exec("sudo psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_fk[$j] . '"'  );

                
                }
               
                 else {
                    break ;
                }
                }
               


		} else {
			echo "No action or event from webhook performed";
		
		}
