<?php

namespace App\Http\Controllers\Api\V1\DisputeType;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisputeType\StoreDisputeTypeRequest;
use App\Http\Requests\DisputeType\UpdateDisputeTypeRequest;
use App\Http\Resources\DisputeTypeResource;
use App\Models\DisputeType;
use App\Services\DisputeType\DisputeTypeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeTypeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DisputeTypeService $disputeTypeService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $disputeTypes = $this
            ->disputeTypeService
            ->getAll(
                $request->only([
                    'search',
                    'status',
                    'per_page',
                ])
            );

        return $this->successResponse(
            [
                'items' => DisputeTypeResource::collection(
                    $disputeTypes->items()
                ),

                'pagination' => [
                    'current_page' => $disputeTypes->currentPage(),
                    'last_page' => $disputeTypes->lastPage(),
                    'per_page' => $disputeTypes->perPage(),
                    'total' => $disputeTypes->total(),
                ],
            ],
            'Dispute types retrieved successfully.'
        );
    }

    public function store(
        StoreDisputeTypeRequest $request
    ): JsonResponse {
        $disputeType = $this
            ->disputeTypeService
            ->create(
                $request->validated()
            );

        return $this->successResponse(
            new DisputeTypeResource(
                $disputeType
            ),
            'Dispute type created successfully.',
            201
        );
    }

    public function show(
        DisputeType $disputeType
    ): JsonResponse {
        return $this->successResponse(
            new DisputeTypeResource(
                $disputeType
            ),
            'Dispute type retrieved successfully.'
        );
    }

    public function update(
        UpdateDisputeTypeRequest $request,
        DisputeType $disputeType
    ): JsonResponse {
        $disputeType = $this
            ->disputeTypeService
            ->update(
                $disputeType,
                $request->validated()
            );

        return $this->successResponse(
            new DisputeTypeResource(
                $disputeType
            ),
            'Dispute type updated successfully.'
        );
    }

    public function destroy(
        DisputeType $disputeType
    ): JsonResponse {
        $this
            ->disputeTypeService
            ->delete($disputeType);

        return $this->successResponse(
            null,
            'Dispute type deleted successfully.'
        );
    }
}
