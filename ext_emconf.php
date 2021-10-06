<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "filefill".
 *
 * Auto generated 07-05-2020 13:17
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
  'version' => '4.0.0',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '10.4.0-11.5.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:42:{s:9:"ChangeLog";s:4:"80fb";s:7:"LICENSE";s:4:"b234";s:9:"README.md";s:4:"f0e0";s:13:"composer.json";s:4:"8f63";s:13:"composer.lock";s:4:"72c9";s:12:"ext_icon.png";s:4:"970b";s:17:"ext_localconf.php";s:4:"9194";s:14:"ext_tables.sql";s:4:"5d8e";s:16:"phpunit.xml.dist";s:4:"041c";s:24:"sonar-project.properties";s:4:"4068";s:35:"Classes/Command/AbstractCommand.php";s:4:"7ee7";s:33:"Classes/Command/DeleteCommand.php";s:4:"4a30";s:32:"Classes/Command/ResetCommand.php";s:4:"ca22";s:47:"Classes/Exception/MissingInterfaceException.php";s:4:"42cb";s:46:"Classes/Exception/UnknownResourceException.php";s:4:"a9e0";s:40:"Classes/Form/Element/ShowDeleteFiles.php";s:4:"0b48";s:41:"Classes/Form/Element/ShowMissingFiles.php";s:4:"aa8b";s:29:"Classes/Hooks/DeleteFiles.php";s:4:"63ce";s:35:"Classes/Hooks/FlexFormToolsHook.php";s:4:"8cd3";s:35:"Classes/Hooks/ResetMissingFiles.php";s:4:"081c";s:47:"Classes/Repository/DomainResourceRepository.php";s:4:"47ef";s:37:"Classes/Repository/FileRepository.php";s:4:"5f21";s:45:"Classes/Resource/RemoteResourceCollection.php";s:4:"bdbe";s:52:"Classes/Resource/RemoteResourceCollectionFactory.php";s:4:"0ad0";s:44:"Classes/Resource/RemoteResourceInterface.php";s:4:"8c84";s:42:"Classes/Resource/Domain/DomainResource.php";s:4:"e019";s:45:"Classes/Resource/Domain/SysDomainResource.php";s:4:"5d61";s:42:"Classes/Resource/Driver/FileFillDriver.php";s:4:"14bc";s:52:"Classes/Resource/Placeholder/PlaceholderResource.php";s:4:"a1f1";s:42:"Classes/Slot/FileProcessingServiceSlot.php";s:4:"fa97";s:36:"Classes/Slot/ResourceFactorySlot.php";s:4:"83f1";s:26:"Configuration/Commands.php";s:4:"9a5a";s:37:"Configuration/FlexForms/Resources.xml";s:4:"6a88";s:48:"Configuration/TCA/Overrides/sys_file_storage.php";s:4:"d9c7";s:27:"Resources/Private/.htaccess";s:4:"8594";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"fa99";s:36:"Resources/Public/Icons/Extension.svg";s:4:"0ad6";s:47:"Tests/Functional/AbstractFunctionalTestCase.php";s:4:"7103";s:33:"Tests/Functional/FilefillTest.php";s:4:"a1ca";s:47:"Tests/Functional/Fixtures/Database/sys_file.xml";s:4:"2cb0";s:56:"Tests/Functional/Fixtures/Database/sys_file_metadata.xml";s:4:"bd9a";s:55:"Tests/Functional/Fixtures/Database/sys_file_storage.xml";s:4:"0679";}',
);

