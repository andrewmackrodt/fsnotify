<?php

namespace Denimsoft\FsNotify\Test\Dispatcher\Filter;

use Denimsoft\FsNotify\Dispatcher\Filter\PathFilter;
use Denimsoft\FsNotify\Event\FileModifiedEvent;

class PathFilterTest extends FilterTestCase
{
    protected function getFilterClass(): string
    {
        return PathFilter::class;
    }

    public function canDispatchProvider(): array
    {
        return [
            'ends with greedy pass'       => ['src/Dispatcher/Filter/PathFilter.php', '**.php', true],
            'ends with non-greedy fail'   => ['src/Dispatcher/Filter/PathFilter.php', '*.php', false],
            'begins with greedy pass'     => ['src/Dispatcher/Filter/PathFilter.php', 'src/**', true],
            'begins with non-greedy fail' => ['src/Dispatcher/Filter/PathFilter.php', 'src/*', false],
            'begins with non-greedy pass' => ['src/helpers.php', 'src/*', true],
            'contains greedy pass'        => ['src/Dispatcher/Filter/PathFilter.php', '**/Filter/*', true],
            'contains non-greedy fail'    => ['src/Dispatcher/Filter/PathFilter.php', '*/Filter/*', false],
            'partial ends with pass'      => ['vendor/autoload.php', 'vendor/*.php', true],
            'exact match pass'            => ['vendor/autoload.php', 'vendor/autoload.php', true],
            'exact match fail'            => ['vendor/autoload.php', 'autoload.php', false],
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
