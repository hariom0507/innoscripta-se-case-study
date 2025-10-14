<?php

namespace App\Services\Fetch;

interface NewsSourceInterface
{
    /**
     * Fetch and save articles.
     * @return array
     */
    public function fetchArticles(): array;
}
