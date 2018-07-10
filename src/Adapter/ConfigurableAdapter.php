<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Adapter;

use Denimsoft\FsNotify\Traits\CachesFileMetadata;

abstract class ConfigurableAdapter implements FsNotifyAdapter
{
    use CachesFileMetadata;

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(array $options = [])
    {
        $this->options = array_replace(static::getDefaultOptions(), $options);
    }

    public static function getDefaultOptions(): array
    {
        return [];
    }
}
