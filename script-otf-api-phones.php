<?php

$brand = shell_exec("ls -d */*/ | cut -d/ -f1");
$family = shell_exec("ls -d */*/ | sed 's/[0-9].*//' | cut -d'/' -f 2");
$model = shell_exec("ls -d */*/ | cut -d/ -f2");

$brand_arr = explode("\n",$brand);
$family_arr = explode("\n",$family);
$model_arr = explode("\n",$model);

//$brand_arr . $family_arr . $model_arr ;
$number = count($family_arr);

$i = 0;
while ($i < $number){
$test1  = "$brand_arr[$i]\n" ;
$test2 ="$brand_arr[$i]" . '/' . "$family_arr[$i]\n";
$test3 ="$brand_arr[$i]" . '/' . "$family_arr[$i]" . '/' ."$model_arr[$i]\n";

$iterate='"template": { "feature_keys": { "iterate": "15" }, "combo_keys": { "iterate": 30, "text": "Lines" } }, "settings": {}';

$parse = "curl -i -H 'Accept: application/json' -H 'Content-Type: application/json' -X PUT -d '{  ".'"settings"'." : {}}' " . "http://127.0.0.1/api/phones/" . $test1 .  "\n";
$parse .= "curl -i -H 'Accept: application/json' -H 'Content-Type: application/json' -X PUT -d '{   ".'"settings"'." : {}}' " . "http://127.0.0.1/api/phones/" . $test2 . "\n";
$parse .= "curl -i -H 'Accept: application/json' -H 'Content-Type: application/json' -X PUT -d '{   ".'"settings"'." : {}, $iterate }' " . "http://127.0.0.1/api/phones/" . $test3 . "\n";

print_r($parse);

$i++;
}


?>
