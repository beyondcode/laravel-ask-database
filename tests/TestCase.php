<?php

namespace BeyondCode\Oracle\Tests;

use BeyondCode\Oracle\OracleServiceProvider;
use OpenAI\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'openai.api_key' => 'test',
        ]);

        config([
            'ask-database.connection' => 'testing',
        ]);

        $this->loadLaravelMigrations();
    }

    protected function getPackageProviders($app)
    {
        return [
            OracleServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-ask-database_table.php.stub';
        $migration->up();
        */
    }
}
