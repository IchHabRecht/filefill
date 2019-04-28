<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing']['filefill'] =
        \IchHabRecht\Filefill\Hooks\FlexFormToolsHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['filefill'] =
        \IchHabRecht\Filefill\Hooks\ResetMissingFiles::class;

    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $dispatcher->connect(
        \TYPO3\CMS\Core\Resource\ResourceFactory::class,
        \TYPO3\CMS\Core\Resource\ResourceFactoryInterface::SIGNAL_PostProcessStorage,
        \IchHabRecht\Filefill\Slot\ResourceFactorySlot::class,
        'initializeResourceStorage'
    );
    $dispatcher->connect(
        \TYPO3\CMS\Core\Resource\ResourceStorage::class,
        \TYPO3\CMS\Core\Resource\Service\FileProcessingService::SIGNAL_PreFileProcess,
        \IchHabRecht\Filefill\Slot\FileProcessingServiceSlot::class,
        'ensureOriginalFileExists'
    );

    if (empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'] = array_merge(
        [
            'domain' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.domain',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.url',
                    'config' => [
                        'type' => 'input',
                        'eval' => 'required',
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Domain\DomainResource::class,
            ],
            'sys_domain' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.sys_domain',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.sys_domain',
                    'config' => [
                        'type' => 'check',
                        'default' => '1',
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Domain\SysDomainResource::class,
            ],
            'placeholder' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.placeholder_com',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.placeholder_com',
                    'config' => [
                        'type' => 'check',
                        'default' => '1',
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Placeholder\PlaceholderResource::class,
            ],
        ],
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler']
    );
});
