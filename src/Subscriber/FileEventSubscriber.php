<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Subscriber;

use Denimsoft\FsNotify\Dispatcher\FileEventDispatcher;
use Denimsoft\FsNotify\Event\FileCreatedEvent;
use Denimsoft\FsNotify\Event\FileDeletedEvent;
use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\Event\FileModifiedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface as EventSubscriber;

class FileEventSubscriber implements EventSubscriber
{
    /**
     * @var FileEventDispatcher
     */
    private $fileEventDispatcher;

    public function __construct(FileEventDispatcher $fileEventDispatcher)
    {
        $this->fileEventDispatcher = $fileEventDispatcher;
    }

    public function dispatch(FileEvent $event, string $name, EventDispatcher $events): void
    {
        $this->fileEventDispatcher->dispatch(...func_get_args());
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            FileCreatedEvent::getEventName()  => 'dispatch',
            FileModifiedEvent::getEventName() => 'dispatch',
            FileDeletedEvent::getEventName()  => 'dispatch',
        ];
    }
}
