<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['filefill'] =
        \IchHabRecht\Filefill\Hooks\ResetMissingFiles::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['filefill_delete'] =
        \IchHabRecht\Filefill\Hooks\DeleteFiles::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1583747569] = [
        'nodeName' => 'showMissingFiles',
        'priority' => 40,
        'class' => \IchHabRecht\Filefill\Form\Element\ShowMissingFiles::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1583933371] = [
        'nodeName' => 'showDeleteFiles',
        'priority' => 40,
        'class' => \IchHabRecht\Filefill\Form\Element\ShowDeleteFiles::class,
    ];

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
});
