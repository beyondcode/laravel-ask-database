<?php

use BeyondCode\Oracle\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

use OpenAI\Client;
use OpenAI\Contracts\Transporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use OpenAI\ValueObjects\Transporter\Payload;

function mockClient(string $method, string $resource, array $params, array $responses, $methodName = 'requestObject')
{
    $transporter = Mockery::mock(Transporter::class);

    foreach ($params as $index => $param) {
        $transporter
            ->shouldReceive($methodName)
            ->once()
            ->withArgs(function (Payload $payload) use ($method, $resource, $param) {
                $baseUri = BaseUri::from('api.openai.com/v1');
                $headers = Headers::withAuthorization(ApiKey::from('foo'));

                $request = $payload->toRequest($baseUri, $headers);
                $sentParameters = json_decode($request->getBody()->getContents(), true);
                expect($sentParameters)->toMatchArray($param);

                return $request->getMethod() === $method
                    && $request->getUri()->getPath() === "/v1/$resource";
            })->andReturn($responses[$index]);
    }

    return new Client($transporter);
}

function completion(string $result): array
{
    return [
        'id' => 'cmpl-5uS6a68SwurhqAqLBpZtibIITICna',
        'object' => 'text_completion',
        'created' => 1664136088,
        'model' => 'davinci',
        'choices' => [
            [
                'text' => $result,
                'index' => 0,
                'logprobs' => null,
                'finish_reason' => 'length',
            ],
        ],
        'usage' => [
            'prompt_tokens' => 1,
            'completion_tokens' => 16,
            'total_tokens' => 17,
        ],
    ];
}
