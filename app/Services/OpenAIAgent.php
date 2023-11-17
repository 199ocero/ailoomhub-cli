<?php

namespace App\Services;

use OpenAI;

class OpenAIAgent
{
    protected $client;

    public function __construct()
    {
        if (config('openai.api_key') === null) {
            throw new \Exception('OpenAI API key is not set.');
        }

        $this->client = OpenAI::client(config('openai.api_key'));
    }

    public static function make(): self
    {
        return new self();
    }

    public function embeddings(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $text,
        ]);

        return $response->toArray()['data'][0]['embedding'];
    }

    public function askQuestion(string $context, string $question): string
    {
        $system_template = "
            Use the following pieces of context to answer the users question.
            If you don't know the answer, just say that you don't know, don't try to make up an answer.
            ----------------
            {context}
        ";

        $system_prompt = str_replace("{context}", $context, $system_template);

        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => 200,
            'temperature' => 0.8,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $question],
            ],
        ]);

        return $response->toArray()['choices'][0]['message']['content'];
    }
}
