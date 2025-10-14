<?php

namespace App\Repositories;

use App\Models\Source;

class SourceRepository
{
    public function saveOrUpdate(array $data): Source
    {
        return Source::updateOrCreate(
            ['api_identifier' => $data['api_identifier']],
            $data
        );
    }

    public function all()
    {
        return \App\Models\Source::all(['id','name','api_identifier']);
    }

    public function getFiltered(array $filters)
    {
        $query = \App\Models\Source::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('name')->paginate($perPage);
    }
}
