<?php

namespace DynDNSKit\Tests\Common;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function tearDown() {
        \Mockery::close();
    }
}
