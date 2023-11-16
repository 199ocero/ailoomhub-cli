<?php

namespace App\Services;

use FiveamCode\LaravelNotionApi\Notion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class PageRetriever
{
    protected $notion;

    public function __construct(string $notionToken)
    {
        $this->notion = new Notion(Crypt::decryptString($notionToken));
    }

    public static function make(string $notionToken): self
    {
        return new self($notionToken);
    }

    public function retrieve(): Collection
    {
        return $this->notion->search()->onlyPages()->query()->asCollection();
    }

    public function retrieveById(string $pageId): Collection
    {
        return $this->notion->block($pageId)->children()->asCollection();
    }
}
