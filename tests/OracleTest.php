<?php

use BeyondCode\Oracle\Exceptions\PotentiallyUnsafeQuery;
use BeyondCode\Oracle\Oracle;

it('can query openai for a query', function () {
    $client = mockClient('POST', 'completions', [[
        'model' => 'text-davinci-003',
        'prompt' => file_get_contents(__DIR__.'/Fixtures/query-prompt.txt'),
    ]], [completion('SELECT * FROM users;')]);

    $oracle = new Oracle($client);
    $query = $oracle->getQuery('How many users do you have?');

    expect($query)->toBe('SELECT * FROM users;');
});

it('can evaluate the returned query', function () {
    $client = mockClient('POST', 'completions', [[
        'model' => 'text-davinci-003',
        'prompt' => file_get_contents(__DIR__.'/Fixtures/query-prompt.txt'),
    ], [
        'model' => 'text-davinci-003',
        'prompt' => file_get_contents(__DIR__.'/Fixtures/result-prompt.txt'),
    ]], [
        completion('SELECT COUNT(*) FROM users;'),
        completion('There are 0 users in the database.'),
    ]);

    $oracle = new Oracle($client);
    $query = $oracle->ask('How many users do you have?');

    expect($query)->toBe('There are 0 users in the database.');
});

it('throws an exception with strict mode enabled', function () {
    $client = mockClient('POST', 'completions', [[
        'model' => 'text-davinci-003',
        'prompt' => file_get_contents(__DIR__.'/Fixtures/query-prompt.txt'),
    ]], [
        completion('DROP TABLE users;'),
    ]);

    $this->expectException(PotentiallyUnsafeQuery::class);

    $oracle = new Oracle($client);
    $query = $oracle->ask('How many users do you have?');

    expect($query)->toBe('There are 0 users in the database.');
});
