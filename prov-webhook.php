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
$couch_port = '';
$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port ;
$device = $device_id;

$command_dev = "curl -s ". $conn . '/'  . $account_db . '/' . $device . '| python3 -mjson.tool' ;
$document = shell_exec($command_dev);

$result_dev = json_decode($document,true);
  

file_put_contents('/var/www/html/webhook-data.log',print_r($result_dev));	


$account = $account_id;


$command_acc = "curl -s ". $conn . '/'  . $account_db . '/' . $account . '| python3 -mjson.tool' ;
$document_acc = shell_exec($command_acc);

$result_acc = json_decode($document_acc,true);


$request_data_account  = $result_acc;
$request_data_device  = $result_dev;



$user = 'fusionpbx';
$password = '';
$host ='';
$database ='';
$account_couchdb_id = $account_id;

$account_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $account_couchdb_id);

$dbconn = 'postgres://' . $user . ':' . $password . '@' . $host . '/' . $database  ;

	if ($json['action'] === 'doc_created' && $json['type'] === 'account'){
	$sql = "INSERT INTO public.v_domains (domain_uuid, domain_parent_uuid, domain_name, domain_enabled, domain_description) VALUES(" . "'" . trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "'" .   ', null ,' . "'" . $request_data_account['realm'] . "'" . ',true,' . "'" .  $request_data_account['name'] . "'" . ");";
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);
	shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );

	} else if ($json['action'] === 'doc_edited'&& $json['type'] === 'account'){
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);
	$sql = "UPDATE public.v_domains SET domain_name='" .$request_data_account['realm']. "', domain_description='". $request_data_account['name'] ."' WHERE domain_uuid='" . $account_uuid .   "';"; 
        shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
		} else {
			echo "No action or event from webhook performed";
		}


