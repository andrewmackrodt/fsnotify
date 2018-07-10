<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\TypeFilter;
use Denimsoft\FsNotify\Event\FileModifiedEvent;

class TypeFilterTest extends FilterTestCase
{
    public function canDispatchProvider(): array
    {
        return [
            'TrueIfSameFile'          => ['file', 'file', true],
            'FalseIfNotSameFile'      => ['file', 'directory', false],
            'TrueIfSameDirectory'     => ['directory', 'directory', true],
            'FalseIfNotSameDirectory' => ['directory', 'file', false],
        ];
    }

    /**
     * @dataProvider canDispatchProvider
     *
     * @param string $filterValue
     * @param string $filetype
     * @param bool   $expectation
     */
    public function testCanDispatchEvent(string $filterValue, string $filetype, bool $expectation): void
    {
        $event = new FileModifiedEvent('/opt/fsnotify/vendor/autoload.php', ['filetype' => $filetype]);

        $this->assertSame($expectation, $this->createFilterCallCanDispatchEvent($filterValue, $event));
    }

    protected function getFilterClass(): string
    {
        return TypeFilter::class;
    }
}
