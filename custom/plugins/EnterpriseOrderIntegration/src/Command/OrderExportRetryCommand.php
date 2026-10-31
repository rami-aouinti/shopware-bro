<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Command;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use EnterpriseOrderIntegration\Application\Message\ExportOrderMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
#[AsCommand(name: 'erp:orders:retry', description: 'Queue a failed ERP order export for retry')]
final class OrderExportRetryCommand extends Command
{
    public function __construct(private readonly Connection $connection, private readonly MessageBusInterface $messageBus) { parent::__construct(); }
    protected function configure(): void { $this->addArgument('order-id', InputArgument::REQUIRED, 'Shopware order UUID'); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = strtolower(str_replace('-', '', (string) $input->getArgument('order-id')));
        if (!preg_match('/^[a-f0-9]{32}$/', $id)) { $io->error('Invalid order UUID.'); return Command::INVALID; }
        $bytes = hex2bin($id);
        $row = $this->connection->fetchAssociative('SELECT status, (updated_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)) AS is_recent FROM enterprise_order_export WHERE order_id = :id', ['id' => $bytes], ['id' => ParameterType::BINARY]);
        if (!is_array($row)) { $io->error('No export record found.'); return Command::FAILURE; }
        if ($row['status'] === 'succeeded') { $io->warning('Already exported successfully; retry refused.'); return Command::SUCCESS; }
        if ($row['status'] === 'processing' && (int) $row['is_recent'] === 1) { $io->warning('Export is actively processing; retry refused.'); return Command::SUCCESS; }
        $correlationId = bin2hex(random_bytes(16));
        $this->connection->executeStatement("UPDATE enterprise_order_export SET status = 'pending', next_attempt_at = NULL, updated_at = NOW(3) WHERE order_id = :id", ['id' => $bytes], ['id' => ParameterType::BINARY]);
        $this->messageBus->dispatch(new ExportOrderMessage($id, $correlationId));
        $io->success('Export queued. Correlation ID: ' . $correlationId);
        return Command::SUCCESS;
    }
}