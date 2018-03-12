<?php
defined('TYPO3_MODE') || die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['filefill'] =
    \IchHabRecht\Filefill\Hooks\ResetMissingFiles::class;

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
    \TYPO3\CMS\Core\Resource\ResourceFactory::class,
    \TYPO3\CMS\Core\Resource\ResourceFactoryInterface::SIGNAL_PostProcessStorage,
    \IchHabRecht\Filefill\Slot\ResourceFactorySlot::class,
    'initializeResourceStorage'
);
