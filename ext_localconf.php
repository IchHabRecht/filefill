<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
    \TYPO3\CMS\Core\Resource\ResourceFactory::class,
    \TYPO3\CMS\Core\Resource\ResourceFactoryInterface::SIGNAL_PostProcessStorage,
    \IchHabRecht\Filefill\Slot\ResourceFactorySlot::class,
    'initializeResourceStorage'
);
