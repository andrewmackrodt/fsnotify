#!/usr/bin/env php
<?php
/**
 * An example to generate laravel ide-helper files on changes to configured
 * paths. The listener is smart enough to wait until it detects the end of
 * a batch of file event changes (e.g. during git checkout) before generating
 * the helper files.
 *
 * Note: this example does not run in this project and requires a laravel ^5.6
 * project with the barryvdh/laravel-ide-helper composer library.
 */
use Amp\Coroutine;
use Amp\Delayed;
use Amp\LazyPromise;
use Amp\Process\Process;
use Amp\Promise;
use function Amp\Promise\all;
use Amp\ReactAdapter\ReactAdapter as EventLoop;
use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\FsNotifyBuilder;
use Illuminate\Contracts\Console\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
chdir(base_path());

// create the builder to add watchers to
$builder = (new FsNotifyBuilder())
    ->addAsyncChangeListener(new class() {
        /**
         * Allow 100 ms of drift due to imperfect timers.
         */
        const DELAY_EPSILON = 100;
        /**
         * Wait 1000 ms before spawning.
         */
        const FILE_EVENT_ACTION_DELAY = 1000;

        /** @var float|null */
        private $lastEventTime;

        /** @var Process[]|null */
        private $processes = [];

        public function __invoke(FileEvent $event, EventLoop $loop)
        {
            $event->stopPropagation();

            // return if there have been recent file events
            if ( ! yield new Coroutine($this->isEndOfFileChangeEvents())) {
                return;
            }

            yield all([
                $this->createProcess('php artisan ide-helper:generate'),
                $this->createProcess('php artisan ide-helper:meta'),
                $this->createProcess('php artisan ide-helper:models'),
            ]);

            $this->processes = [];
        }

        private function createProcess(string $command): Promise
        {
            return new LazyPromise(function () use ($command) {
                $process = new Process($command);
                yield $process->start();
                $this->processes[] = $process;

                return new Coroutine($this->processOutput($process));
            });
        }

        private function isEndOfFileChangeEvents(): ?Generator
        {
            $lastEventTime = $this->lastEventTime ?? 0;
            $timeNow = microtime(true);
            $elapsed = 1000.0 * ($timeNow - $lastEventTime);

            // return if there have been recent file events
            if ($elapsed < self::FILE_EVENT_ACTION_DELAY - self::DELAY_EPSILON) {
                return false;
            }

            $this->lastEventTime = $timeNow;

            // wait FILE_EVENT_ACTION_DELAY ms
            yield new Delayed(self::FILE_EVENT_ACTION_DELAY);

            // return if there have been more recent file events
            if ($this->lastEventTime !== $timeNow) {
                return false;
            }

            // sanity check, return if a process is already running
            if ( ! empty($this->process)) {
                return false;
            }

            return true;
        }

        private function processOutput(Process $process): Generator
        {
            $stream = $process->getStdout();

            while (($chunk = yield $stream->read()) !== null) {
                echo $chunk;
            }
        }
    })
;

//region determine which folders to watch
$watch = [];
foreach (array_merge(
    config('ide-helper.helper_files'),
    config('ide-helper.model_locations')
) as $path) {
    if ($path[0] !== '/') {
        $path = base_path($path);
    }

    if (is_file($path)) {
        $path = dirname($path);
    }

    if (isset($watch[$path])) {
        continue;
    }

    $watch[$path] = $builder->addWatcher($path, true);
}
//endregion

// create the notifier and start listening for changes
$builder->createFsNotify()->start();
