<?php declare(strict_types=1);

namespace EnterpriseOrderIntegration\\Controller\\Administration;

use Doctrine\\DBAL\\Connection;
use Doctrine\\DBAL\\ParameterType;
use EnterpriseOrderIntegration\\Application\\Message\\ExportOrderMessage;
use Shopware\\Core\\PlatformRequest;
use Symfony\\Component\\HttpFoundation\\JsonResponse;
use Symfony\\Component\\HttpFoundation\\Request;
use Symfony\\Component\\Routing\\Attribute\\Route;
use Symfony\\Component\\Messenger\\MessageBusInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
final class OrderExportController
{
    private const SORT_COLUMNS = [
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'status' => 'status',
        'attempts' => 'attempts',
        'erpReference' => 'erp_reference',
        'orderId' => 'order_id',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    #[Route(
        path: '/api/_action/enterprise-order-integration/exports',
        name: 'api.action.enterprise_order_integration.exports',
        methods: ['GET'],
        defaults: [PlatformRequest::ATTRIBUTE_ACL => ['order:read']],
    )]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 25)));
        $sortBy = (string) $request->query->get('sortBy', 'createdAt');
        $sortColumn = self::SORT_COLUMNS[$sortBy] ?? self::SORT_COLUMNS['createdAt'];
        $direction = strtoupper((string) $request->query->get('sortDirection', 'DESC'));
        $direction = $direction === 'ASC' ? 'ASC' : 'DESC';

        $where = [];
        $parameters = [];
        $types = [];

        $status = trim((string) $request->query->get('status', ''));
        if (in_array($status, ['pending', 'processing', 'succeeded', 'failed'], true)) {
            $where[] = 'status = :status';
            $parameters['status'] = $status;
        }

        $search = trim((string) $request->query->get('search', ''));
        if ($search !== '') {
            $where[] = '(LOWER(HEX(order_id)) LIKE :search OR LOWER(correlation_id) LIKE :search OR LOWER(COALESCE(erp_reference, \'\')) LIKE :search OR LOWER(COALESCE(last_error, \'\')) LIKE :search)';
            $parameters['search'] = '%' . mb_strtolower($search) . '%';
        }

        foreach (['fromDate' => '>=', 'toDate' => '<='] as $key => $operator) {
            $date = trim((string) $request->query->get($key, ''));
            if ($date !== '' && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date) === 1) {
                $where[] = 'DATE(created_at) ' . $operator . ' :' . $key;
                $parameters[$key] = $date;
            }
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $total = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM enterprise_order_export' . $whereSql,
            $parameters,
            $types,
        );

        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, order_id, status, attempts, correlation_id, erp_reference, last_error, next_attempt_at, created_at, updated_at, is_demo'
            . ' FROM enterprise_order_export'
            . $whereSql
            . ' ORDER BY ' . $sortColumn . ' ' . $direction
            . ' LIMIT :limit OFFSET :offset',
            array_merge($parameters, [
                'limit' => $limit,
                'offset' => ($page - 1) * $limit,
            ]),
            array_merge($types, [
                'limit' => ParameterType::INTEGER,
                'offset' => ParameterType::INTEGER,
            ]),
        );

        foreach ($rows as &$row) {
            $row['id'] = bin2hex((string) $row['id']);
            $row['orderId'] = bin2hex((string) $row['order_id']);
            unset($row['order_id']);
            $row['attempts'] = (int) $row['attempts'];
            $row['isDemo'] = (bool) $row['is_demo'];
            unset($row['is_demo']);
        }
        unset($row);

        return new JsonResponse([
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / $limit),
                'sortBy' => $sortBy,
                'sortDirection' => $direction,
            ],
        ]);
    }

    #[Route(
        path: '/api/_action/enterprise-order-integration/exports/{orderId}/retry',
        name: 'api.action.enterprise_order_integration.retry',
        methods: ['POST'],
        requirements: ['orderId' => '[a-fA-F0-9-]{32,36}'],
        defaults: [PlatformRequest::ATTRIBUTE_ACL => ['order:update']],
    )]
    public function retry(string $orderId): JsonResponse
    {
        $normalized = strtolower(str_replace('-', '', $orderId));
        if (!preg_match('/^[a-f0-9]{32}$/', $normalized)) {
            return new JsonResponse(['error' => 'invalid_order_id'], 400);
        }

        $bytes = hex2bin($normalized);
        $row = $this->connection->fetchAssociative(
            'SELECT status, is_demo, (updated_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)) AS is_recent'
            . ' FROM enterprise_order_export WHERE order_id = :id',
            ['id' => $bytes],
            ['id' => ParameterType::BINARY],
        );

        if (!is_array($row)) {
            return new JsonResponse(['error' => 'export_not_found'], 404);
        }
        if ((bool) $row['is_demo']) {
            return new JsonResponse(['error' => 'demo_export_cannot_be_retried'], 409);
        }
        if ($row['status'] === 'succeeded') {
            return new JsonResponse(['error' => 'already_succeeded'], 409);
        }
        if ($row['status'] === 'processing' && (int) $row['is_recent'] === 1) {
            return new JsonResponse(['error' => 'export_is_processing'], 409);
        }

        $this->connection->executeStatement(
            "UPDATE enterprise_order_export SET status = 'pending', next_attempt_at = NULL, updated_at = NOW(3)"
            . ' WHERE order_id = :id AND status <> \'succeeded\' AND is_demo = 0',
            ['id' => $bytes],
            ['id' => ParameterType::BINARY],
        );

        $correlationId = bin2hex(random_bytes(16));
        $this->messageBus->dispatch(new ExportOrderMessage($normalized, $correlationId));

        return new JsonResponse([
            'status' => 'queued',
            'orderId' => $normalized,
            'correlationId' => $correlationId,
        ], 202);
    }
}
