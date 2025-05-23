<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Form\Element;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Hummel <nicole-typo3@nimut.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Page\PageRenderer;

class ShowMissingFiles extends AbstractFormElement
{
    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly IconFactory $iconFactory,
        protected readonly PageRenderer $pageRenderer
    ) {
    }

    public function render(): array
    {
        $result = $this->initializeResultArray();

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file');
        $expressionBuilder = $queryBuilder->expr();
        $count = $queryBuilder->count('*')
            ->from('sys_file')
            ->where(
                $expressionBuilder->eq(
                    'storage',
                    $queryBuilder->createNamedParameter($this->data['vanillaUid'], ParameterType::INTEGER)
                ),
                $expressionBuilder->eq(
                    'missing',
                    $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchOne();

        $html = [];
        $html[] = '<div class="form-group">';

        $languageService = $this->getLanguageService();
        if ($count === 0) {
            $html[] = '<div class="form-text">';
            $html[] = '<span class="badge badge-success">'
                . $languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.no_missing')
                . '</span>'
                . '</div>';
        } else {
            $this->pageRenderer->loadJavaScriptModule('@ichhabrecht/filefill/form/submit-interceptor.js');
            $html[] = '<div class="form-control-wrap">';
            $html[] = '<a class="btn btn-default t3js-editform-submitButton" data-name="_save_tx_filefill_missing" data-form="EditDocumentController" data-value="1">';
            $html[] = $this->iconFactory->getIcon('actions-database-reload', IconSize::SMALL);
            $html[] = ' ' . $languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.reset');
            $html[] = '</a>';
            $html[] = '</div>';
            $html[] = '<div class="form-text">';
            $html[] = '<span class="badge badge-danger">'
                . sprintf(
                    $languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.missing_files'),
                    $count
                )
                . '</span>';
            $html[] = '</div>';
        }

        $html[] = '</div>';

        $result['html'] = $this->wrapWithFieldsetAndLegend(implode('', $html));

        return $result;
    }
}
