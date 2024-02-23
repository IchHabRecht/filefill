<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Command;

use Doctrine\DBAL\Connection;
use IchHabRecht\Filefill\Repository\FileRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResetCommand extends AbstractCommand
{
    /**
     * @var \TYPO3\CMS\Core\Database\Connection
     */
    protected $connection;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    public function __construct(string $name = null, Connection $connection = null, FileRepository $fileRepository = null)
    {
        parent::__construct($name);

        $this->connection = $connection ?: GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');
        $this->fileRepository = $fileRepository ?: GeneralUtility::makeInstance(FileRepository::class);
    }

    public function configure(): void
    {
        $this->setDescription('Resets missing files')
            ->addOption(
                'storage',
                's',
                InputOption::VALUE_OPTIONAL,
                'Reset files from a specific storage only'
            );
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $storage = $input->getOption('storage');

        $enabledStorages = $this->getEnabledStorages();
        if ($storage !== null) {
            $storage = (int)$storage;
            $enabledStorages = [
                $storage => $enabledStorages[$storage] ?? [],
            ];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $expressionBuilder = $queryBuilder->expr();
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getConcreteQueryBuilder()->select('COUNT(*) AS count', 'f.storage', 's.name');
        $statement = $queryBuilder->from('sys_file', 'f')
            ->leftJoin(
                'f',
                'sys_file_storage',
                's',
                $expressionBuilder->eq('s.uid', $queryBuilder->quoteIdentifier('f.storage'))
            )
            ->where(
                $expressionBuilder->in(
                    'f.storage',
                    $queryBuilder->createNamedParameter(array_keys($enabledStorages), Connection::PARAM_INT_ARRAY)
                ),
                $expressionBuilder->eq(
                    'f.missing',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            )
            ->groupBy('f.storage')
            ->orderBy('f.storage')
            ->execute();

        while ($row = $statement->fetch()) {
            $updateQueryBuilder = $this->connection->createQueryBuilder();
            $updateQueryBuilder->update('sys_file')
                ->where(
                    $updateQueryBuilder->expr()->eq(
                        'storage',
                        $updateQueryBuilder->createNamedParameter($row['storage'], \PDO::PARAM_INT)
                    )
                )
                ->set('missing', 0, true, \PDO::PARAM_INT)
                ->execute();
            $output->writeln(sprintf(
                'Reset %d file(s) in storage "%s" (uid: %d)',
                $row['count'],
                $row['name'],
                $row['storage']
            ));
        }

        return 0;
    }
}
