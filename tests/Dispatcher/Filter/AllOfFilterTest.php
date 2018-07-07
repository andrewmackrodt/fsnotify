<?php

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\AllOfFilter;

class AllOfFilterTest extends FilterTestCase
{
    protected function getFilterClass(): string
    {
        return AllOfFilter::class;
    }

    public function canDispatchProvider(): array
    {
        return [
            'TrueIfAllFiltersPass'   => [[true, true, true], true],
            'TrueIfEmpty'            => [[], true],
            'FalseIfOnlyFilterFails' => [[false], false],
            'TrueIfOnlyFilterPasses' => [[true], true],
        ];
    }

    /**
     * @dataProvider canDispatchProvider
     *
     * @param bool[] $filterValues
     * @param bool $expectation
     */
    public function testCanDispatchEvent(array $filterValues, bool $expectation)
    {
        $filters = [];

        foreach ($filterValues as $mockReturnResult) {
            $filters[] = $this->createMockFilterWithReturn($mockReturnResult);
        }

        $this->assertEquals($expectation, $this->createFilterCallCanDispatchEvent($filters));
    }

    public function testCanDispatchStopsEvaluatingAfterFirstFailure()
    {
        $filters = [];
        $filters[] = $this->createMockFilterWithReturn(true);
        $filters[] = $this->createMockFilterWithReturn(true);
        $filters[] = $this->createMockFilterWithReturn(false);
        $filters[] = $this->createMockFilterNoAssertions();

        $this->assertEquals(false, $this->createFilterCallCanDispatchEvent($filters));
    }
}
