<?php

namespace App\Services\Fetch;

use App\Repositories\{ArticleRepository, SourceRepository, CategoryRepository};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewYorkTimesService implements NewsSourceInterface
{
    public function __construct(
        protected string $apiKey,
        protected ArticleRepository $articles,
        protected SourceRepository $sources,
        protected CategoryRepository $categories
    ) {}

    public function fetchArticles(): array
    {
        $sourceIdentifier = 'nytimes';
        $source = $this->sources->saveOrUpdate([
            'name' => 'New York Times',
            'api_identifier' => $sourceIdentifier,
            'base_url' => 'https://api.nytimes.com/svc/topstories/v2/'
        ]);

        try {
            $response = Http::timeout(30)->get('https://api.nytimes.com/svc/topstories/v2/home.json', [
                'api-key' => $this->apiKey
            ]);

            if ($response->failed()) {
                Log::warning("NYTimes API failed: ".$response->body());
                return [];
            }

            $articles = [];
            foreach ($response->json('results') as $item) {
                $category = $this->categories->saveOrUpdate([
                    'name' => $item['section'] ?? 'general',
                    'slug' => strtolower($item['section'] ?? 'general')
                ]);

                $article = $this->articles->saveOrUpdate([
                    'title' => $item['title'],
                    'author' => $item['byline'] ?? null,
                    'description' => $item['abstract'] ?? null,
                    'content' => $item['abstract'] ?? null,
                    'url' => $item['url'],
                    'image_url' => $item['multimedia'][0]['url'] ?? null,
                    'published_at' => $item['published_date'] ?? now(),
                    'source_id' => $source->id,
                    'category_id' => $category->id
                ]);

                $article->metadata()->updateOrCreate(['article_id' => $article->id], ['metadata' => $item]);
                $articles[] = $article;
            }

            $source->update(['last_fetched_at' => now()]);
            return $articles;

        } catch (\Throwable $e) {
            Log::error("NYTimes fetch failed: ".$e->getMessage());
            return [];
        }
    }
}
