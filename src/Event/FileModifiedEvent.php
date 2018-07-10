<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Event;

class FileModifiedEvent extends FileEvent
{
    public static function getEventName(): string
    {
        return 'fsNotify.file.modified';
    }
}
