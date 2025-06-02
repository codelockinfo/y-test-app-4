<?php
// Log or process the webhook payload
$data = file_get_contents('php://input');
file_put_contents('order_create_log.json', $data, FILE_APPEND);
http_response_code(200);
