<?php declare(strict_types=1);

/**
 * Tiny local ERP simulator. Start from the repository root with:
 * php -S 0.0.0.0:8080 custom/plugins/EnterpriseOrderIntegration/tools/erp-simulator/router.php
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$expectedToken = (string) (getenv('ERP_SIMULATOR_TOKEN') ?: '');
$authorization = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
if ($expectedToken !== '' && !hash_equals('Bearer ' . $expectedToken, $authorization)) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    return;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) !== '/api/orders') {
    http_response_code(404);
    echo json_encode(['error' => 'not_found', 'expected' => 'POST /api/orders']);
    return;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload) || !isset($payload['orderId'], $payload['orderNumber'], $payload['lineItems']) || !is_array($payload['lineItems'])) {
    http_response_code(422);
    echo json_encode(['error' => 'invalid_payload']);
    return;
}

$scenario = (string) (getenv('ERP_SIMULATOR_SCENARIO') ?: ($_GET['scenario'] ?? 'success'));
if ($scenario === 'timeout') {
    sleep(15);
}
if ($scenario === 'unavailable') {
    http_response_code(503);
    echo json_encode(['error' => 'erp_temporarily_unavailable']);
    return;
}
if ($scenario === 'unauthorized') {
    http_response_code(401);
    echo json_encode(['error' => 'invalid_credentials']);
    return;
}
if ($scenario === 'invalid') {
    http_response_code(422);
    echo json_encode(['error' => 'business_validation_failed']);
    return;
}

$reference = 'ERP-DEMO-' . strtoupper(substr(hash('sha256', (string) $payload['orderId']), 0, 12));
error_log(json_encode([
    'event' => 'simulator_order_accepted',
    'orderId' => $payload['orderId'],
    'orderNumber' => $payload['orderNumber'],
    'reference' => $reference,
], JSON_UNESCAPED_SLASHES));

http_response_code(201);
echo json_encode(['status' => 'accepted', 'reference' => $reference], JSON_UNESCAPED_SLASHES);