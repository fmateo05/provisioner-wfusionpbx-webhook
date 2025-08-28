<?php

define('__ROOT__', dirname(dirname(__FILE__)));

require_once(__ROOT__.'/env.php');

$number = $argv[1];
$account = "d18cae03a0203258af5e30aab6502e15"; // MASTER ACCOUNT
$number_np = str_replace("+", "", $number);

$kamusr = 'kamailio';
$kampwd = 'your_secure_password';
$kamhost = '10.171.91.5';
$kamdb = 'kamailio-five';

$dbconn_kamailio = "postgres://" . $kamusr . ':' . $kampwd . '@' .  $kamhost . '/' . $kamdb   ;

$auth_doc = '{
  "data": {
      "credentials": "'. $credentials .'",
      "account_name": "master"
  }
}';


$cmd_json_auth= 'curl -s -H "Content-Type: application/json" -X PUT ' . $otf_conn . 'user_auth -d ' . "'" . $auth_doc . "'" ;

$json_auth = json_decode(shell_exec($cmd_json_auth),true);

$cmd_json_number_identify= 'curl -s -H "Content-Type: application/json" -H "X-Auth-Token: '. $json_auth['auth_token']. '" -X GET ' . $otf_conn . 'accounts/' . $account . '/phone_numbers/' . $number . '/identify';


$phone_number_identify = json_decode(shell_exec($cmd_json_number_identify),true);

$account_identify = $phone_number_identify['data']['account_id'];

$cmd_json_number_get= 'curl -s -H "Content-Type: application/json" -H "X-Auth-Token: '. $json_auth['auth_token']. '" -X GET ' . $otf_conn . 'accounts/' . $account_identify . '/phone_numbers/' . $number ;

$phone_number_info = shell_exec($cmd_json_number_get);

$all_data = json_decode($phone_number_info,true);

$regextern_data = $all_data['data']['regextern'];

if(!empty($regextern_data)) {
$query_select = "select l_uuid from uacreg where l_uuid='" . $number . "';";
$sel_query = shell_exec("sudo psql -qtAX  -d " . $dbconn_kamailio . " -c " . '"' .   $query_select . '"' );
echo $sel_query ;
	if(!$sel_query && $regextern_data['active'] == true){

$query_ins = "INSERT INTO uacreg (l_uuid,l_username,l_domain,r_username,r_domain,realm,auth_username,auth_password,auth_proxy,expires,reg_delay) VALUES('". $number ."','". $regextern_data['username'] ."','". $regextern_data['proxy'] ."','". $regextern_data['providerusr'] . "','". $regextern_data['proxy'] ."','". $regextern_data['proxy']  ."','".$regextern_data['providerusr'] ."','". $regextern_data['password'] ."','sip:". $regextern_data['proxy'] ."','300','10');";

shell_exec("sudo psql  -d " . $dbconn_kamailio . " -c " . '"' .   $query_ins . '"' );
echo $query_ins ;

	} else if(!$regextern_data['active'] == true ){
		$query_del = "DELETE from uacreg where l_uuid='". $number ."'";
		echo $query_del;
		shell_exec("sudo psql  -d " . $dbconn_kamailio . " -c " . '"' .   $query_del . '"' );
		
		
		
		} else {
		$query_upd  = "UPDATE uacreg set l_username='". $regextern_data['username']  ."', l_domain='". $regextern_data['proxy'] ."' , r_username='". $regextern_data['providerusr']."', r_domain='".$regextern_data['proxy']. "', realm='". $regextern_data['proxy'] ."' , auth_username='". $regextern_data['providerusr']  ."', auth_password='". $regextern_data['password']  ."', auth_proxy='". $regextern_data['proxy'] ."', expires='300', reg_delay='10' WHERE l_uuid='". $number  ."';";
		shell_exec("sudo psql  -d " . $dbconn_kamailio . " -c " . '"' .   $query_upd . '"' );
		  }
	} else {

echo "Registration data  does not exists";

	}
