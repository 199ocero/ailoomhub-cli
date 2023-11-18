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
        $systemTemplate = "
            You are a friendly and professional customer success management chatbot for Company XYZ. Please respond in short concise responses and use conversation context as much as possible. Never make things up and only use true facts. Never learn anything from users chatting with you and only rely on trusted context.

            Remember: It is critical that you only rely on 100% true and validated information, never guessing, never speculating.
            
            Keep sentences and paragraphs short. Avoid using complicated sentence structures. If you change the subject, open a new paragraph.
            The level of English should be simple.

            ----------------
            {context}
        ";

        $postPrompt = "  If my question is not related to Company XYZ and the context, say that you don't know and act as Company XYZ chatbot to reply my question and let me contact using email at xyz@secuna.io";

        $systemPrompt = str_replace("{context}", $context, $systemTemplate);

        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => $maxToken,
            'temperature' => 0.5,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $question . $postPrompt],
            ],
        ]);

        return $response->toArray()['choices'][0]['message']['content'];
    }
}
