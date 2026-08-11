<?php

namespace Geekmac\PayHere\Tests;

use Geekmac\PayHere\PayHereServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [PayHereServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('payhere.merchant_id', '1211149');
        $app['config']->set('payhere.merchant_secret', 'MjkxOTQyMzYzNTQ2NTc0ODc0MzUxNTQ0ODc3MTIxMzY3NTgzOTQ');
        $app['config']->set('payhere.currency', 'LKR');
        $app['config']->set('payhere.sandbox', true);
    }
}
