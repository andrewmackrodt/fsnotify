<?php

namespace Denimsoft\FsNotify;

use Amp\ReactAdapter\ReactAdapter;
use Denimsoft\FsNotify\Adapter\AsyncWatch;
use Denimsoft\FsNotify\Adapter\FsNotifyAdapter;
use Denimsoft\FsNotify\Event\ShutdownEvent;
use LogicException;

class FsNotify
{
    /**
     * @var ReactAdapter
     */
    private $eventLoop;

    /**
     * @var FsNotifyAdapter
     */
    private $adapter;

    /**
     * @var EventBridge
     */
    private $eventBridge;

    /**
     * @var Watcher[]
     */
    private $watchers;

    /**
     * @var ShutdownHandler
     */
    private $shutdownHandler;

    /**
     * @var AsyncWatch|null
     */
    private $asyncWatch;

    /**
     * @param ReactAdapter $eventLoop
     * @param FsNotifyAdapter $adapter
     * @param EventBridge $eventBridge
     * @param Watcher[] $watchers
     */
    public function __construct(
        ReactAdapter $eventLoop,
        FsNotifyAdapter $adapter,
        EventBridge $eventBridge,
        array $watchers
    ) {
        $this->eventLoop = $eventLoop;
        $this->adapter = $adapter;
        $this->eventBridge = $eventBridge;
        $this->watchers = $watchers;
        $this->shutdownHandler = new ShutdownHandler();
    }

    public function start()
    {
        if ($this->asyncWatch) {
            throw new LogicException('FsNotify is already running');
        }

        $this->asyncWatch = $this->adapter->watch($this->watchers, $this->eventBridge);
        $this->shutdownHandler->register();
        $this->shutdownHandler->addAsyncWatch($this->asyncWatch);

        $this->eventLoop->addSignal(SIGINT, [$this, 'stop']);
        $this->eventLoop->addSignal(SIGTERM, [$this, 'stop']);

        $this->eventLoop->futureTick(function () {
            try {
                return $this->asyncWatch->start();
            } finally {
                $this->eventLoop->removeSignal(SIGINT, [$this, 'stop']);
                $this->eventLoop->removeSignal(SIGTERM, [$this, 'stop']);
            }
        });

        // ensure the event loop is running / return control
        $this->eventLoop->run();
    }

    public function stop(): void
    {
        if (!$this->asyncWatch) {
            throw new LogicException('FsNotify is not running');
        }

        $this->eventLoop->removeSignal(SIGINT, [$this, 'stop']);
        $this->eventLoop->removeSignal(SIGTERM, [$this, 'stop']);

        $this->shutdownHandler->removeAsyncWatch($this->asyncWatch);
        $this->asyncWatch->stop();
        $this->asyncWatch = null;

        $this->eventBridge->dispatch(new ShutdownEvent());
    }
}
