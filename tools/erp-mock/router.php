<?php

declare(strict_types=1);

// Run only in local Docker development. Never publish this mock API publicly.
$expectedToken = getenv('ERP_MOCK_TOKEN') ?: 'local-mock-token';
$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
header('Content-Type: application/json; charset=utf-8');

if (!hash_equals('Bearer ' . $expectedToken, $authorization)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized'], JSON_THROW_ON_ERROR);
    return;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$status = 200;

$data = match ($path) {
    '/health' => ['status' => 'ok', 'provider' => 'techparts-mock'],
    '/api/suppliers' => ['items' => [
        ['supplierCode' => 'BOSCH', 'externalProductId' => '4711'],
        ['supplierCode' => 'VALEO', 'externalProductId' => '8989'],
    ]],
    '/api/stock' => ['items' => [
        ['source' => 'ACME_ERP', 'eventId' => 'mock-evt-1', 'availableQuantity' => 25, 'safetyStock' => 5],
    ]],
    '/api/simulate/error' => (function () use (&$status): array {
        $status = 503;
        return ['error' => 'Mock ERP temporarily unavailable'];
    })(),
    default => (function () use (&$status): array {
        $status = 404;
        return ['error' => 'Endpoint not found'];
    })(),
};

http_response_code($status);
echo json_encode($data, JSON_THROW_ON_ERROR);
