<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Adapter;

use Amp\Promise;

class AsyncWatch
{
    /**
     * @var Promise
     */
    private $promise;

    /**
     * @var callable|null
     */
    private $shutdownHandler;

    public function __construct(Promise $promise, callable $shutdownHandler = null)
    {
        $this->promise         = $promise;
        $this->shutdownHandler = $shutdownHandler;
    }

    public function start(): Promise
    {
        return $this->promise;
    }

    public function stop(): void
    {
        if (($shutdownHandler = $this->shutdownHandler) !== null) {
            $shutdownHandler();
        }
    }
}
