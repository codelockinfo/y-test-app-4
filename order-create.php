<?php


// Replace with your app's Shopify access token
$accessToken = 'shpua_3ce3b6454ad2457db7a898c6b4ced64f';
// Replace with your Shopify store domain
$storeUrl = 'yogin-testing-june-1.myshopify.com';

// Get the incoming webhook data
$webhookData = file_get_contents('php://input');
$order = json_decode($webhookData, true);
file_put_contents('order_create_log.json', $order, FILE_APPEND);
http_response_code(200);
// Check if order is valid
if (!$order || !isset($order['id'])) {
    http_response_code(400);
    echo 'Invalid order data';
    exit;
}

$orderId = $order['id'];
$isPreorder = false;

// Loop through line items to find any preorder property
file_put_contents('order_create_log.json', print_r($lineItem,1), FILE_APPEND);
foreach ($order['line_items'] as $lineItem) {
    if (isset($lineItem['properties']) && is_array($lineItem['properties'])) {
        foreach ($lineItem['properties'] as $property) {
            if (
                (isset($property['name']) && strtolower($property['name']) === 'pre-order') &&
                (isset($property['value']) && strtolower($property['value']) === 'yes')
            ) {
                $isPreorder = true;
                break 2;
            }
        }
    }
}

if ($isPreorder) {
    file_put_contents('order_create_log.json', "STEP0 PRE ORDER" , FILE_APPEND);
    // Shopify API endpoint to update the order
    $apiUrl = "https://$storeUrl/admin/api/2024-01/orders/$orderId.json";

    // Set order update data
    $updateData = [
        'order' => [
            'id' => $orderId,
            'tags' => 'pre-order'
        ]
    ];

    // Send PUT request to Shopify
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "X-Shopify-Access-Token: $accessToken"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    file_put_contents('order_create_log.json', "STEP1 UPDATE ORDER DATA".print_r($response,1) , FILE_APPEND);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        http_response_code(200);
        file_put_contents('order_create_log.json', "STEP2 Order updated with pre-order tag".print_r($response,1) , FILE_APPEND);
    } else {
        // Fallback: Update order note_attributes
        $noteData = [
            'order' => [
                'id' => $orderId,
                'note_attributes' => [
                    ['name' => 'Pre-order', 'value' => 'Yes']
                ]
            ]
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($noteData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "X-Shopify-Access-Token: $accessToken"
        ]);

        $response = curl_exec($ch);
        file_put_contents('order_create_log.json', "STEP3 ORDER DATA".print_r($response,1) , FILE_APPEND);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            file_put_contents('order_create_log.json', "STEP4 Order updated with note attribute instead".print_r($response,1) , FILE_APPEND);
            http_response_code(200);
        } else {
            file_put_contents('order_create_log.json', "STEP5 Failed to update order".print_r($response,1), FILE_APPEND);
            http_response_code(500);
        }
    }
} else {
    file_put_contents('order_create_log.json', "STEP0 No pre-order item found", FILE_APPEND);
    http_response_code(200);
}

?>
