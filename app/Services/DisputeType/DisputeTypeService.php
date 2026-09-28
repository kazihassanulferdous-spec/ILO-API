<?php

namespace App\Services\DisputeType;

use App\Models\DisputeType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DisputeTypeService
{
    public function getAll(
        array $filters = []
    ): LengthAwarePaginator {
        $query = DisputeType::query();

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
    ): DisputeType {
        return DisputeType::create($data);
    }

    public function update(
        DisputeType $disputeType,
        array $data
    ): DisputeType {
        $disputeType->update($data);

        return $disputeType->fresh();
    }

    public function delete(
        DisputeType $disputeType
    ): void {
        $disputeType->delete();
    }
}
