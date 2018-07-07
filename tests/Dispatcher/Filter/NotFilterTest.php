<?php

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\NotFilter;

class NotFilterTest extends FilterTestCase
{
    protected function getFilterClass(): string
    {
        return NotFilter::class;
    }

    public function canDispatchProvider(): array
    {
        return [
            'TrueIfFilterFails'   => [false, true],
            'FalseIfFilterPasses' => [true, false],
        ];
    }

    /**
     * @dataProvider canDispatchProvider
     *
     * @param bool $mockFilterResult
     * @param bool $expectation
     */
    public function testCanDispatchEvent(bool $mockFilterResult, bool $expectation)
    {
        $filter = $this->createMockFilterWithReturn($mockFilterResult);

        $this->assertEquals($expectation, $this->createFilterCallCanDispatchEvent($filter));
    }
}
