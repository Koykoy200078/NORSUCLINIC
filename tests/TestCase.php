<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function tearDown(): void
    {
        // The consultation PDF export calls set_time_limit(120). In a web request that ends with the request; in the one
        // process that runs every test it becomes a 120-second limit for ALL the tests that follow and kills the run.
        set_time_limit(0);

        parent::tearDown();
    }
}
