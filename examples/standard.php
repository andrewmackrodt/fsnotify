#!/usr/bin/env php
<?php
/**
 * Example demonstrating a single watcher and callback
 */

use Denimsoft\FsNotify\FsNotifyBuilder;

require_once __DIR__ . '/bootstrap.php';

$fsNotify = (new FsNotifyBuilder())
    ->addWatcher(__DIR__ . '/../src', true)
    ->getBuilder()
    ->addChangeListener(debug_listener('global'))
    ->createFsNotify();

$fsNotify->start();
