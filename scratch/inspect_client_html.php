<?php
$ch = curl_init('https://client.pay.bka.sh');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

echo "Error: " . $err . "\n";
echo "Length: " . strlen($res) . "\n";
echo substr($res, 0, 1000) . "\n";
