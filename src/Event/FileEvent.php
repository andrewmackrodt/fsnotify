<?php

namespace Denimsoft\FsNotify\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class FileEvent extends Event implements FsNotifyEvent
{
    /**
     * @var string
     */
    private $filepath;

    /**
     * @var array
     */
    private $metadata;

    public function __construct(string $filepath, array $metadata = [])
    {
        $this->filepath = $filepath;
        $this->metadata = $metadata;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function getFilename(): string
    {
        return basename($this->filepath);
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
