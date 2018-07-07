<?php

namespace Denimsoft\FsNotify\Adapter;

use Denimsoft\FsNotify\Traits\CachesFileMetadata;

abstract class ConfigurableAdapter implements FsNotifyAdapter
{
    use CachesFileMetadata;

    /**
     * @var array
     */
    protected $options = [];

    public static function getDefaultOptions(): array
    {
        return [];
    }

    public function __construct(array $options = [])
    {
        $this->options = array_replace(static::getDefaultOptions(), $options);
    }
}
