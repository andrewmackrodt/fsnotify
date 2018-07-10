<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\NotFilter;

class NotFilterTest extends FilterTestCase
{
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
    public function testCanDispatchEvent(bool $mockFilterResult, bool $expectation): void
    {
        $filter = $this->createMockFilterWithReturn($mockFilterResult);

        $this->assertSame($expectation, $this->createFilterCallCanDispatchEvent($filter));
    }

    protected function getFilterClass(): string
    {
        return NotFilter::class;
    }
}
