<?php

declare(strict_types=1);

namespace Denimsoft\FsNotify\Test;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        Mockery::close();
    }
}
