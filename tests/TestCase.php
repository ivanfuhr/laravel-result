<?php

declare(strict_types=1);

namespace IvanFuhr\Result\Tests;

use IvanFuhr\Result\ResultServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ResultServiceProvider::class,
        ];
    }
}
