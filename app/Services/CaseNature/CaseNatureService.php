<?php

namespace App\Services\CaseNature;

use App\Models\CaseNature;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CaseNatureService
{
    public function getAll(
        array $filters = []
    ): LengthAwarePaginator {
        $query = CaseNature::query();

        if (
            isset($filters['status']) &&
            $filters['status'] !== ''
        ) {
            $query->where(
                'status',
                filter_var(
                    $filters['status'],
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'name_en',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'name_bn',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description_en',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description_bn',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        $perPage = (int) (
            $filters['per_page'] ?? 15
        );

        $perPage = max(
            1,
            min($perPage, 100)
        );

        return $query
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(
        array $data
    ): CaseNature {
        return CaseNature::create($data);
    }

    public function update(
        CaseNature $caseNature,
        array $data
    ): CaseNature {
        $caseNature->update($data);

        return $caseNature->fresh();
    }

    public function delete(
        CaseNature $caseNature
    ): void {
        $caseNature->delete();
    }
}
