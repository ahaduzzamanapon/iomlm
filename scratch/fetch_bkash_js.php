<?php
$js = file_get_contents("https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js");
file_put_contents("e:/My Soft/iom/learning-plus/scratch/bkash_script.js", $js);
echo "Length: " . strlen($js) . "\n";
