<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Dispatcher\Filter;

use Denimsoft\FsNotify\Event\FileEvent;

class NameFilter implements FsNotifyFilter
{
    /**
     * @var string
     */
    private $filename;

    /**
     * Filter files with an exact filename match. One or more wildcards may be
     * specified to perform searches such as "\*.php" and "File\*Event.php".
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function canDispatchEvent(FileEvent $event, string $relFilepath): bool
    {
        // absolute filename match
        if ($event->getFilename() === $this->filename) {
            return true;
        }

        // wildcard filename match
        $pattern = str_replace(['\*\*', '\*'], ['.*?', '[^\/]*?'], preg_quote($this->filename, '/'));

        return (bool) preg_match("/^$pattern$/", $event->getFilename());
    }
}
