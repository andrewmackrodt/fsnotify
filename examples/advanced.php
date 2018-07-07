#!/usr/bin/env php
<?php
/**
 * Example demonstrating multiple watchers and listeners using different filters
 */

use Denimsoft\FsNotify\Dispatcher\Filter\NameFilter;
use Denimsoft\FsNotify\Dispatcher\Filter\PathFilter;
use Denimsoft\FsNotify\FsNotifyBuilder;

use function Denimsoft\FsNotify\Dispatcher\Filter\anyOf;

require_once __DIR__ . '/bootstrap.php';

$fsNotify = (new FsNotifyBuilder())
    // watch for changes to ".php" files in the "src" directory
    ->addWatcher(__DIR__ . '/../src', true)
        ->setFilter(new NameFilter('*.php'))
        ->addChangeListener(debug_listener('php'))
        ->addChangeListener(debug_listener('php.adapter'), new PathFilter('Adapter/**'))
        ->getBuilder()

    // watch for changes to "composer.json" and "phpunit.xml" files in the "vendor" directory
    ->addWatcher(__DIR__ . '/../vendor', true)
        ->setFilter(anyOf([new NameFilter('composer.json'), new NameFilter('phpunit.xml')]))
        ->addChangeListener(debug_listener('vendor'))
        ->getBuilder()

    // listen for all changes from all watchers
    ->addChangeListener(debug_listener('global'))

    // create the fsNotify object
    ->createFsNotify();

$fsNotify->start();
