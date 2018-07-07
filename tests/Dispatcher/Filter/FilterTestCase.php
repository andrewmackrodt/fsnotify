<?php

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\FsNotifyFilter;
use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\Event\FileModifiedEvent;
use Denimsoft\FsNotify\Test\TestCase;
use Mockery;
use Mockery\MockInterface;

abstract class FilterTestCase extends TestCase
{
    abstract protected function getFilterClass(): string;

    abstract protected function canDispatchProvider(): array;

    protected function createFilterCallCanDispatchEvent($filterValue, FileEvent $event = null)
    {
        $filter = $this->createFilter($filterValue);
        $event = $event ?? new FileModifiedEvent('/opt/fsnotify/src/Dispatcher/Filter/NameFilter.php', []);
        $relFilepath = substr($event->getFilepath(), strlen('/opt/fsnotify/'));

        return $filter->canDispatchEvent($event, $relFilepath);
    }

    protected function createFilter($filterValue): FsNotifyFilter
    {
        $class = $this->getFilterClass();

        return new $class($filterValue);

    }

    /**
     * @param bool $return
     * @param int $times
     *
     * @return FsNotifyFilter|MockInterface
     */
    protected function createMockFilterWithReturn(bool $return, int $times = 1): FsNotifyFilter
    {
        $filter = Mockery::mock(FsNotifyFilter::class);
        $filter->shouldReceive('canDispatchEvent')->times($times)->andReturn($return);

        return $filter;
    }

    /**
     * @return FsNotifyFilter|MockInterface
     */
    protected function createMockFilterNoAssertions(): FsNotifyFilter
    {
        $filter = Mockery::mock(FsNotifyFilter::class);
        $filter->shouldReceive('canDispatchEvent')->never();

        return $filter;
    }
}
