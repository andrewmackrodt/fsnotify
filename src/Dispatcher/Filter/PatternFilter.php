<?php

declare(strict_types=1);

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
        return (bool) preg_match($this->pattern, $relFilepath);
    }
}
