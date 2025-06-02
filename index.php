<?php
require 'vendor/autoload.php';

$shop = $_GET['shop'] ?? '';
$apiKey = getenv('SHOPIFY_API_KEY');
$scopes = getenv('SHOPIFY_SCOPES');
$redirectUri = getenv('SHOPIFY_REDIRECT_URI');

$installUrl = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUri}";
file_put_contents('check_logs.json', "STEP1**".$installUrl , FILE_APPEND);
header("Location: $installUrl");
exit;
