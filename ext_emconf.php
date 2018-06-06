<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "filefill".
 *
 * Auto generated 07-06-2018 00:24
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
  'version' => '1.2.1',
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
  '_md5_values_when_last_written' => 'a:22:{s:9:"ChangeLog";s:4:"7684";s:7:"LICENSE";s:4:"b234";s:9:"README.md";s:4:"0cdf";s:13:"composer.json";s:4:"2b81";s:12:"ext_icon.png";s:4:"970b";s:17:"ext_localconf.php";s:4:"ff94";s:14:"ext_tables.sql";s:4:"29ed";s:24:"sonar-project.properties";s:4:"1d7c";s:35:"Classes/Hooks/ResetMissingFiles.php";s:4:"e88e";s:45:"Classes/Resource/RemoteResourceCollection.php";s:4:"3a98";s:52:"Classes/Resource/RemoteResourceCollectionFactory.php";s:4:"0220";s:44:"Classes/Resource/RemoteResourceInterface.php";s:4:"3d69";s:42:"Classes/Resource/Domain/DomainResource.php";s:4:"16ab";s:52:"Classes/Resource/Domain/DomainResourceRepository.php";s:4:"3434";s:42:"Classes/Resource/Driver/FileFillDriver.php";s:4:"76d5";s:52:"Classes/Resource/Placeholder/PlaceholderResource.php";s:4:"6840";s:42:"Classes/Slot/FileProcessingServiceSlot.php";s:4:"cea3";s:36:"Classes/Slot/ResourceFactorySlot.php";s:4:"bc0e";s:38:"Classes/UserFunc/CheckMissingFiles.php";s:4:"8457";s:37:"Configuration/FlexForms/Resources.xml";s:4:"bdd1";s:48:"Configuration/TCA/Overrides/sys_file_storage.php";s:4:"ff68";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"508c";}',
);

