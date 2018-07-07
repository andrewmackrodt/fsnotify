<?php

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\AnyOfFilter;

class AnyOfFilterTest extends FilterTestCase
{
    protected function getFilterClass(): string
    {
        return AnyOfFilter::class;
    }

    public function canDispatchProvider(): array
    {
        return [
            'FalseIfAllFiltersFail'  => [[false, false, false], false],
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

    public function testCanDispatchStopsEvaluatingAfterFirstSuccess()
    {
        $filters = [];
        $filters[] = $this->createMockFilterWithReturn(false);
        $filters[] = $this->createMockFilterWithReturn(false);
        $filters[] = $this->createMockFilterWithReturn(true);
        $filters[] = $this->createMockFilterNoAssertions();

        $this->assertEquals(true, $this->createFilterCallCanDispatchEvent($filters));
    }
}
