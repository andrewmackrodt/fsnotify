<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify {
    function normalize_path(string $path): string
    {
        $normalized = preg_replace('#/{2,}#', '/', rtrim(str_replace('\\', '/', $path), '/'));
        if ($normalized && $normalized[0] !== '/') {
            $normalized = getcwd() . "/$normalized";
        }
        $split           = explode('/', $normalized);
        $normalizedSplit = [];

        foreach ($split as $segment) {
            // move up a directory
            if ($segment === '..') {
                if (count($normalizedSplit) < 1) {
                    throw new \InvalidArgumentException("Illegal path: $path");
                }
                array_pop($normalizedSplit);
                continue;
            }
            // ignore this folder
            if ($segment === '.') {
                continue;
            }
            $normalizedSplit[] = $segment;
        }

        return implode('/', $normalizedSplit) ?: '/';
    }
}

namespace Denimsoft\FsNotify\Dispatcher\Filter {
    /**
     * @param FsNotifyFilter[] $filters
     *
     * @return AllOfFilter
     */
    function allOf(array $filters): AllOfFilter
    {
        return new AllOfFilter($filters);
    }
}

namespace Denimsoft\FsNotify\Dispatcher\Filter {
    /**
     * @param FsNotifyFilter[] $filters
     *
     * @return AnyOfFilter
     */
    function anyOf(array $filters): AnyOfFilter
    {
        return new AnyOfFilter($filters);
    }
}

namespace Denimsoft\FsNotify\Dispatcher\Filter {
    /**
     * @param FsNotifyFilter $filter
     *
     * @return NotFilter
     */
    function not(FsNotifyFilter $filter): NotFilter
    {
        return new NotFilter($filter);
    }
}
