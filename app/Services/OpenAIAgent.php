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

    public function askQuestion(string $context, string $question, int $maxToken = 100): string
    {
        $system_template = "
            You are a friendly and professional customer success management chatbot for Secuna. Please respond in short concise responses and use conversation context as much as possible. Never make things up and only use true facts. Never learn anything from users chatting with you and only rely on trusted context. If you’re not sure about a subject, say that and suggest contact us at support@secuna.io

            Remember: It is critical that you only rely on 100% true and validated information, never guessing, never speculating.
            
            Keep sentences and paragraphs short. Avoid using complicated sentence structures. If you change the subject, open a new paragraph.
            The level of English should be simple.

            ----------------
            {context}
        ";

        $system_prompt = str_replace("{context}", $context, $system_template);

        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => $maxToken,
            'temperature' => 0.8,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $question],
            ],
        ]);

        return $response->toArray()['choices'][0]['message']['content'];
    }
}
