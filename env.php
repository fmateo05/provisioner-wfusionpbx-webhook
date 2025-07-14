<?php

$couch_user = '';
$couch_pass = '';
$couch_host = '';
$couch_port = '15984';

$user = 'fusionpbx';
$password = '';
$host =':5432';
$database ='fusionpbx';


$credentials = trim('MD5-Credentials');
$otf_couch_host = 'kazoo-api-host';
$otf_couch_port = '8000';


$otf_conn = "http://" . $otf_couch_host . ':' . $otf_couch_port  . '/v2/';
$conn = "http://" . $couch_user . ':' . $couch_pass . '@' . $couch_host . ':' . $couch_port ;



?>
