<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$shop = $_GET['shop'];
$code = $_GET['code'];
$hmac = $_GET['hmac'];

$apiKey = getenv('SHOPIFY_API_KEY');
$secret = getenv('SHOPIFY_API_SECRET');
file_put_contents('check_logs.json', "SHOP". $shop  , FILE_APPEND);
// ✅ HMAC validation (basic security)
$calculatedHmac = hash_hmac('sha256', http_build_query(array_diff_key($_GET, ['hmac' => ''])), $secret);
file_put_contents('check_logs.json', "STEP2**HMAC". print_r($calculatedHmac,1)  , FILE_APPEND);
if (!hash_equals($hmac, $calculatedHmac)) {
    die("Invalid HMAC");
}

// ✅ Exchange code for access token
$response = file_get_contents("https://{$shop}/admin/oauth/access_token", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-type: application/json",
        'content' => json_encode([
            'client_id' => $apiKey,
            'client_secret' => $secret,
            'code' => $code,
        ]),
    ]
]));

$data = json_decode($response, true);
$accessToken = $data['access_token'];
file_put_contents('check_logs.json', "STEP3**". print_r($accessToken ,1)  , FILE_APPEND);
// ✅ Register GraphQL webhook for ORDER CREATE
$graphqlQuery = <<<GRAPHQL
mutation webhookSubscriptionCreate {
  webhookSubscriptionCreate(topic: ORDERS_CREATE, webhookSubscription: {
    callbackUrl: "https://codelocksolutions.com/order-create.php",
    format: JSON
  }) {
    userErrors {
      field
      message
    }
    webhookSubscription {
      id
    }
  }
}
GRAPHQL;

$ch = curl_init("https://{$shop}/admin/api/2023-10/graphql.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $graphqlQuery]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-Shopify-Access-Token: {$accessToken}"
]);

$result = curl_exec($ch);
file_put_contents('check_logs.json', "STEP4**". print_r($result,1)  , FILE_APPEND);
curl_close($ch);

