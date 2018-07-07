<?php

namespace Denimsoft\FsNotify\Event;

interface FsNotifyEvent
{
    public static function getEventName(): string;
}
