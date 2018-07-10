#!/usr/bin/env php
<?php
/**
 * Example demonstrating using an async callback.
 */
use Amp\Process\Process;
use Amp\ReactAdapter\ReactAdapter;
use Denimsoft\FsNotify\Dispatcher\Filter\FsNotifyFilter;
use Denimsoft\FsNotify\Event\FileDeletedEvent;
use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\FsNotifyBuilder;

require_once __DIR__ . '/bootstrap.php';

$fsNotify = (new FsNotifyBuilder())
    ->addWatcher(sys_get_temp_dir())
    ->addAsyncChangeListener(
        // async callbacks are passed an instance of the event loop as the second parameter
        function (FileEvent $event, ReactAdapter $eventLoop) use (&$fsNotify) {
            // don't propagate the event to the global change listener
            $event->stopPropagation();

            $mtime = $event->getMetadata()['modified'] ?? 0;
            $suffix = "{$event->getEventName()}($mtime): {$event->getFilepath()}\n";
            echo $suffix;

            for ($i = 1; $i <= 5; ++$i) {
                $process = new Process('sleep 2; date');
                $process->start();
                while (($chunk = yield $process->getStdout()->read()) !== null) {
                    echo "\tasync: $chunk";
                }
            }

            // stop fsNotify
            $fsNotify->stop();
        },
        // only respond to the first non file deleted event
        new class() implements FsNotifyFilter {
            public function canDispatchEvent(FileEvent $event, string $relFilepath): bool
            {
                static $firstMetadata;

                return ! $event instanceof FileDeletedEvent &&
                    ($firstMetadata ?? ($firstMetadata = $event->getMetadata())) === $event->getMetadata();
            }
        }
    )
    ->addChangeListener(function (FileEvent $event) use (&$fsNotify): void {
        $mtime = $event->getMetadata()['modified'] ?? 0;

        echo "{$event->getEventName()}($mtime): {$event->getFilepath()}\n";
    })
    ->getBuilder()
    ->createFsNotify();

$fsNotify->start();
