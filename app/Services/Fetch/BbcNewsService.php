<?php

namespace App\Services\Fetch;

use App\Repositories\{ArticleRepository, SourceRepository, CategoryRepository};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BbcNewsService implements NewsSourceInterface
{
    public function __construct(
        protected ArticleRepository $articles,
        protected SourceRepository $sources,
        protected CategoryRepository $categories
    ) {}

    public function fetchArticles(): array
    {
        $sourceIdentifier = 'bbc';
        $source = $this->sources->saveOrUpdate([
            'name'=>'BBC News',
            'api_identifier'=>$sourceIdentifier,
            'base_url'=>'https://bbc-news-api.vercel.app/'
        ]);

        try {
            $response = Http::timeout(30)->get('https://bbc-news-api.vercel.app/news', [
                'lang'=>'english'
            ]);

            if ($response->failed()) {
                Log::warning("BBC API failed: ".$response->body());
                return [];
            }

            $articles = [];
            foreach ($response->json('articles') as $item) {
                $category = $this->categories->saveOrUpdate([
                    'name'=>$item['category'] ?? 'general',
                    'slug'=> strtolower($item['category'] ?? 'general')
                ]);

                $article = $this->articles->saveOrUpdate([
                    'title'=>$item['title'],
                    'author'=>$item['author'] ?? null,
                    'description'=>$item['description'] ?? null,
                    'content'=>$item['content'] ?? null,
                    'url'=>$item['url'],
                    'image_url'=>$item['imageUrl'] ?? null,
                    'published_at'=>$item['publishedAt'] ?? now(),
                    'source_id'=>$source->id,
                    'category_id'=>$category->id
                ]);

                $article->metadata()->updateOrCreate(['article_id'=>$article->id], ['metadata'=>$item]);
                $articles[] = $article;
            }

            $source->update(['last_fetched_at'=>now()]);
            return $articles;

        } catch (\Throwable $e) {
            Log::error("BBC fetch failed: ".$e->getMessage());
            return [];
        }
    }
}
