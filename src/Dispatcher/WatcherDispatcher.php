<?php

namespace Denimsoft\FsNotify\Dispatcher;

use Amp\ReactAdapter\ReactAdapter;
use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\Traits\FilterableWatcherDispatcher;
use Denimsoft\FsNotify\Watcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;

class WatcherDispatcher implements FileEventDispatcher
{
    use FilterableWatcherDispatcher;

    /**
     * @var Watcher
     */
    private $watcher;

    /**
     * @var ReactAdapter
     */
    private $eventLoop;

    public function __construct(Watcher $watcher, ReactAdapter $eventLoop)
    {
        $this->watcher   = $watcher;
        $this->eventLoop = $eventLoop;
    }

    public function dispatch(FileEvent $event, string $eventName, EventDispatcher $events): void
    {
        if ($event->isPropagationStopped()
            || !$this->isWatcherForEvent($this->watcher, $event)
            || $this->isFiltered($this->watcher, $event)
        ) {
            return;
        }

        foreach ($this->getListeners($this->watcher, $event) as $listener) {
            if ($event->isPropagationStopped()) {
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
}
