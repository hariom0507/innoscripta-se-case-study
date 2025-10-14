<?php

namespace App\Services\Fetch;

use App\Repositories\{ArticleRepository, SourceRepository, CategoryRepository};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianService implements NewsSourceInterface
{
    public function __construct(
        protected string $apiKey,
        protected ArticleRepository $articles,
        protected SourceRepository $sources,
        protected CategoryRepository $categories
    ) {}

    public function fetchArticles(): array
    {
        $sourceIdentifier = 'guardian';
        $source = $this->sources->saveOrUpdate([
            'name'=>'The Guardian',
            'api_identifier'=>$sourceIdentifier,
            'base_url'=>'https://content.guardianapis.com/'
        ]);

        try {
            $response = Http::timeout(30)->get('https://content.guardianapis.com/search', [
                'api-key'=>$this->apiKey,
                'show-fields'=>'all'
            ]);

            if ($response->failed()) {
                Log::warning("Guardian API failed: ".$response->body());
                return [];
            }

            $articles = [];
            foreach ($response->json('response.results') as $item) {
                $fields = $item['fields'] ?? [];
                $category = $this->categories->saveOrUpdate([
                    'name'=>$item['sectionName'] ?? 'general',
                    'slug'=> strtolower($item['sectionName'] ?? 'general')
                ]);

                $article = $this->articles->saveOrUpdate([
                    'title'=>$item['webTitle'],
                    'author'=>$fields['byline'] ?? null,
                    'description'=>$fields['trailText'] ?? null,
                    'content'=>$fields['bodyText'] ?? null,
                    'url'=>$item['webUrl'],
                    'image_url'=>$fields['thumbnail'] ?? null,
                    'published_at'=>$item['webPublicationDate'] ?? now(),
                    'source_id'=>$source->id,
                    'category_id'=>$category->id
                ]);

                $article->metadata()->updateOrCreate(['article_id'=>$article->id], ['metadata'=>$item]);
                $articles[] = $article;
            }

            $source->update(['last_fetched_at'=>now()]);
            return $articles;

        } catch (\Throwable $e) {
            Log::error("Guardian fetch failed: ".$e->getMessage());
            return [];
        }
    }
}
