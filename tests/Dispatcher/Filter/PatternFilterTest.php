<?php

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\PatternFilter;
use Denimsoft\FsNotify\Event\FileModifiedEvent;

class PatternFilterTest extends FilterTestCase
{
    protected function getFilterClass(): string
    {
        return PatternFilter::class;
    }

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
     * @param bool $expectation
     */
    public function testCanDispatchEvent(string $relFilepath, string $filterValue, bool $expectation)
    {
        $event = new FileModifiedEvent("/opt/fsnotify/$relFilepath", []);

        $this->assertEquals($expectation, $this->createFilterCallCanDispatchEvent($filterValue, $event));
    }
}
