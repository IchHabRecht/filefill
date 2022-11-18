<?php

defined('TYPO3') || die();

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing']['filefill'] =
        \IchHabRecht\Filefill\Hooks\FlexFormToolsHook::class;

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
            'sys_domain' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.sys_domain',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.sys_domain',
                    'config' => [
                        'type' => 'check',
                        'default' => '1',
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Handler\SysDomainResource::class,
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
            'placeholder' => [
                'title' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.placeholder_com',
                'config' => [
                    'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.placeholder_com',
                    'config' => [
                        'type' => 'check',
                        'default' => '1',
                    ],
                ],
                'handler' => \IchHabRecht\Filefill\Resource\Handler\PlaceholderResource::class,
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
