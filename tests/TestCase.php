<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Safety guard: ensure tests run ONLY on the separate MySQL test database.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.connections.mysql.database') !== 'myapp10_test') {
            throw new \RuntimeException("Refusing to run tests on a non-test database");
        }
    }
}
