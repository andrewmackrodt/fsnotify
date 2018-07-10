<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Event;

class FileDeletedEvent extends FileEvent
{
    public static function getEventName(): string
    {
        return 'fsNotify.file.deleted';
    }
}
