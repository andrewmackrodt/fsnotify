<?php

namespace Denimsoft\FsNotify\Test\Subscriber;

use Denimsoft\FsNotify\Dispatcher\FileEventDispatcher;
use Denimsoft\FsNotify\Event\FileCreatedEvent;
use Denimsoft\FsNotify\Subscriber\FileEventSubscriber;
use Denimsoft\FsNotify\Test\TestCase;
use Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;

class FileEventSubscriberTest extends TestCase
{
    public function testDispatch()
    {
        $fileEvent = new FileCreatedEvent('/tmp/test');
        $name = $fileEvent->getEventName();
        $events = Mockery::mock(EventDispatcher::class);
        $fileEventDispatcher = Mockery::mock(FileEventDispatcher::class);
        $fileEventDispatcher->shouldReceive('dispatch')->once()->with($fileEvent, $name, $events);
        $subscriber = new FileEventSubscriber($fileEventDispatcher);
        $subscriber->dispatch($fileEvent, $name, $events);
    }
}
