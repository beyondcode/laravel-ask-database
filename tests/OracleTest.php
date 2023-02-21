<?php

use BeyondCode\Oracle\Exceptions\PotentiallyUnsafeQuery;
use BeyondCode\Oracle\Oracle;

it('can query openai for a query', function () {
    $queryPrompt = version_compare(app()->version(), '10', '>=') ? 'query-prompt-l10.txt' : 'query-prompt.txt';
    $client = mockClient('POST', 'completions', [[
        'model' => 'text-davinci-003',
        'prompt' => rtrim(file_get_contents(__DIR__.'/Fixtures/'.$queryPrompt), PHP_EOL),
    ]], [completion('SELECT * FROM users;"')]);

    $oracle = new Oracle($client);
    $query = $oracle->getQuery('How many users do you have?');

    expect($query)->toBe('SELECT * FROM users;');
});

it('can evaluate the returned query', function () {
    $queryPrompt = version_compare(app()->version(), '10', '>=') ? 'query-prompt-l10.txt' : 'query-prompt.txt';
    $resultPrompt = version_compare(app()->version(), '10', '>=') ? 'result-prompt-l10.txt' : 'result-prompt.txt';

    $client = mockClient('POST', 'completions', [[
        'model' => 'text-davinci-003',
        'prompt' => rtrim(file_get_contents(__DIR__.'/Fixtures/'.$queryPrompt), PHP_EOL),
    ], [
        'model' => 'text-davinci-003',
        'prompt' => rtrim(file_get_contents(__DIR__.'/Fixtures/'.$resultPrompt), PHP_EOL),
    ]], [
        completion('SELECT COUNT(*) FROM users;"'),
        completion('There are 0 users in the database.'),
    ]);

    $oracle = new Oracle($client);
    $query = $oracle->ask('How many users do you have?');

    expect($query)->toBe('There are 0 users in the database.');
});

it('can query openai to find matching tables', function () {
    config(['ask-database.max_tables_before_performing_lookup' => 1]);

    $tablePrompt = version_compare(app()->version(), '10', '>=') ? 'table-prompt-l10.txt' : 'table-prompt.txt';

    $client = mockClient('POST', 'completions', [[
        'model' => 'text-davinci-003',
        'prompt' => rtrim(file_get_contents(__DIR__.'/Fixtures/'.$tablePrompt), PHP_EOL),
    ], [
        'model' => 'text-davinci-003',
        'prompt' => rtrim(file_get_contents(__DIR__.'/Fixtures/filtered-query-prompt.txt'), PHP_EOL),
    ], [
        'model' => 'text-davinci-003',
        'prompt' => rtrim(file_get_contents(__DIR__.'/Fixtures/filtered-result-prompt.txt'), PHP_EOL),
    ]], [
        completion('users,'),
        completion('SELECT COUNT(*) FROM users;"'),
        completion('There are 0 users in the database.'),
    ]);

    $oracle = new Oracle($client);
    $query = $oracle->ask('How many users do you have?');

    expect($query)->toBe('There are 0 users in the database.');
});

it('throws an exception with strict mode enabled', function () {
    $fixture = version_compare(app()->version(), '10', '>=') ? 'query-prompt-l10.txt' : 'query-prompt.txt';
    $client = mockClient('POST', 'completions', [[
        'model' => 'text-davinci-003',
        'prompt' => rtrim(file_get_contents(__DIR__.'/Fixtures/'.$fixture), PHP_EOL),
    ]], [
        completion('DROP TABLE users;"'),
    ]);

    $this->expectException(PotentiallyUnsafeQuery::class);

    $oracle = new Oracle($client);
    $query = $oracle->ask('How many users do you have?');

    expect($query)->toBe('There are 0 users in the database.');
});
