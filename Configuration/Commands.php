<?php

declare(strict_types=1);

return [
    'filefill:delete' => [
        'class' => \IchHabRecht\Filefill\Command\DeleteCommand::class,
    ],
    'filefill:reset' => [
        'class' => \IchHabRecht\Filefill\Command\ResetCommand::class,
    ],
];
