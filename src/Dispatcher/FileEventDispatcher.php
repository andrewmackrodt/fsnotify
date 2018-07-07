<?php

namespace Denimsoft\FsNotify\Dispatcher;

use Denimsoft\FsNotify\Event\FileEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;

interface FileEventDispatcher
{
    public function dispatch(FileEvent $event, string $eventName, EventDispatcher $events): void;
}
