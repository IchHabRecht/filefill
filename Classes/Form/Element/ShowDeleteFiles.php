<?php
declare(strict_types = 1);
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
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ShowDeleteFiles extends AbstractFormElement
{
    /**
     * @var FileRepository|null
     */
    protected $fileRepository;

    /**
     * @var LanguageService
     */
    protected $languageService;

    /**
     * Container objects give $nodeFactory down to other containers.
     *
     * @param NodeFactory $nodeFactory
     * @param array $data
     * @param FileRepository|null $fileRepository
     * @param LanguageService|null $languageService
     */
    public function __construct(NodeFactory $nodeFactory, array $data, FileRepository $fileRepository = null, $languageService = null)
    {
        parent::__construct($nodeFactory, $data);
        $this->fileRepository = $fileRepository ?: GeneralUtility::makeInstance(FileRepository::class);
        $this->languageService = $languageService ?: $GLOBALS['LANG'];
    }

    /**
     * @return array
     */
    public function render()
    {
        $result = $this->initializeResultArray();

        $rows = $this->fileRepository->countByIdentifier($this->data['vanillaUid']);

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
                $html[] = $iconFactory->getIcon('actions-edit-delete', Icon::SIZE_SMALL);
                $html[] = ' ' . sprintf(
                        $this->languageService->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.delete_files'),
                        $row['count'],
                        $this->languageService->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'][$row['tx_filefill_identifier']]['title'])
                    );
                $html[] = '</a>';
            }
        }
        $html[] = '</div>';

        $result['html'] = implode('', $html);

        return $result;
    }
}
