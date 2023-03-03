<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Form\Element;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Cordes <typo3@cordes.co>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Doctrine\DBAL\Result;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ShowMissingFiles extends AbstractFormElement
{
    /**
     * @var LanguageService
     */
    protected $languageService;

    /**
     * Container objects give $nodeFactory down to other containers.
     *
     * @param NodeFactory $nodeFactory
     * @param array $data
     * @param LanguageService|null $languageService
     * @throws \InvalidArgumentException
     */
    public function __construct(NodeFactory $nodeFactory, array $data, $languageService = null)
    {
        parent::__construct($nodeFactory, $data);
        $this->languageService = $languageService ?: $GLOBALS['LANG'];
    }

    /**
     * @return array
     */
    public function render()
    {
        $result = $this->initializeResultArray();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
        $expressionBuilder = $queryBuilder->expr();
        $queryResult = $queryBuilder->count('*')
            ->from('sys_file')
            ->where(
                $expressionBuilder->eq(
                    'storage',
                    $queryBuilder->createNamedParameter($this->data['vanillaUid'], \PDO::PARAM_INT)
                ),
                $expressionBuilder->eq(
                    'missing',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            )
            ->execute();

        if ($queryResult instanceof Result) {
            // TYPO3 >= 12
            $count = $queryResult->fetchOne();
        } else {
            // TYPO3 < 12
            $count = $queryResult->fetchColumn(0);
        }

        $html = [];
        $html[] = '<div class="form-control-wrap">';

        if ($count === 0) {
            $html[] = '<span class="badge badge-success">'
                . $this->languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.no_missing')
                . '</span>';
        } else {
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $html[] = '<span class="badge badge-danger">'
                . sprintf(
                    $this->languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.missing_files'),
                    $count
                )
                . '</span>';
            $html[] = '</div>';
            $html[] = '<div class="form-control-wrap t3js-module-docheader">';
            $html[] = '<a class="btn btn-default t3js-editform-submitButton" data-name="_save_tx_filefill_missing" data-form="EditDocumentController" data-value="1">';
            $html[] = $iconFactory->getIcon('actions-database-reload', Icon::SIZE_SMALL);
            $html[] = ' ' . $this->languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.reset');
            $html[] = '</a>';
        }

        $html[] = '</div>';

        $result['html'] = implode('', $html);

        return $result;
    }
}
