<?php
require 'vendor/autoload.php';

$shop = $_GET['shop'] ?? '';
$apiKey = "9a5adb07279a98af27fd73da38dabafe";
$scopes = "read_checkouts,read_customers,read_discounts,read_draft_orders,read_fulfillments,read_inventory,read_orders,read_products,write_checkouts,write_customers,write_draft_orders,write_fulfillments,write_orders,write_products,write_themes";
$redirectUri = "https://codelocksolutions.com/y-test-app-4/callback.php";
/* Prepare app installation link  */
$installUrl = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUri}";
file_put_contents('check_logs.json', "STEP1**".$installUrl , FILE_APPEND);
header("Location: $installUrl");
exit;
