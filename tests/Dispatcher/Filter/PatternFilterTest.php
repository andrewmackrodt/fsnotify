<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\PatternFilter;
use Denimsoft\FsNotify\Event\FileModifiedEvent;

class PatternFilterTest extends FilterTestCase
{
    public function canDispatchProvider(): array
    {
        return [
            'TrueIfBeginsWith'   => ['src/Dispatcher/Filter/PatternFilter.php', '/^src\//', true],
            'TrueIfEndsWith'     => ['src/Dispatcher/Filter/PatternFilter.php', '/\.php$/', true],
            'TrueIfContains'     => ['src/Dispatcher/Filter/PatternFilter.php', '/\/Filter\//', true],
            'FalseIfNotContains' => ['src/Dispatcher/Filter/NameFilterTest.php', '/error/', false],
            'TrueIfExactMatch'   => ['vendor/autoload.php', '/^vendor\/autoload.php$/', true],
        ];
    }

    /**
     * @dataProvider canDispatchProvider
     *
     * @param string $relFilepath
     * @param string $filterValue
     * @param bool   $expectation
     */
    public function testCanDispatchEvent(string $relFilepath, string $filterValue, bool $expectation): void
    {
        $event = new FileModifiedEvent("/opt/fsnotify/$relFilepath", []);

        $this->assertSame($expectation, $this->createFilterCallCanDispatchEvent($filterValue, $event));
    }

    protected function getFilterClass(): string
    {
        return PatternFilter::class;
    }
}
