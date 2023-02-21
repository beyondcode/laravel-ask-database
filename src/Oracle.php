<?php

namespace BeyondCode\Oracle;

use BeyondCode\Oracle\Exceptions\PotentiallyUnsafeQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenAI\Client;

class Oracle
{
    protected string $connection;

    public function __construct(protected Client $client)
    {
        $this->connection = config('ask-database.connection');
    }

    public function ask(string $question): string
    {
        $query = $this->getQuery($question);

        $result = json_encode($this->evaluateQuery($query));

        $prompt = $this->buildPrompt($question, $query, $result);

        return $this->queryOpenAi($prompt, "\n", 0.7);
    }

    public function getQuery(string $question): string
    {
        $prompt = $this->buildPrompt($question);

        $query = $this->queryOpenAi($prompt, "\n");

        $this->ensureQueryIsSafe($query);

        return $query;
    }

    protected function queryOpenAi(string $prompt, string $stop, float $temperature = 0.0)
    {
        $completions = $this->client->completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => $prompt,
            'temperature' => $temperature,
            'max_tokens' => 100,
            'stop' => $stop,
        ]);

        return $completions->choices[0]->text;
    }

    public function buildPrompt(string $question, string $query = null, string $result = null): string
    {
        $databasePlatform = DB::connection($this->connection)->getDoctrineConnection()->getDatabasePlatform();
        $schemaManager = DB::connection($this->connection)->getDoctrineSchemaManager();
        $dialect = Str::before(class_basename($databasePlatform), 'Platform');

        $tables = $schemaManager->listTables();

        return (string) view('ask-database::prompts.query', [
            'prompt' => $question,
            'tables' => $tables,
            'dialect' => $dialect,
            'query' => $query,
            'result' => $result,
        ]);
    }

    protected function evaluateQuery(string $query): object
    {
        return DB::connection($this->connection)->select($this->getRawQuery($query))[0] ?? new \stdClass();
    }

    protected function getRawQuery(string $query): string
    {
        if (version_compare(app()->version(), '10.0', '<')) {
            /* @phpstan-ignore-next-line */
            return (string) DB::raw($query);
        }

        return DB::raw($query)->getValue(DB::connection($this->connection)->getQueryGrammar());
    }

    protected function ensureQueryIsSafe(string $query): void
    {
        if (! config('ask-database.strict_mode')) {
            return;
        }

        $query = strtolower($query);
        $forbiddenWords = ['insert', 'update', 'delete', 'alter', 'drop', 'truncate', 'create', 'replace'];
        throw_if(Str::contains($query, $forbiddenWords), PotentiallyUnsafeQuery::fromQuery($query));
    }
}
