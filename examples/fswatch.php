#!/usr/bin/env php
<?php
/**
 * Example using the FswatchAdapter and ignoring Jetbrains IDE temp files
 * created during atomic file saves
 */

use Denimsoft\FsNotify\Adapter\FswatchAdapter;
use Denimsoft\FsNotify\Dispatcher\Filter\NameFilter;
use function Denimsoft\FsNotify\Dispatcher\Filter\not;
use Denimsoft\FsNotify\FsNotifyBuilder;

require_once __DIR__ . '/bootstrap.php';

$fsNotify = (new FsNotifyBuilder())
    ->setAdapter(new FswatchAdapter())
    ->addWatcher(__DIR__ . '/../src', true)
    ->setFilter(not(new NameFilter('*___jb_tmp___')))
    ->getBuilder()
    ->addChangeListener(debug_listener('fswatch'))
    ->createFsNotify();

$fsNotify->start();
