<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\AnyOfFilter;

class AnyOfFilterTest extends FilterTestCase
{
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

    public function testCanDispatchStopsEvaluatingAfterFirstSuccess(): void
    {
        $filters   = [];
        $filters[] = $this->createMockFilterWithReturn(false);
        $filters[] = $this->createMockFilterWithReturn(false);
        $filters[] = $this->createMockFilterWithReturn(true);
        $filters[] = $this->createMockFilterNoAssertions();

        $this->assertSame(true, $this->createFilterCallCanDispatchEvent($filters));
    }

    protected function getFilterClass(): string
    {
        return AnyOfFilter::class;
    }
}
