<?php

namespace Denimsoft\FsNotify\Dispatcher\Filter;

use Denimsoft\FsNotify\Event\FileEvent;

class AllOfFilter implements FsNotifyFilter
{
    /**
     * @var FsNotifyFilter[]
     */
    private $filters;

    /**
     * @param FsNotifyFilter[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function canDispatchEvent(FileEvent $event, string $relFilepath): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->canDispatchEvent($event, $relFilepath)) {
                return false;
            }
        }

        return true;
    }
}
