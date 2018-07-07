<?php

namespace Denimsoft\FsNotify\Dispatcher;

use Amp\ReactAdapter\ReactAdapter;
use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\Traits\FilterableWatcherDispatcher;
use Denimsoft\FsNotify\Watcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;

class GlobalDispatcher implements FileEventDispatcher
{
    use FilterableWatcherDispatcher;

    /**
     * @var callback[]
     */
    private $listeners;

    /**
     * @var Watcher[]
     */
    private $watchers;

    /**
     * @var ReactAdapter
     */
    private $eventLoop;

    public function __construct(array $listeners = [], array $watchers, ReactAdapter $eventLoop)
    {
        $this->listeners = $listeners;
        $this->watchers  = $watchers;
        $this->eventLoop = $eventLoop;
    }

    public function dispatch(FileEvent $event, string $eventName, EventDispatcher $events): void
    {
        if ($event->isPropagationStopped()
            || (($watcher = $this->getFirstWatcherForEvent($event)) === null)
        ) {
            return;
        }

        $listeners = $this->listeners[$event->getEventName()] ?? [];
        $relFilepath = substr($event->getFilepath(), strlen($watcher->getFilepath()) + 1);

        foreach ($listeners as $listener) {
            // continue if the listener filter does not match
            if ($listener['filter'] && !$listener['filter']->canDispatchEvent($event, $relFilepath)) {
                continue;
            }

            if ($listener['async']) {
                $this->eventLoop->futureTick(function () use ($listener, $event) {
                    return $listener['callback']($event, $this->eventLoop);
                });
            } else {
                $listener['callback']($event);
            }
        }
    }

    private function getFirstWatcherForEvent(FileEvent $event): ?Watcher
    {
        foreach ($this->watchers as $watcher) {
            if ($this->isWatcherForEvent($watcher, $event) && !$this->isFiltered($watcher, $event)) {
                return $watcher;
            }
        }

        return null;
    }
}
