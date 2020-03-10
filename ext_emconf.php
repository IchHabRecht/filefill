<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "filefill".
 *
 * Auto generated 10-03-2020 15:43
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'File Fill',
  'description' => 'Find and fetch missing local files from different remotes',
  'category' => 'misc',
  'author' => 'Nicole Cordes',
  'author_email' => 'typo3@cordes.co',
  'author_company' => 'biz-design',
  'state' => 'stable',
  'uploadfolder' => 0,
  'createDirs' => '',
  'clearCacheOnLoad' => 0,
  'version' => '3.1.0',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '8.7.0-10.3.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:29:{s:9:"ChangeLog";s:4:"1d00";s:7:"LICENSE";s:4:"b234";s:9:"README.md";s:4:"f0e0";s:13:"composer.json";s:4:"da69";s:12:"ext_icon.png";s:4:"970b";s:17:"ext_localconf.php";s:4:"303f";s:14:"ext_tables.sql";s:4:"29ed";s:16:"phpunit.xml.dist";s:4:"041c";s:24:"sonar-project.properties";s:4:"4068";s:47:"Classes/Exception/MissingInterfaceException.php";s:4:"42cb";s:46:"Classes/Exception/UnknownResourceException.php";s:4:"a9e0";s:41:"Classes/Form/Element/ShowMissingFiles.php";s:4:"aa8b";s:35:"Classes/Hooks/FlexFormToolsHook.php";s:4:"8cd3";s:35:"Classes/Hooks/ResetMissingFiles.php";s:4:"081c";s:45:"Classes/Resource/RemoteResourceCollection.php";s:4:"1c43";s:52:"Classes/Resource/RemoteResourceCollectionFactory.php";s:4:"f7a9";s:44:"Classes/Resource/RemoteResourceInterface.php";s:4:"8c84";s:42:"Classes/Resource/Domain/DomainResource.php";s:4:"00f4";s:52:"Classes/Resource/Domain/DomainResourceRepository.php";s:4:"9c1d";s:45:"Classes/Resource/Domain/SysDomainResource.php";s:4:"3358";s:42:"Classes/Resource/Driver/FileFillDriver.php";s:4:"4892";s:52:"Classes/Resource/Placeholder/PlaceholderResource.php";s:4:"c9a2";s:42:"Classes/Slot/FileProcessingServiceSlot.php";s:4:"fa97";s:36:"Classes/Slot/ResourceFactorySlot.php";s:4:"83f1";s:37:"Configuration/FlexForms/Resources.xml";s:4:"6a88";s:48:"Configuration/TCA/Overrides/sys_file_storage.php";s:4:"fb5d";s:27:"Resources/Private/.htaccess";s:4:"8594";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"508c";s:36:"Resources/Public/Icons/Extension.svg";s:4:"0ad6";}',
);

