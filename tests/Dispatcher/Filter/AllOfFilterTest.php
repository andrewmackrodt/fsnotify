<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\AllOfFilter;

class AllOfFilterTest extends FilterTestCase
{
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
     * @param bool   $expectation
     */
    public function testCanDispatchEvent(array $filterValues, bool $expectation): void
    {
        $filters = [];

        foreach ($filterValues as $mockReturnResult) {
            $filters[] = $this->createMockFilterWithReturn($mockReturnResult);
        }

        $this->assertSame($expectation, $this->createFilterCallCanDispatchEvent($filters));
    }

    public function testCanDispatchStopsEvaluatingAfterFirstFailure(): void
    {
        $filters   = [];
        $filters[] = $this->createMockFilterWithReturn(true);
        $filters[] = $this->createMockFilterWithReturn(true);
        $filters[] = $this->createMockFilterWithReturn(false);
        $filters[] = $this->createMockFilterNoAssertions();

        $this->assertSame(false, $this->createFilterCallCanDispatchEvent($filters));
    }

    protected function getFilterClass(): string
    {
        return AllOfFilter::class;
    }
}
