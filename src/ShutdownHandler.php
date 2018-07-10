<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify;

use Denimsoft\FsNotify\Adapter\AsyncWatch;
use Denimsoft\FsNotify\Exception\ShutdownException;
use LogicException;
use Throwable;

class ShutdownHandler
{
    /**
     * @var AsyncWatch[]
     */
    private $asyncWatches = [];
    /**
     * @var bool
     */
    private $registered = false;

    public function addAsyncWatch(AsyncWatch $asyncWatch): void
    {
        $this->asyncWatches[] = $asyncWatch;
    }

    /**
     * @throws ShutdownException
     */
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

        if ( ! $exceptions) {
            return;
        }

        throw ShutdownException::create($exceptions);
    }

    public function register(): void
    {
        if ($this->registered) {
            throw new LogicException('The shutdown handler is already registered');
        }

        register_shutdown_function([$this, 'handle']);

        $this->registered = true;
    }

    public function removeAsyncWatch(AsyncWatch $asyncWatch): void
    {
        if (($off = array_search($asyncWatch, $this->asyncWatches, true)) !== false) {
            unset($this->asyncWatches[$off]);

            $this->asyncWatches = array_values($this->asyncWatches);
        }
    }
}
