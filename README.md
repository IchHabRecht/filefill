# TYPO3 Extension filefill

[![Latest Stable Version](https://img.shields.io/packagist/v/ichhabrecht/filefill.svg)](https://packagist.org/packages/ichhabrecht/filefill)
[![Build Status](https://img.shields.io/travis/IchHabRecht/filefill/main.svg)](https://travis-ci.org/IchHabRecht/filefill)
[![StyleCI](https://styleci.io/repos/123628122/shield?branch=main)](https://styleci.io/repos/123628122)

Find and fetch missing local files from different remotes.

Ever tried to set up a new system as copy from an existing one? Wondered if all the files (in fileadmin) are really needed?
Ever run into the problem that a local file was missing?

Filefill fetches missing files from one or multiple remote servers to ensure you have all the files you need for the
new system.

Once the configuration is set up, fetching can be triggered by loading a page with missing files in the frontend.

The extension requires the usage of FAL api to fetch missing files. Files are stored directly in the (local) storage
folder (e.g. fileadmin). You can re-run filefill at any time by deleting the local files in the storage folder.

## Installation

Simply install the extension with Composer or the Extension Manager.

`composer require ichhabrecht/filefill`

## Usage

You need to configure resources for one or more existing file storages.

*Prerequisite: Only storages with a "Local filesystem" driver are currently supported.*

Resources for file storages can be configured using one of the two following options where the database record configuration is preferred if set. If not set, the `TYPO3_CONF_VARS` configuration is used.

### Database record configuration

- go to the root of your TYPO3 page tree (id=0)
- change to the list module (Web -> List on the left side)
- find the "File Storage" section and edit a record
- change to the tab "File Fill" and select the enable checkbox
- define the resource chain that should be used to fetch missing files

### TYPO3_CONF_VARS configuration

- given a file storage with uid 1, the configuration might look like this

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'][1] = [
    [
        'identifier' => 'domain',
        'configuration' => 'https://example.com',
    ],
    [
        'identifier' => 'domain',
        'configuration' => 'https://another-example.com',
    ],
    [
        'identifier' => 'sys_domain',
    ],
    [
        'identifier' => 'placeholder',
    ],
    [
        'identifier' => 'imagebuilder',
        'configuration' => [
            'backgroundColor' => '#FFFFFF',
            'textColor' => '#000000',
        ],
    ],
    [
        'identifier' => 'static',
        'configuration' => [
            'path/to/example/file.txt' => 'Hello world!',
            'another' => [
                'path' => [
                    'to' => [
                        'anotherFile.txt' => 'Lorem ipsum',
                        '*.youtube' => 'yiJjpKzCVE4',
                    ],
                    '*' => 'This file was found in /another/path folder.',
                ],
            ],
            '*.vimeo' => '143018597',
            '*' => 'This is some static text for all other files.',
        ],
    ],
];
```

- you don't need to configure resources that you don't want to use
- the ordering in your configuration defines the ordering of processing

## Resources

Resources define the places (url / services) where filefill tries to fetch missing files from. You can use multiple
resources to build some kind of fallback chain.

### Single domain

Fetch missing files from a fixed url.

Configuration:

- Url: Enter a valid url (incl http/https scheme)

You can use multiple single domains within one resources configuration.

### Domain records

Fetch missing files from all available site configurations. Filefill runs through all base and variant urls as long as
the file can be fetched or all domains are processed.

Configuration:

- no configuration required (the checkbox is just a field placeholder)

There is no need for multiple usage. All domains are used by default.

### Placeholder.com

Fetch a missing image from the [placeholder.com](https://placeholder.com) service. This fetches an image with the correct
resolution of the original file.

Configuration:

- no configuration required (the checkbox is just a field placeholder)

There is no need for multiple usage. This resource can be the last one in the chain but can handle image files only.

### Image builder

Create an empty image with the correct resolution of the original file. The height and width is added as a text layer.

Configuration:

- Background color: Enter a valid hex code as background color
- Text color: Enter a valid hex code as text color

### Static file

Ensure missing files will be available. By default, an empty file will be created.

Configuration:

- You can configure the content of a file by its path or extension

Please use *TypoScript syntax* for **record configuration**.

```
path/to/example/file.txt = Hello world!
another {
    path {
        to {
            anotherFile\.txt = Lorem ipsum
            *\.youtube => yiJjpKzCVE4
        }
        * = This file was found in /another/path folder.
    }
}
*\.vimeo = 143018597
* = This is some static text for all other files.
```

## Additional resources

You can add own resource handlers to fetch files from additional services.

### Registration

```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler']['identifierName'] = [
    'title' => 'Name of the resource',
    'handler' => \Vendor\Extension\Resource\ResourceHandler::class,
    'config' => [
        'label' => 'Name of the resource',
        'config' => [
            'type' => 'check',
            'default' => 1,
        ],
    ],
];
```

- title: name of the resource that is taken as backend (flex) button label
- handler: name of the class that handels the actual implementation
- config: TCA configuration for the backend (flex) field

### Handler

```
namespace Vendor\Extension\Resource;
class ResourceHandler implements \IchHabRecht\Filefill\Resource\RemoteResourceInterface
{
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return true;
    }

    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return 'file content';
    }
}
```

The handler needs to implement the interface `\IchHabRecht\Filefill\Resource\RemoteResourceInterface` and therefore has to
add both functions `hasFile` and `getFile`.

## Debugging

You can enable additional log information by configuring a filefill logger.

```
$GLOBALS['TYPO3_CONF_VARS']['LOG']['IchHabRecht']['Filefill'] = [
    'writerConfiguration' => [
        \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFileInfix' => 'filefill',
            ],
        ],
    ],
];
```

## Known issues

### 1509741907 TYPO3Fluid\Fluid\Core\ViewHelper\Exception
*Folder "[...]" does not exist.*

Filefill tries to fetch the existing file from any resource. However, due to the FAL api the exception cannot be
prevented nor handled by filefill. Try to reload the page again, the exception (for this specific file) should not occur
anymore. Please note that there might be a new exception for a new file. In this case you need to reload your page until
all files were properly created on your current system.

## Community

- Thanks to [b13](https://b13.com) that sponsored the maintenance of this extension with a sponsorship
- Thanks to [Wolfgang Wagner](https://wwagner.net) who sponsored the maintenance of this extension with a sponsorship
- Thanks to [Marcus Schwemer](https://twitter.com/MarcusSchwemer) who wrote about filefill in his blog [TYPO3worx](https://typo3worx.eu/2018/03/eight-typo3-extensions-making-developers-happy/)
- Thanks to [Thomas Löffler](https://spooner-web.de) for his ongoing support as [Patron](https://www.patreon.com/IchHabRecht)
