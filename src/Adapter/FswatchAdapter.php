<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Adapter;

use Amp\Coroutine;
use Amp\LazyPromise;
use Amp\Process\Process;
use Amp\Promise;
use function Amp\Promise\all;
use Denimsoft\FsNotify\Event\FileCreatedEvent;
use Denimsoft\FsNotify\Event\FileDeletedEvent;
use Denimsoft\FsNotify\Event\FileModifiedEvent;
use Denimsoft\FsNotify\EventBridge;
use Denimsoft\FsNotify\Watcher;
use Generator;
use RuntimeException;

class FswatchAdapter extends ConfigurableAdapter
{
    const OPTION_STAT_ERROR_TIMEOUT = 'statErrorTimeout';

    private $statErrors = [];

    public static function getCapabilities(): array
    {
        return [];
    }

    public static function getDefaultOptions(): array
    {
        return [
            self::OPTION_STAT_ERROR_TIMEOUT => 2.00,
        ];
    }

    public static function isSupported(): bool
    {
        exec('fswatch --version 2>/dev/null', $output, $exitCode);

        return $exitCode === 0;
    }

    public function watch(array $watchers, EventBridge $eventBridge): AsyncWatch
    {
        /** @var Process[] $processes */
        $processes = [];

        return new AsyncWatch(
            (function () use ($watchers, $eventBridge, &$processes) {
                /** @var Promise[] $promises */
                $promises = [];

                foreach ($watchers as $watcher) {
                    $process = $this->createProcess($watcher);
                    $processes[] = $process;

                    $promises[] = new LazyPromise(function () use ($eventBridge, $process) {
                        yield $process->start();

                        return new Coroutine($this->processOutput($eventBridge, $process));
                    });
                }

                return all($promises);
            })(),
            function () use ($processes): void {
                foreach ($processes as $process) {
                    if ( ! $process->isRunning()) {
                        return;
                    }
                    $process->kill();
                }
            }
        );
    }

    private function createEventsFromAdapterEvents(array $adapterEvents): array
    {
        $events = [];

        foreach ($adapterEvents as $filepath => $fileAdapterEvents) {
            $fileAdapterEvents = array_values(array_unique(array_filter(
                $fileAdapterEvents,
                function (string $adapterEvent) {
                    return $adapterEvent === 'Created' || $adapterEvent === 'Removed';
                }
            )));

            // continue if the file was created and removed, it is probably
            // a temporary file, e.g. atomic file saving
            if ($fileAdapterEvents === ['Created', 'Removed']) {
                continue;
            }

            // the inverse of the above, i.e. a file modification
            if ($fileAdapterEvents === ['Removed', 'Created']) {
                $fileAdapterEvent = 'Updated';
            } else {
                $fileAdapterEvent = end($fileAdapterEvents);
            }

            try {
                switch ($fileAdapterEvent) {
                    case 'Created':
                        $events[] = new FileCreatedEvent($filepath, $this->getFileMetadata($filepath));
                        break;
                    case 'Removed':
                        if (isset($this->statErrors[$filepath])) {
                            $elapsed = microtime(true) - $this->statErrors[$filepath];
                            unset($this->statErrors[$filepath]);
                            if ($elapsed < $this->options[self::OPTION_STAT_ERROR_TIMEOUT]) {
                                break;
                            }
                        }
                        $events[] = new FileDeletedEvent($filepath, $this->metadata[$filepath] ?? []);
                        break;
                    default:
                        $metadata = $this->getFileMetadata($filepath, $oldMetadata);
                        if ($metadata !== $oldMetadata) {
                            $events[] = new FileModifiedEvent($filepath, $this->getFileMetadata($filepath));
                        }
                }
            } catch (RuntimeException $e) {
                if (strpos($e->getMessage(), 'SplFileInfo::getInode(): stat failed') === false) {
                    throw $e;
                }

                $this->statErrors[$filepath] = microtime(true);
            }
        }

        return $events;
    }

    private function createProcess(Watcher $watcher): Process
    {
        return new Process(sprintf(
            "fswatch %s --event-flags --event-flag-separator='|' %s",
            $watcher->isRecursive() ? '--recursive' : '',
            escapeshellarg($watcher->getFilepath())
        ));
    }

    private function getFileAdapterEvents(string $output): array
    {
        preg_match_all(
            '/^(?<filepath>.+?) (?:[a-z]+\|)*(?<event>Updated|Created|Removed|AttributeModified)(?:\|[a-z]+)*$/im',
            $output,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches[0]['filepath'])) {
            return [];
        }

        $adapterEvents = [];

        foreach ($matches as $match) {
            $adapterEvent = &$adapterEvents[$match['filepath']];
            if ($adapterEvent === null) {
                $adapterEvent = [];
            }
            $adapterEvent = array_merge($adapterEvent, [$match['event']]);
        }
        unset($adapterEvent);

        return $adapterEvents;
    }

    private function processOutput(EventBridge $eventBridge, Process $process): Generator
    {
        $stream = $process->getStdout();

        while (($chunk = yield $stream->read()) !== null) {
            $adapterEvents = $this->getFileAdapterEvents($chunk);
            $fileEvents    = $this->createEventsFromAdapterEvents($adapterEvents);

            foreach ($fileEvents as $fileEvent) {
                $eventBridge->dispatch($fileEvent);
            }
        }
    }
}
