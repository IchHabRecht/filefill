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

use IchHabRecht\Filefill\Repository\FileRepository;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ShowDeleteFiles extends AbstractFormElement
{
    /**
     * Container objects give $nodeFactory down to other containers.
     *
     * @param FileRepository $fileRepository
     * @param LanguageService $languageService
     */
    public function __construct(
        protected readonly FileRepository $fileRepository,
        protected readonly LanguageService $languageService
    ) {
    }

    /**
     * @return array
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $rows = $this->fileRepository->countByIdentifier($this->data['vanillaUid']);

        // TODO https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.0/Breaking-97330-FormEngineElementClassesMustCreateLabelOrLegend.html

        $html = [];
        $html[] = '<div class="form-control-wrap">';

        if (empty($rows)) {
            $html[] = '<span class="badge badge-success">'
                . $this->languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.no_delete')
                . '</span>';
        } else {
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            foreach ($rows as $row) {
                $html[] = '</div>';
                $html[] = '<div class="form-control-wrap t3js-module-docheader">';
                $html[] = '<a class="btn btn-default t3js-editform-submitButton" data-name="_save_tx_filefill_delete" data-form="EditDocumentController" data-value="' . $row['tx_filefill_identifier'] . '">';
                $html[] = $iconFactory->getIcon('actions-edit-delete', IconSize::SMALL);
                $html[] = ' ' . sprintf(
                    $this->languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.delete_files'),
                    $row['count'],
                    $this->languageService->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'][$row['tx_filefill_identifier']]['title'] ?? $row['tx_filefill_identifier'])
                );
                $html[] = '</a>';
            }
        }
        $html[] = '</div>';

        $result['html'] = implode('', $html);

        return $result;
    }
}
