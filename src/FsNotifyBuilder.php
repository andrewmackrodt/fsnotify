<?php

namespace Denimsoft\FsNotify;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;
use Denimsoft\FsNotify\Adapter\FsNotifyAdapter;
use Denimsoft\FsNotify\Adapter\PhpAdapter;
use Denimsoft\FsNotify\Dispatcher\GlobalDispatcher;
use Denimsoft\FsNotify\Dispatcher\WatcherDispatcher;
use Denimsoft\FsNotify\Event\ShutdownEvent;
use Denimsoft\FsNotify\Subscriber\FileEventSubscriber;
use Denimsoft\FsNotify\Traits\FileEventListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FsNotifyBuilder
{
    use FileEventListener;

    /**
     * @var ReactAdapter|null
     */
    private $eventLoop;

    /**
     * @var FsNotifyAdapter|null
     */
    private $adapter;

    /**
     * @var EventDispatcherInterface|null
     */
    private $events;

    /**
     * @var Watcher[]
     */
    private $watchers = [];

    public function createFsNotify(): FsNotify
    {
        $fsNotify = new FsNotify(
            $this->eventLoop  ?? ($this->eventLoop = $this->createDefaultEventLoop()),
            $this->adapter    ?? ($this->adapter   = $this->createDefaultAdapter()),
            new EventBridge(
                $this->events ?? ($this->events    = $this->createDefaultEventDispatcher())),
            $this->watchers);

        $this->registerEventHandlers($this->events);

        return $fsNotify;
    }

    public function setEventLoop(ReactAdapter $eventLoop): self
    {
        $this->eventLoop = $eventLoop;

        return $this;
    }

    private function createDefaultEventLoop(): ReactAdapter
    {
        return new ReactAdapter(Loop::get());
    }

    public function setAdapter(FsNotifyAdapter $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    private function createDefaultAdapter(): FsNotifyAdapter
    {
        return new PhpAdapter();
    }

    public function setEventDispatcher(EventDispatcherInterface $events): self
    {
        $this->events = $events;

        return $this;
    }

    private function createDefaultEventDispatcher(): EventDispatcherInterface
    {
        return new EventDispatcher();
    }

    public function addWatcher(string $filepath, bool $recurse = false): Watcher
    {
        $this->watchers[] = new Watcher($filepath, $recurse, $this);

        return end($this->watchers);
    }

    /**
     * @param EventDispatcherInterface $events
     * @param ReactAdapter $eventLoop
     *
     * @return EventSubscriberInterface[]
     */
    private function subscribeWatcherFileEvents(EventDispatcherInterface $events, ReactAdapter $eventLoop): array
    {
        $subscribers = [];

        foreach ($this->watchers as $watcher) {
            $watcherDispatcher = new WatcherDispatcher($watcher, $eventLoop);
            $subscriber = new FileEventSubscriber($watcherDispatcher);
            $subscribers[] = $subscriber;

            $events->addSubscriber($subscriber);
        }

        return $subscribers;
    }

    /**
     * @param EventDispatcherInterface $events
     * @param ReactAdapter $eventLoop
     *
     * @return EventSubscriberInterface[]
     */
    private function subscribeGlobalFileEvents(EventDispatcherInterface $events, ReactAdapter $eventLoop): array
    {
        $globalDispatcher = new GlobalDispatcher($this->getListeners(), $this->watchers, $eventLoop);
        $subscriber = new FileEventSubscriber($globalDispatcher);
        $events->addSubscriber($subscriber);

        return [$subscriber];
    }

    private function registerEventHandlers(EventDispatcherInterface $events): void
    {
        $subscribers = array_merge(
            $this->subscribeWatcherFileEvents($events, $this->eventLoop),
            $this->subscribeGlobalFileEvents($events, $this->eventLoop)
        );

        $events->addListener(
            ShutdownEvent::getEventName(),
            $shutdownEvent = function (
                ShutdownEvent $event,
                string $name,
                EventDispatcher $events
            ) use ($subscribers, &$shutdownEvent) {
                foreach ($subscribers as $subscriber) {
                    $events->removeSubscriber($subscriber);
                }

                $events->removeListener(ShutdownEvent::getEventName(), $shutdownEvent);
            }
        );
    }
}
