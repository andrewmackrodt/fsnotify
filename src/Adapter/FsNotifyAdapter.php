<?php

namespace Denimsoft\FsNotify\Adapter;

use Denimsoft\FsNotify\EventBridge;
use Denimsoft\FsNotify\Watcher;

interface FsNotifyAdapter
{
    /**
     * Fires a FileModifiedEvent for the parent directory of a modified file.
     */
    const CAPABILITY_DETECTS_PARENT_DIRECTORY_MODIFIED = 'detectsParentDirectoryModified';

    /**
     * Fires FileCreatedEvent for all files contained within a copied directory.
     * If the capability is missing, file events will behave as if a new watcher
     * was started to monitor the directory, i.e. new events after the initial
     * copy operation will be detected.
     */
    const CAPABILITY_RECURSES_DIRECTORY_ON_COPY  = 'recursesDirectoryOnCopy';

    /**
     * Fires FileDeletedEvent and FileCreatedEvent events for all files contained
     * within moved directories. If the capability is missing, file events will
     * behave as if a new watcher was started to monitor the directory, i.e. new
     * events after the initial move operation will be detected.
     */
    const CAPABILITY_RECURSES_DIRECTORY_ON_MOVE  = 'recursesDirectoryOnMove';

    /**
     * Returns an array of adapter capabilities
     *
     * @return string[]
     */
    public static function getCapabilities(): array;

    /**
     * @param Watcher[] $watchers
     * @param EventBridge $eventBridge
     *
     * @return AsyncWatch
     */
    public function watch(array $watchers, EventBridge $eventBridge): AsyncWatch;
}
