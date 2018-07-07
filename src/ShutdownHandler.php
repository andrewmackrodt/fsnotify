<?php

namespace Denimsoft\FsNotify;

use Denimsoft\FsNotify\Adapter\AsyncWatch;
use Denimsoft\FsNotify\Exception\ShutdownException;
use LogicException;
use Throwable;

class ShutdownHandler
{
    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @var AsyncWatch[]
     */
    private $asyncWatches = [];

    public function addAsyncWatch(AsyncWatch $asyncWatch): void
    {
        $this->asyncWatches[] = $asyncWatch;
    }

    public function removeAsyncWatch(AsyncWatch $asyncWatch): void
    {
        if (($off = array_search($asyncWatch, $this->asyncWatches)) !== false) {
            unset($this->asyncWatches[$off]);

            $this->asyncWatches = array_values($this->asyncWatches);
        }
    }

    public function register(): void
    {
        if ($this->registered) {
            throw new LogicException('The shutdown handler is already registered');
        }

        register_shutdown_function([$this, 'handle']);

        $this->registered = true;
    }

    public function handle(): void
    {
        $exceptions = [];

        foreach ($this->asyncWatches as $asyncWatch) {
            try {
                $asyncWatch->stop();
            } catch (Throwable $e) {
                $exceptions[] = $e;
            }
        }

        if (!$exceptions) {
            return;
        }

        throw ShutdownException::create($exceptions);
    }
}
