<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Dispatcher\Filter;

use Denimsoft\FsNotify\Event\FileEvent;

interface FsNotifyFilter
{
    /**
     * @param FileEvent $event       The file event
     * @param string    $relFilepath The filepath relative to the watch directory
     *
     * @return bool
     */
    public function canDispatchEvent(FileEvent $event, string $relFilepath): bool;
}
