<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Adapter;

class Factory
{
    public function __invoke(array $options = [])
    {
        if (FswatchAdapter::isSupported()) {
            return new FswatchAdapter($options);
        }

        return new PhpAdapter($options);
    }
}
