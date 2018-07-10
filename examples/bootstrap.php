<?php

declare(strict_types=1);
/**
 * Bootstrap for the examples within the directory.
 */
use Denimsoft\FsNotify\Event\FileEvent;

require_once __DIR__ . '/../vendor/autoload.php';

function debug_listener(string $listenerName): Closure
{
    return function (FileEvent $event) use ($listenerName): void {
        echo json_encode([
            'event'    => $event->getEventName(),
            'listener' => $listenerName,
            'filepath' => $event->getFilepath(),
            'metadata' => $event->getMetadata(),
        ], JSON_PRETTY_PRINT) . "\n";
    };
}
