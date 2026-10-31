<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Command;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
#[AsCommand(name: 'erp:orders:health', description: 'Show health of the order export pipeline')]
final class OrderExportHealthCommand extends Command
{
    public function __construct(private readonly Connection $connection) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $counts = $this->connection->fetchAllKeyValue('SELECT status, COUNT(*) AS total FROM enterprise_order_export GROUP BY status');
            $stuck = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM enterprise_order_export WHERE status = 'processing' AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
            $due = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM enterprise_order_export WHERE status = 'failed' AND next_attempt_at <= NOW()");
        } catch (\Throwable $exception) {
            $io->error('Export table unavailable. Install the plugin and run its migration.');
            $io->comment($exception->getMessage());
            return Command::FAILURE;
        }
        $io->title('ERP Order Export Health');
        $io->table(['Status', 'Count'], array_map(static fn ($status, $count) => [$status, (string) $count], array_keys($counts), array_values($counts)));
        $io->definitionList(['Stuck processing (>10 min)' => (string) $stuck], ['Retries due' => (string) $due]);
        if ($stuck > 0 || $due > 0) { $io->warning('The export pipeline requires attention.'); return Command::FAILURE; }
        $io->success('No stuck exports or due retries detected.');
        return Command::SUCCESS;
    }
}