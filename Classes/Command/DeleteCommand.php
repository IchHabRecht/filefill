<?php
namespace IchHabRecht\Filefill\Command;

use Doctrine\DBAL\Connection;
use IchHabRecht\Filefill\Repository\FileRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteCommand extends Command
{
    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var LanguageService
     */
    protected $languageService;

    public function __construct(string $name = null, FileRepository $fileRepository = null, $languageService = null)
    {
        parent::__construct($name);

        $this->fileRepository = $fileRepository ?: GeneralUtility::makeInstance(FileRepository::class);
        $this->languageService = $languageService ?: $GLOBALS['LANG'];
    }

    public function configure()
    {
        $this->setDescription('Deletes files fetched by filefill')
            ->addOption(
                'identifier',
                'i',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Delete files from specific identifier(s)'
            )
            ->addOption(
                'storage',
                's',
                InputOption::VALUE_OPTIONAL,
                'Delete files from a specific storage only'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Delete all files fetched by filefill'
            );
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifiers = $input->getOption('identifier');
        $storage = $input->getOption('storage');
        $all = $input->getOption('all');

        if (empty($identifiers) && empty($all)) {
            throw new \RuntimeException('No identifier configured neither --all option found.', 1584358697);
        }

        if ($all) {
            $rows = $this->fileRepository->countByIdentifier($storage);
            $identifiers = array_column($rows, 1);
        }

        $enabledStorages = $this->getEnabledStorages();
        if ($storage !== null) {
            $storage = (int)$storage;
            $enabledStorages = [
                $storage => $enabledStorages[$storage] ?? [],
            ];
        }

        foreach ($enabledStorages as $storage) {
            foreach ($identifiers as $identifier) {
                $count = $this->fileRepository->deleteByIdentifier($identifier, $storage['uid']);
                if ($count) {
                    $output->writeln(sprintf(
                        'Deleted %d file(s) from "%s" resource in storage "%s" (uid: %d)',
                        $count,
                        $this->languageService->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'][$identifier]['title']),
                        $storage['name'],
                        $storage['uid']
                    ));
                }
            }
        }
    }

    protected function getEnabledStorages(): array
    {
        $configuredStorages = array_keys($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'] ?? ['0' => '']);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_storage');
        $expressionBuilder = $queryBuilder->expr();
        $rows = $queryBuilder->select('uid', 'name')
            ->from('sys_file_storage')
            ->where(
                $expressionBuilder->orX(
                    $expressionBuilder->eq(
                        'tx_filefill_enable',
                        $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                    ),
                    $expressionBuilder->in(
                        'uid',
                        $queryBuilder->createNamedParameter($configuredStorages, Connection::PARAM_INT_ARRAY)
                    )
                )
            )
            ->orderBy('uid')
            ->execute()
            ->fetchAll();

        return array_combine(array_map('intval', array_column($rows, 'uid')), $rows);
    }
}
