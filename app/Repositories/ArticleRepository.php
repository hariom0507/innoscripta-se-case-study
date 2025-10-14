<?php

namespace App\Repositories;

use App\Models\Article;

class ArticleRepository
{
    public function saveOrUpdate(array $data): Article
    {
        return Article::updateOrCreate(
            ['url' => $data['url']],
            $data
        );
    }

    public function getFilteredArticles(array $filters)
    {
        $query = Article::query()->with(['source', 'category']);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%')
                  ->orWhere('content', 'like', '%'.$filters['search'].'%');
        }

        if (!empty($filters['source'])) {
            $query->whereHas('source', fn($q) => $q->where('api_identifier', $filters['source']));
        }

        if (!empty($filters['category'])) {
            $query->whereHas('category', fn($q) => $q->where('slug', $filters['category']));
        }

        if (!empty($filters['date'])) {
            $query->whereDate('published_at', $filters['date']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }
}
