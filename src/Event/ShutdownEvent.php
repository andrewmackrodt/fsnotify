<?php

namespace Denimsoft\FsNotify\Event;

use Symfony\Component\EventDispatcher\Event;

class ShutdownEvent extends Event implements FsNotifyEvent
{
    public static function getEventName(): string
    {
        return 'fsNotify.shutdown';
    }
}
