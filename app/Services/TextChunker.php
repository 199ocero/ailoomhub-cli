<?php

namespace App\Services;

class TextChunker
{
    protected $text;
    protected $chunk;
    public function __construct(string $text, int $chunk = 200)
    {
        $this->text = $text;
        $this->chunk = $chunk;
    }

    public static function make(string $text, int $chunk = 200): self
    {
        return new self($text, $chunk);
    }

    public function chunk(): string
    {
        // Split the text into an array of words
        $words = preg_split('/\s+/', $this->text, -1, PREG_SPLIT_NO_EMPTY);

        // Extract the first chunk words
        $chunkedWords = array_slice($words, 0, $this->chunk);

        // Check if the remaining text is less than 100 words
        if (count($words) - $this->chunk < 100) {
            // If remaining text is less than 100 words, combine it with the current chunk
            $result = implode(' ', $chunkedWords) . ' ' . implode(' ', array_slice($words, $this->chunk));
        } else {
            $result = implode(' ', $chunkedWords);
        }

        return $result;
    }
}
