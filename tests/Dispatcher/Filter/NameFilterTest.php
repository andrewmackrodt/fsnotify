<?php

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\NameFilter;

class NameFilterTest extends FilterTestCase
{
    protected function getFilterClass(): string
    {
        return NameFilter::class;
    }

    public function canDispatchProvider(): array
    {
        return [
            'ends with'               => ['*.php', true],
            'ends with no wildcard'   => ['Filter.php', false],
            'contains'                => ['*Filter*', true],
            'contains with extension' => ['*Filter*.php', true],
            'exact match'             => ['NameFilter.php', true],
            'ignores directory'       => ['*Dispatcher*', false],
        ];
    }

    /**
     * @dataProvider canDispatchProvider
     *
     * @param string $filterValue
     * @param bool $expectation
     */
    public function testCanDispatchEvent(string $filterValue, bool $expectation)
    {
        $this->assertEquals($expectation, $this->createFilterCallCanDispatchEvent($filterValue));
    }
}