$device_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $device_id);
$account_couch_id = $request_data_device['pvt_account_id'];
$account_couch_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $account_couch_id);
$mac_address = $request_data_device['mac_address'];
$alllinesck = array_values(array_keys($request_data_device['provision']['combo_keys'])) ;
$alllinesfk = array_values(array_keys($request_data_device['provision']['feature_keys'])) ;



	if ($json['action'] === 'doc_created' && $json['type'] === 'device'){
	$sql = "INSERT INTO public.v_devices (device_uuid, domain_uuid, device_address, device_label, device_vendor, device_model, device_enabled, device_template, device_username, device_password, device_description) VALUES('" . $device_uuid . "','" . $account_couch_uuid . "','" . $mac_address  . "','" . $request_data_device['name'] . "','" . $request_data_device['provision']['endpoint_brand'] . "','" . $request_data_device['provision']['endpoint_model'] . "', true ,'" . $request_data_device['provision']['endpoint_brand'] . "/" . $request_data_device['provision']['endpoint_model'] . "','" . $request_data_device['sip']['username'] .  "','"  . $request_data_device['sip']['password'] . "','" . $request_data_device['name'] . "');";
	 	$sql_line= "INSERT INTO public.v_device_lines (domain_uuid, device_line_uuid, device_uuid, line_number, display_name, user_id, auth_id,password, sip_port, sip_transport, register_expires, enabled) VALUES('" . $account_couch_uuid . "','". trim(file_get_contents('/proc/sys/kernel/random/uuid')) . "','" . $device_id .  "',1,'" . $request_data_device['name'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['username'] . "','" . $request_data_device['sip']['password'] . "',5060, 'udp', 120 ,  true);";
	 	$sql_line_domain= "UPDATE public.v_device_lines set server_address = (SELECT domain_name FROM public.v_domains WHERE domain_uuid='" . $account_couch_uuid ."' ) WHERE domain_uuid='". $account_couch_uuid  ."' AND device_uuid='". $device_uuid  ."';";
	file_put_contents("/var/www/html/webhook-data.log",$sql, FILE_APPEND);
	shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
	shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line . '"'  );
	shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line_domain . '"'  );

	} else if  ($json['action'] === 'doc_edited' && $json['type'] === 'device'){
	        $sql = "UPDATE public.v_devices SET domain_uuid='".$account_couch_uuid."', device_address='".$mac_address."', device_label='".$request_data_device['name']."', device_vendor='". $request_data_device['provision']['endpoint_brand'] ."', device_model='".$request_data_device['provision']['endpoint_model']."', device_enabled=true, device_template='".$request_data_device['provision']['endpoint_brand'] . "/" . $request_data_device['provision']['endpoint_model']  ."', device_username='".$request_data_device['sip']['username']."', device_password='".$request_data_device['sip']['password']."'  WHERE device_uuid='".$device_uuid."';";
	 	$sql_line_domain= "UPDATE public.v_device_lines set server_address = (SELECT domain_name FROM public.v_domains WHERE domain_uuid='" . $account_couch_uuid ."' ) WHERE domain_uuid='". $account_couch_uuid  ."' AND device_uuid='". $device_uuid  ."';";
	file_put_contents("/var/www/html/webhook-data.log",print_r($sql,true), FILE_APPEND);

		
                shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql . '"'  );
		shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_line_domain . '"'  );

		for ($i = 0 ; $i < count($alllinesck) ; $i++){ 

                $device_key_type =  str_replace('_',' ',$request_data_device['provision']['combo_keys'][$alllinesck[$i]]['type']);
                $device_key_value = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['value']);
                $device_key_label = trim($request_data_device['provision']['combo_keys'][$alllinesck[$i]]['value']['label']);
                $device_key_line = '0';
                $device_key_id= $alllinesck[$i] ;
		
		$sql_lines_placeholder_ck[$i] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id."' , 'line', '".$request_data_device['provision']['endpoint_brand']."', '".$device_key_type."', '', '".$device_key_line."', '".$device_key_value."', '', '".$device_key_label."');";
		$sql_lines_ck[$i] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id."', device_key_category='line', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type."' , device_key_line='".$device_key_line."', device_key_value='".$device_key_value."', device_key_label='".$device_key_label."' WHERE device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='". $request_data_device['mac_address']."') AND device_uuid=(SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."') ;";

		
		  shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_ck[$i] . '"'  );
           
		 
		}
	        for ($j = 0 ; $j < count($alllinesfk) ; $j++){ 
                $device_key_type_fk =  str_replace('_',' ',$request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['type']);
                $device_key_value_fk = trim($request_data_device['provision']['feature_keys'][$alllinesfk[$j]]['value']);

                $device_key_line_fk = '0';
                $device_key_id_fk= $alllinesfk[$j] ;
		$sql_lines_placeholder_fk[$j] = "INSERT INTO public.v_device_keys (domain_uuid, device_key_uuid, device_uuid, device_key_id, device_key_category, device_key_vendor, device_key_type, device_key_subtype, device_key_line, device_key_value, device_key_extension,  device_key_label) VALUES('".$account_uuid."', '".trim(file_get_contents('/proc/sys/kernel/random/uuid'))."', (SELECT device_uuid FROM public.v_devices WHERE device_address='".$request_data_device['mac_address']."'),'".$device_key_id_fk."' , 'memory', '".$request_data_device['provision']['endpoint_brand']."', '".$device_key_type_fk."', '', '".$device_key_line_fk."', '".$device_key_value_fk."', '', '');";
		$sql_lines_fk[$j] = "UPDATE public.v_device_keys SET domain_uuid='".$account_uuid."', device_uuid=(SELECT device_uuid from public.v_devices WHERE device_address='".$request_data_device['mac_address']."'), device_key_id='".$device_key_id_fk."', device_key_category='memory', device_key_vendor='".$request_data_device['provision']['endpoint_brand']."', device_key_type='".$device_key_type_fk."' , device_key_line='".$device_key_line_fk."', device_key_value='".$device_key_value_fk."' WHERE device_uuid='". $device_uuid. "';";

		
                
                shell_exec("psql -d " . '"' . $dbconn . '" -c ' . '"' . $sql_lines_placeholder_fk[$j] . '"'  );
              
		}
               


		} else {
			echo "No action or event from webhook performed";
		}


