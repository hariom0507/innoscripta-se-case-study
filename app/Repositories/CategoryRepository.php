<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function saveOrUpdate(array $data): Category
    {
        return Category::updateOrCreate(
            ['slug' => $data['slug']],
            $data
        );
    }

    public function all()
    {
        return \App\Models\Category::all(['id','name','slug']);
    }

    public function getFiltered(array $filters)
    {
        $query = \App\Models\Category::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%')
                ->orWhere('slug', 'like', '%'.$filters['search'].'%');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('name')->paginate($perPage);
    }
}
