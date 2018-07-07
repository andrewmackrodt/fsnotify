<?php

namespace Denimsoft\FsNotify\Traits;

use Denimsoft\FsNotify\Dispatcher\Filter\FsNotifyFilter;
use Denimsoft\FsNotify\Event\FileCreatedEvent;
use Denimsoft\FsNotify\Event\FileDeletedEvent;
use Denimsoft\FsNotify\Event\FileModifiedEvent;

trait FileEventListener
{
    /**
     * @var array
     */
    private $listeners = [];

    public function getListeners(string $eventName = null): array
    {
        $listeners = $this->listeners;
        if ($eventName) {
            $listeners = $this->listeners[$eventName] ?? [];
        }

        return $listeners;
    }

    public function addChangeListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addCreatedListener(...func_get_args())
            ->addModifiedListener(...func_get_args())
            ->addDeletedListener(...func_get_args());
    }

    public function addAsyncChangeListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addAsyncCreatedListener(...func_get_args())
            ->addAsyncModifiedListener(...func_get_args())
            ->addAsyncDeletedListener(...func_get_args());
    }

    public function addCreatedListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addListener(FileCreatedEvent::getEventName(), ...func_get_args());
    }

    public function addAsyncCreatedListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addListener(FileCreatedEvent::getEventName(), $callback, $filter, true);
    }

    public function addModifiedListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addListener(FileModifiedEvent::getEventName(), ...func_get_args());
    }

    public function addAsyncModifiedListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addListener(FileModifiedEvent::getEventName(), $callback, $filter, true);
    }

    public function addDeletedListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addListener(FileDeletedEvent::getEventName(), ...func_get_args());
    }

    public function addAsyncDeletedListener(callable $callback, FsNotifyFilter $filter = null): self
    {
        return $this->addListener(FileDeletedEvent::getEventName(), $callback, $filter, true);
    }

    private function addListener(
        string $eventName,
        callable $callback,
        FsNotifyFilter $filter = null,
        bool $async = false
    ): self {
        $this->listeners[$eventName][] = [
            'callback' => $callback,
            'filter'   => $filter,
            'async'    => $async,
        ];

        return $this;
    }
}
