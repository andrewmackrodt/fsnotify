<?php

namespace Denimsoft\FsNotify\Dispatcher\Filter;

use Denimsoft\FsNotify\Event\FileEvent;

class PatternFilter implements FsNotifyFilter
{
    /**
     * @var string
     */
    private $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function canDispatchEvent(FileEvent $event, string $relFilepath): bool
    {
        return preg_match($this->pattern, $relFilepath);
    }
}
