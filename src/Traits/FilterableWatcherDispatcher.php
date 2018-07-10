<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Traits;

use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\Watcher;

trait FilterableWatcherDispatcher
{
    private function getListeners(Watcher $watcher, FileEvent $event): array
    {
        $relFilepath = substr($event->getFilepath(), strlen($watcher->getFilepath()) + 1);

        return array_filter(
            $watcher->getListeners($event->getEventName()),

            function (array $listener) use ($event, $relFilepath) {
                return ! $listener['filter'] || $listener['filter']->canDispatchEvent($event, $relFilepath);
            }
        );
    }

    private function isFiltered(Watcher $watcher, FileEvent $event): bool
    {
        $relFilepath = substr($event->getFilepath(), strlen($watcher->getFilepath()) + 1);

        if (($filter = $watcher->getFilter()) !== null) {
            if ( ! $filter->canDispatchEvent($event, $relFilepath)) {
                return true;
            }
        }

        return false;
    }

    private function isWatcherForEvent(Watcher $watcher, FileEvent $event): bool
    {
        // continue if the watcher does not apply to this filepath
        if (strpos($event->getFilepath(), $watcher->getFilepath()) === false) {
            return false;
        }

        $relFilepath = substr($event->getFilepath(), strlen($watcher->getFilepath()) + 1);

        // continue if the watcher is non-recursive and the filepath contains at least two directories
        if ( ! $watcher->isRecursive() && preg_match_all('#/#', $relFilepath) > 1) {
            return false;
        }

        return true;
    }
}
