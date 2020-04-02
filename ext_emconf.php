<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "filefill".
 *
 * Auto generated 02-04-2020 15:17
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
  'version' => '1.6.0',
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
  '_md5_values_when_last_written' => 'a:32:{s:9:"ChangeLog";s:4:"7171";s:7:"LICENSE";s:4:"b234";s:9:"README.md";s:4:"64a3";s:13:"composer.json";s:4:"482d";s:13:"composer.lock";s:4:"95a8";s:12:"ext_icon.png";s:4:"970b";s:17:"ext_localconf.php";s:4:"bf74";s:14:"ext_tables.sql";s:4:"5d8e";s:16:"phpunit.xml.dist";s:4:"f28a";s:24:"sonar-project.properties";s:4:"88f7";s:40:"Classes/Form/Element/ShowDeleteFiles.php";s:4:"bf17";s:41:"Classes/Form/Element/ShowMissingFiles.php";s:4:"1d27";s:29:"Classes/Hooks/DeleteFiles.php";s:4:"18a6";s:35:"Classes/Hooks/ResetMissingFiles.php";s:4:"e88e";s:37:"Classes/Repository/FileRepository.php";s:4:"56ed";s:45:"Classes/Resource/RemoteResourceCollection.php";s:4:"090d";s:52:"Classes/Resource/RemoteResourceCollectionFactory.php";s:4:"64c0";s:44:"Classes/Resource/RemoteResourceInterface.php";s:4:"c081";s:42:"Classes/Resource/Domain/DomainResource.php";s:4:"7956";s:52:"Classes/Resource/Domain/DomainResourceRepository.php";s:4:"d8e5";s:42:"Classes/Resource/Driver/FileFillDriver.php";s:4:"efac";s:52:"Classes/Resource/Placeholder/PlaceholderResource.php";s:4:"c07b";s:42:"Classes/Slot/FileProcessingServiceSlot.php";s:4:"cea3";s:36:"Classes/Slot/ResourceFactorySlot.php";s:4:"adbd";s:37:"Configuration/FlexForms/Resources.xml";s:4:"e7e0";s:48:"Configuration/TCA/Overrides/sys_file_storage.php";s:4:"032c";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"ab20";s:47:"Tests/Functional/AbstractFunctionalTestCase.php";s:4:"381e";s:33:"Tests/Functional/FilefillTest.php";s:4:"7e69";s:47:"Tests/Functional/Fixtures/Database/sys_file.xml";s:4:"2cb0";s:56:"Tests/Functional/Fixtures/Database/sys_file_metadata.xml";s:4:"bd9a";s:55:"Tests/Functional/Fixtures/Database/sys_file_storage.xml";s:4:"0679";}',
);

