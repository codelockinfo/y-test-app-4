<?php
require 'vendor/autoload.php';

$shop = $_GET['shop'] ?? '';
$apiKey = "9a5adb07279a98af27fd73da38dabafe";
$scopes = "read_all_orders,read_assigned_fulfillment_orders,read_analytics,read_checkouts,read_customers,read_discounts,read_draft_orders,read_fulfillments,read_gift_cards,read_inventory,read_locales,read_locations,read_marketing_events,read_merchant_managed_fulfillment_orders,read_online_store_pages,read_orders,read_payment_terms,read_price_rules,read_product_listings,read_products,read_translations,write_checkouts,write_customers,write_draft_orders,write_fulfillments,write_gift_cards,write_inventory,write_locales,write_locations,write_orders,write_payment_terms,write_price_rules,write_products,write_shipping,write_themes,write_users";
$redirectUri = "https://codelocksolutions.com/y-test-app-4/callback.php";

$installUrl = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUri}";
file_put_contents('check_logs.json', "STEP1**".$installUrl , FILE_APPEND);
header("Location: $installUrl");
exit;
