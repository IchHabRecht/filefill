<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['filefill'] =
        'IchHabRecht\\Filefill\\Hooks\\ResetMissingFiles';

    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
    $dispatcher->connect(
        'TYPO3\\CMS\\Core\\Resource\\ResourceFactory',
        \TYPO3\CMS\Core\Resource\ResourceFactoryInterface::SIGNAL_PostProcessStorage,
        'IchHabRecht\\Filefill\\Slot\\ResourceFactorySlot',
        'initializeResourceStorage'
    );
    $dispatcher->connect(
        'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
        \TYPO3\CMS\Core\Resource\Service\FileProcessingService::SIGNAL_PreFileProcess,
        'IchHabRecht\\Filefill\\Slot\\FileProcessingServiceSlot',
        'ensureOriginalFileExists'
    );
});
