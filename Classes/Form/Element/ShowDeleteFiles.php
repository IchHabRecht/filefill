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

use IchHabRecht\Filefill\Repository\FileRepository;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Page\PageRenderer;

class ShowDeleteFiles extends AbstractFormElement
{
    public function __construct(
        protected readonly FileRepository $fileRepository,
        protected readonly IconFactory $iconFactory,
        protected readonly PageRenderer $pageRenderer
    ) {
    }

    /**
     * @return array
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $rows = $this->fileRepository->countByIdentifier($this->data['vanillaUid']);

        $html = [];
        $html[] = '<div class="row">';

        $languageService = $this->getLanguageService();
        if (empty($rows)) {
            $html[] = '<div class="form-group">';
            $html[] = '<div class="form-text">';
            $html[] = '<span class="badge badge-success">'
                . $languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.no_delete')
                . '</span>';
            $html[] = '</div>';
            $html[] = '</div>';
        } else {
            $this->pageRenderer->loadJavaScriptModule('@ichhabrecht/filefill/form/submit-interceptor.js');
            foreach ($rows as $row) {
                $html[] = '<div class="form-group">';
                $html[] = '<div class="form-control-wrap">';
                $html[] = '<a class="btn btn-default t3js-editform-submitButton" data-name="_save_tx_filefill_delete" data-form="EditDocumentController" data-value="' . $row['tx_filefill_identifier'] . '">';
                $html[] = $this->iconFactory->getIcon('actions-edit-delete', IconSize::SMALL) . ' ';
                $html[] = sprintf(
                    $languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.delete_files'),
                    $row['count'],
                    $languageService->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'][$row['tx_filefill_identifier']]['title'] ?? $row['tx_filefill_identifier'])
                );
                $html[] = '</a>';
                $html[] = '</div>';
                $html[] = '</div>';
            }
        }
        $html[] = '</div>';

        $result['html'] = $this->wrapWithFieldsetAndLegend(implode('', $html));

        return $result;
    }
}
