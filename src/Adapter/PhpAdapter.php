<?php

namespace Denimsoft\FsNotify\Adapter;

use Amp\Delayed;
use Amp\LazyPromise;
use Denimsoft\FsNotify\Event\FileCreatedEvent;
use Denimsoft\FsNotify\Event\FileDeletedEvent;
use Denimsoft\FsNotify\Event\FileModifiedEvent;
use Denimsoft\FsNotify\EventBridge;
use Denimsoft\FsNotify\Watcher;
use RecursiveDirectoryIterator;
use SplFileInfo;
use UnexpectedValueException;

class PhpAdapter extends ConfigurableAdapter
{
    const DIRECTORY_ITERATOR_IGNORED_ERRORS = [
        'No such file or directory',
        'Permission denied',
        'SplFileInfo::getInode()',
    ];

    const OPTION_POLL_INTERVAL = 'pollInterval';

    /**
     * @var bool
     */
    private $firstLoop = true;

    public static function getDefaultOptions(): array
    {
        return [
            self::OPTION_POLL_INTERVAL => 1.00,
        ];
    }

    public static function getCapabilities(): array
    {
        return [
            self::CAPABILITY_DETECTS_PARENT_DIRECTORY_MODIFIED,
            self::CAPABILITY_RECURSES_DIRECTORY_ON_COPY,
            self::CAPABILITY_RECURSES_DIRECTORY_ON_MOVE,
        ];
    }

    public function watch(array $watchers, EventBridge $eventBridge): AsyncWatch
    {
        $running = true;

        return new AsyncWatch(
            new LazyPromise(function () use ($watchers, $eventBridge, &$running) {
                while ($running) {
                    $files = $this->getFiles($watchers);
                    $fileEvents = $this->diff($files);

                    $this->dispatch($fileEvents, $eventBridge);

                    yield new Delayed(1000.0 * $this->options[self::OPTION_POLL_INTERVAL]);
                }
            }),
            function () use (&$running) {
                $running = false;
            });
    }

    private function dispatch(array $events, EventBridge $eventBridge): void
    {
        if ($this->firstLoop) {
            $this->firstLoop = false;

            return;
        }

        foreach ($events as $event) {
            $eventBridge->dispatch($event);
        }
    }

    private function getFiles(array $watchers): array
    {
        $files = [];
        foreach ($watchers as $watcher) {
            $files = array_replace($files, $this->getFilesForWatcher($watcher));
        }

        return $files;
    }

    private function getFilesForWatcher(Watcher $watcher): array
    {
        $filepath = $watcher->getFilepath();

        clearstatcache(true, $filepath);
        
        if (is_file($filepath)) {
            $files = [];

            if (file_exists($filepath)) {
                $files[$filepath] = new SplFileInfo($filepath);
            }
        } else {
            // unlimited (null) depth if recursive
            $depth = $watcher->isRecursive() ? null : 0;
            $files = $this->getFilesRecursively($filepath, $depth);
        }

        return $files;
    }

    private function getFilesRecursively(string $dirname, int $depth = null): array
    {
        $allFiles = [];

        /** @var SplFileInfo[] $dirFiles */
        try {
            $dirFiles = iterator_to_array(
                new RecursiveDirectoryIterator(
                    $dirname,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ));
        } catch (UnexpectedValueException $e) {
            if (!array_filter(
                    self::DIRECTORY_ITERATOR_IGNORED_ERRORS,
                    function (string $error) use ($e) : string {
                        return stripos($e->getMessage(), $error) !== false;
                    })
            ) {
                throw $e;
            }

            return $allFiles;
        }

        foreach ($dirFiles as $file) {
            $allFiles[$file->getPathname()] = $file;
        }

        if ($depth === 0) {
            return $dirFiles;
        }

        if ($depth) {
            $depth--;
        }

        foreach ($dirFiles as $file) {
            if (!$file->isDir() || $file->isLink()) {
                continue;
            }

            $allFiles = array_replace($allFiles, $this->getFilesRecursively($file->getPathname(), $depth));
        }

        return $allFiles;
    }

    private function diff(array $files): array
    {
        $modified = [];
        $created  = [];
        $deleted  = array_diff_key($this->metadata, $files);

        if ($deleted) {
            $this->metadata = array_diff_key($this->metadata, $deleted);
        }

        foreach ($files as $key => $file) {
            $fileMetadata = $this->getFileMetadata($file, $oldFileMetadata);

            if ($fileMetadata === $oldFileMetadata) {
                continue;
            }

            if (!$oldFileMetadata) {
                $created[$key]  = $fileMetadata;
            } else {
                $modified[$key] = $fileMetadata;
            }
        }

        if ($this->firstLoop) {
            return []; // no need to construct events
        }

        return $this->createFileEvents($modified, $created, $deleted);
    }

    private function createFileEvents(array $modified, array $created, array $deleted): array
    {
        $events = [];

        /**
         * @var string $filename
         * @var SplFileInfo $file
         */

        foreach ($modified as $filename => $metadata) {
            $events[] = new FileModifiedEvent($filename, $metadata);
        }

        foreach ($created as $filename => $metadata) {
            $events[] = new FileCreatedEvent($filename, $metadata);
        }

        foreach ($deleted as $filename => $metadata) {
            $events[] = new FileDeletedEvent($filename, $metadata);
        }

        return $events;
    }
}
