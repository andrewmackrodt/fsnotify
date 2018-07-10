<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Event;

interface FsNotifyEvent
{
    public static function getEventName(): string;
}
