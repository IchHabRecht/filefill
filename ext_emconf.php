<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "filefill".
 *
 * Auto generated 12-03-2018 13:32
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
  'version' => '1.1.0',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '7.6.0-8.7.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:20:{s:9:"ChangeLog";s:4:"22a1";s:9:"README.md";s:4:"a600";s:13:"composer.json";s:4:"2b81";s:12:"ext_icon.png";s:4:"970b";s:17:"ext_localconf.php";s:4:"1d19";s:14:"ext_tables.sql";s:4:"29ed";s:24:"sonar-project.properties";s:4:"1d7c";s:35:"Classes/Hooks/ResetMissingFiles.php";s:4:"04bc";s:45:"Classes/Resource/RemoteResourceCollection.php";s:4:"bb23";s:52:"Classes/Resource/RemoteResourceCollectionFactory.php";s:4:"5af8";s:44:"Classes/Resource/RemoteResourceInterface.php";s:4:"f0d8";s:42:"Classes/Resource/Domain/DomainResource.php";s:4:"c142";s:52:"Classes/Resource/Domain/DomainResourceRepository.php";s:4:"551d";s:42:"Classes/Resource/Driver/FileFillDriver.php";s:4:"bb61";s:52:"Classes/Resource/Placeholder/PlaceholderResource.php";s:4:"e43c";s:36:"Classes/Slot/ResourceFactorySlot.php";s:4:"f8e9";s:38:"Classes/UserFunc/CheckMissingFiles.php";s:4:"a9ee";s:37:"Configuration/FlexForms/Resources.xml";s:4:"bdd1";s:48:"Configuration/TCA/Overrides/sys_file_storage.php";s:4:"d62a";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"508c";}',
);

