<?php declare(strict_types=1);

namespace EnterpriseOrderIntegration\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'erp:orders:demo-data',
    description: 'Create clearly marked demo export records using existing Shopware orders.',
)]
final class OrderExportDemoDataCommand extends Command
{
    private const DEMO_STATES = [
        ['status' => 'succeeded', 'attempts' => 1, 'reference' => 'DEMO-ERP-1001', 'error' => null],
        ['status' => 'failed', 'attempts' => 3, 'reference' => null, 'error' => 'Demo: ERP endpoint returned HTTP 503.'],
        ['status' => 'pending', 'attempts' => 0, 'reference' => null, 'error' => null],
        ['status' => 'succeeded', 'attempts' => 1, 'reference' => 'DEMO-ERP-1004', 'error' => null],
        ['status' => 'failed', 'attempts' => 2, 'reference' => null, 'error' => 'Demo: ERP connection timed out.'],
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->connection->fetchFirstColumn(
            'SELECT o.id FROM \`order\` o'
            . ' LEFT JOIN enterprise_order_export e ON e.order_id = o.id'
            . ' WHERE e.id IS NULL ORDER BY o.created_at DESC LIMIT 5',
        );

        if ($orders === []) {
            $output->writeln('<comment>No unexported Shopware orders found. Demo records were not created.</comment>');

            return Command::SUCCESS;
        }

        $created = 0;
        foreach (array_values($orders) as $index => $orderId) {
            $demo = self::DEMO_STATES[$index % count(self::DEMO_STATES)];
            $createdAt = new \DateTimeImmutable(sprintf('-%d days', $index));
            $this->connection->insert('enterprise_order_export', [
                'id' => Uuid::randomBytes(),
                'order_id' => (string) $orderId,
                'status' => $demo['status'],
                'attempts' => $demo['attempts'],
                'correlation_id' => 'DEMO-' . strtoupper(bin2hex(random_bytes(8))),
                'erp_reference' => $demo['reference'],
                'last_error' => $demo['error'],
                'next_attempt_at' => null,
                'is_demo' => 1,
                'created_at' => $createdAt->format('Y-m-d H:i:s.v'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s.v'),
            ], [
                'id' => ParameterType::BINARY,
                'order_id' => ParameterType::BINARY,
            ]);
            ++$created;
        }

        $output->writeln(sprintf('<info>Created %d demo export record(s). They are marked as demo and cannot be retried.</info>', $created));
        $output->writeln('<comment>Only existing orders without an export record were used; no real ERP calls were made.</comment>');

        return Command::SUCCESS;
    }
}
