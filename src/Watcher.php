<?php

namespace Denimsoft\FsNotify;

use Denimsoft\FsNotify\Dispatcher\Filter\FsNotifyFilter;
use Denimsoft\FsNotify\Traits\FileEventListener;

class Watcher
{
    use FileEventListener;

    /**
     * @var string
     */
    private $filepath;

    /**
     * @var bool
     */
    private $recurse;

    /**
     * @var FsNotifyBuilder
     */
    private $builder;

    /**
     * @var FsNotifyFilter|null
     */
    private $filter;

    public function __construct(string $filepath, bool $recurse, FsNotifyBuilder $builder)
    {
        $this->filepath = normalize_path($filepath);
        $this->recurse = $recurse;
        $this->builder = $builder;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function isRecursive(): bool
    {
        return $this->recurse;
    }

    public function getBuilder(): FsNotifyBuilder
    {
        return $this->builder;
    }

    /**
     * @return FsNotifyFilter|null
     */
    public function getFilter(): ?FsNotifyFilter
    {
        return $this->filter;
    }

    public function setFilter(?FsNotifyFilter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }
}
