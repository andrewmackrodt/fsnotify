<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify;

use Denimsoft\FsNotify\Dispatcher\Filter\FsNotifyFilter;
use Denimsoft\FsNotify\Traits\FileEventListener;

class Watcher
{
    use FileEventListener;

    /**
     * @var FsNotifyBuilder
     */
    private $builder;

    /**
     * @var string
     */
    private $filepath;

    /**
     * @var FsNotifyFilter|null
     */
    private $filter;

    /**
     * @var bool
     */
    private $recurse;

    public function __construct(string $filepath, bool $recurse, FsNotifyBuilder $builder)
    {
        $this->filepath = normalize_path($filepath);
        $this->recurse  = $recurse;
        $this->builder  = $builder;
    }

    public function getBuilder(): FsNotifyBuilder
    {
        return $this->builder;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    /**
     * @return FsNotifyFilter|null
     */
    public function getFilter(): ?FsNotifyFilter
    {
        return $this->filter;
    }

    public function isRecursive(): bool
    {
        return $this->recurse;
    }

    public function setFilter(?FsNotifyFilter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }
}
