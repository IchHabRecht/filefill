<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Command;

use IchHabRecht\Filefill\Repository\FileRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteCommand extends AbstractCommand
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

    public function configure(): void
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
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifiers = $input->getOption('identifier');
        $storage = $input->getOption('storage');
        $all = $input->getOption('all');

        if (empty($identifiers) && empty($all)) {
            throw new \RuntimeException('No identifier configured neither --all option found.', 1584358697);
        }

        if ($all) {
            $rows = $this->fileRepository->countByIdentifier($storage);
            $identifiers = array_column($rows, 'tx_filefill_identifier');
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

        return 0;
    }
}
