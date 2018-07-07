<?php

namespace Denimsoft\FsNotify\Dispatcher\Filter;

use Denimsoft\FsNotify\Event\FileEvent;

class PathFilter implements FsNotifyFilter
{
    /**
     * @var string
     */
    private $pathname;

    /**
     * Filter files with an exact filepath match. This filter supports two types
     * of wildcard expansion, greedy "\**" and non-greedy "\*". For example, to
     * match the filepath "src/Dispatcher/Filter/PathFilter.php", any of the
     * following patterns would match:
     *
     * ```
     * No wildcard
     *   "src/Dispatcher/Filter/PathFilter.php"
     *
     * Greedy wildcard
     *   "src/ ** /PathFilter.php"
     *
     * Non-Greedy wildcard
     *   "src/ * /Filter/PathFilter.php"
     * ```
     *
     * However, the following pattern would not match: "src/ * /PathFilter.php".
     *
     * @param string $pathname
     */
    public function __construct(string $pathname)
    {
        $this->pathname = $pathname;
    }

    public function canDispatchEvent(FileEvent $event, string $relFilepath): bool
    {
        // absolute pathname match
        if ($relFilepath === $this->pathname) {
            return true;
        }

        // wildcard pathname match
        $pattern = str_replace(['\*\*', '\*'], ['.*?', '[^\/]*?'], preg_quote($this->pathname, '/'));

        return preg_match("/^$pattern$/", $relFilepath);
    }
}
