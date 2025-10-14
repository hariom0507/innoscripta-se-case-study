<?php

namespace App\Services\Fetch;

use App\Repositories\{ArticleRepository, SourceRepository, CategoryRepository};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NewsApiService implements NewsSourceInterface
{
    public function __construct(
        protected string $apiKey,
        protected ArticleRepository $articles,
        protected SourceRepository $sources,
        protected CategoryRepository $categories
    ) {}

    public function fetchArticles(): array
    {
        $sourceIdentifier = 'newsapi';
        $source = $this->sources->saveOrUpdate([
            'name' => 'NewsAPI',
            'api_identifier' => $sourceIdentifier,
            'base_url' => 'https://newsapi.org/v2/',
        ]);

        try {
            $response = Http::timeout(30)->get('https://newsapi.org/v2/top-headlines', [
                'apiKey' => $this->apiKey,
                'country' => 'us'
            ]);

            if ($response->failed()) {
                Log::warning("NewsAPI request failed: ".$response->body());
                return [];
            }

            $articles = [];
            foreach ($response->json('articles') as $item) {
                // dd($item);
                $category = $this->categories->saveOrUpdate([
                    'name' => $item['source']['name'] ?? 'general',
                    'slug' => $item['source']['name'] ? strtolower($item['source']['name']) : 'general'
                ]);

                $article = $this->articles->saveOrUpdate([
                    'title' => $item['title'],
                    'author' => $item['author'] ?? null,
                    'description' => $item['description'] ?? null,
                    'content' => $item['content'] ?? null,
                    'url' => $item['url'],
                    'image_url' => $item['urlToImage'] ?? null,
                    'published_at' => $item['publishedAt'] ?? now(),
                    'source_id' => $source->id,
                    'category_id' => $category->id
                ]);

                $article->metadata()->updateOrCreate(['article_id'=>$article->id], ['metadata'=>$item]);
                $articles[] = $article;
            }

            $source->update(['last_fetched_at'=>now()]);
            return $articles;

        } catch (\Throwable $e) {
            Log::error("NewsAPI fetch failed: ".$e->getMessage());
            return [];
        }
    }
}
