# FsNotify

[![Build Status](https://img.shields.io/travis/andrewmackrodt/fsnotify/develop.svg?style=flat-square)](https://travis-ci.com/andrewmackrodt/fsnotify)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

Event based file system watcher for PHP. FsNotify is fully† async and event based using [Amp](https://github.com/amphp/amp) and [Symfony\EventDispatcher](https://github.com/symfony/event-dispatcher).

† excluding stat operations to determine file metadata

## Usage

**Quick start**

```php
<?php

use Denimsoft\FsNotify\Event\FileEvent;
use Denimsoft\FsNotify\FsNotifyBuilder;

require_once __DIR__ . '/vendor/autoload.php';

$fsNotify = (new FsNotifyBuilder())
    ->addWatcher(__DIR__ . '/src', true)
    ->getBuilder()
    ->addChangeListener(function (FileEvent $event) {
        echo sprintf(
                "[%s] (%s) \"%s\" %s\n",
                date('c'),
                $event->getEventName(),
                $event->getFilepath(),
                json_encode($event->getMetadata())
            );
    })
    ->createFsNotify();

$fsNotify->start();
```

**Advanced usage**

FsNotify supports monitoring multiple paths with configurable callbacks and filters.
See the examples directory for complete usage:

- [examples/standard.php](examples/standard.php)
- [examples/fswatch.php](examples/fswatch.php)
- [examples/async.php](examples/async.php)
- [examples/advanced.php](examples/advanced.php)
- [examples/laravel-ide-helper.php](examples/laravel-ide-helper.php)

This library was developed with the [laravel-ide-helper](examples/laravel-ide-helper.php)
use case in mind, it highlights a moderately complicated yet useful usage scenario.

**Adapters**

**PhpAdapter** - the default adapter which uses file polling to detect changes. The adapter
defaults to a configurable polling interval of 1 second. It is sufficient for monitoring
a small amount of files.

**FswatchAdapter** - fswatch requires the GNU CLI tool fswatch, it's more performant than
PhpAdapter and detects file changes almost instantaneously. It is preferred in most cases
but can be slow to propagate large amounts of file deletions (such as deleting the vendor
or node_modules folder of a PHP or JavaScript project respectively); however, this use case
is considered atypical and should not affect most users. It also does not initially recurse
into a copied or moved directory but this is once again not typically a requirement when
watching for changes to Laravel models or static asset directories before executing a
third-party tool.
