<?php

namespace App\Services;

use OpenAI;

class OpenAIAgent
{
    protected $client;
    protected $text;

    public function __construct(string $text)
    {
        if (config('openai.api_key') === null) {
            throw new \Exception('OpenAI API key is not set.');
        }

        $this->client = OpenAI::client(config('openai.api_key'));
        $this->text = $text;
    }

    public static function make(string $text): self
    {
        return new self($text);
    }

    public function embeddings(): array
    {
        $response = $this->client->embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $this->text,
        ]);

        return $response->toArray()['data'][0]['embedding'];
    }
}
