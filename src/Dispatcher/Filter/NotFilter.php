<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Dispatcher\Filter;

use Denimsoft\FsNotify\Event\FileEvent;

class NotFilter implements FsNotifyFilter
{
    /**
     * @var FsNotifyFilter
     */
    private $filter;

    public function __construct(FsNotifyFilter $filter)
    {
        $this->filter = $filter;
    }

    public function canDispatchEvent(FileEvent $event, string $relFilepath): bool
    {
        return ! $this->filter->canDispatchEvent(...func_get_args());
    }
}
