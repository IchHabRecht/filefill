<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Resource\Handler;

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

use TYPO3\CMS\Core\Http\RequestFactory;

class PlaceholderResource extends PlaceholdResource
{
    public function __construct($_, RequestFactory $requestFactory = null)
    {
        trigger_error(
            'As the service placeholder.com was closed down, using this class is deprecated. Please use the' .
            ' replacement class \IchHabRecht\Filefill\Resource\Handler\PlaceholdResource instead.',
            E_USER_DEPRECATED
        );
        parent::__construct($_, $requestFactory);
    }
}
