<?php

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['filefill_missing'] =
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
                'handler' => \IchHabRecht\Filefill\Resource\Handler\DomainResource::class,
            ],
            'imagebuilder' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.imagebuilder',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.colors',
                    'config' => [
                        'type' => 'input',
                        'eval' => 'required',
                        'default' => '#FFFFFF, #000000',
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Handler\ImageBuilderResource::class,
            ],
            'placehold' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.placehold',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.placehold',
                    'config' => [
                        'type' => 'check',
                        'default' => '1',
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Handler\PlaceholdResource::class,
            ],
            'static' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.static',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.static',
                    'config' => [
                        'type' => 'text',
                        'rows' => 5,
                        'cols' => 30,
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Handler\StaticFileResource::class,
            ],
        ],
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler']
    );
});
