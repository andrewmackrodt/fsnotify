<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Dispatcher\Filter;

use Denimsoft\FsNotify\Event\FileEvent;

class TypeFilter implements FsNotifyFilter
{
    const TYPE_DIRECTORY = 'directory';
    const TYPE_FILE      = 'file';

    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function canDispatchEvent(FileEvent $event, string $relFilepath): bool
    {
        return ($event->getMetadata()['filetype'] ?? null) === $this->type;
    }
}
