<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify;

use Denimsoft\FsNotify\Event\FsNotifyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;

class EventBridge
{
    /**
     * @var EventDispatcher
     */
    private $events;

    public function __construct(EventDispatcher $events)
    {
        $this->events = $events;
    }

    public function dispatch(FsNotifyEvent $event): void
    {
        $this->events->dispatch($event->getEventName(), $event);
    }
}
