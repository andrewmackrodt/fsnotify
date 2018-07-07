<?php

namespace Denimsoft\FsNotify\Event;

class FileCreatedEvent extends FileEvent
{
    public static function getEventName(): string
    {
        return 'fsNotify.file.created';
    }
}
