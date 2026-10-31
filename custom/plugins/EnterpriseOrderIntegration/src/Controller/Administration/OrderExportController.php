<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Controller\Administration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use EnterpriseOrderIntegration\Application\Message\ExportOrderMessage;
use Shopware\Core\Framework\Routing\Attribute\Acl;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
#[Route(defaults: ['_routeScope' => ['api']])]
final class OrderExportController
{
    public function __construct(private readonly Connection $connection, private readonly MessageBusInterface $messageBus) {}
    #[Route(path: '/api/_action/enterprise-order-integration/exports', name: 'api.action.enterprise_order_integration.exports', methods: ['GET'], defaults: [PlatformRequest::ATTRIBUTE_ACL => ['order:read']])]
    public function list(): JsonResponse
    {
        $rows = $this->connection->fetchAllAssociative('SELECT id, order_id, status, attempts, correlation_id, erp_reference, last_error, next_attempt_at, created_at, updated_at FROM enterprise_order_export ORDER BY created_at DESC LIMIT 100');
        foreach ($rows as &$row) {
            $row['id'] = bin2hex((string) $row['id']);
            $row['orderId'] = bin2hex((string) $row['order_id']);
            unset($row['order_id']);
            $row['attempts'] = (int) $row['attempts'];
        }
        unset($row);
        return new JsonResponse(['data' => $rows, 'meta' => ['limit' => 100]]);
    }
    #[Route(path: '/api/_action/enterprise-order-integration/exports/{orderId}/retry', name: 'api.action.enterprise_order_integration.retry', methods: ['POST'], requirements: ['orderId' => '[a-fA-F0-9-]{32,36}'], defaults: [PlatformRequest::ATTRIBUTE_ACL => ['order:update']])]
    public function retry(string $orderId): JsonResponse
    {
        $normalized = strtolower(str_replace('-', '', $orderId));
        if (!preg_match('/^[a-f0-9]{32}$/', $normalized)) return new JsonResponse(['error' => 'invalid_order_id'], 400);
        $bytes = hex2bin($normalized);
        $row = $this->connection->fetchAssociative('SELECT status, (updated_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)) AS is_recent FROM enterprise_order_export WHERE order_id = :id', ['id' => $bytes], ['id' => ParameterType::BINARY]);
        if (!is_array($row)) return new JsonResponse(['error' => 'export_not_found'], 404);
        if ($row['status'] === 'succeeded') return new JsonResponse(['error' => 'already_succeeded'], 409);
        if ($row['status'] === 'processing' && (int) $row['is_recent'] === 1) return new JsonResponse(['error' => 'export_is_processing'], 409);
        $this->connection->executeStatement("UPDATE enterprise_order_export SET status = 'pending', next_attempt_at = NULL, updated_at = NOW(3) WHERE order_id = :id AND status <> 'succeeded'", ['id' => $bytes], ['id' => ParameterType::BINARY]);
        $correlationId = bin2hex(random_bytes(16));
        $this->messageBus->dispatch(new ExportOrderMessage($normalized, $correlationId));
        return new JsonResponse(['status' => 'queued', 'orderId' => $normalized, 'correlationId' => $correlationId], 202);
    }
}