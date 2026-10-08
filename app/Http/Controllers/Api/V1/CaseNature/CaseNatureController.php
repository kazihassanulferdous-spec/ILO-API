<?php

namespace App\Http\Controllers\Api\V1\CaseNature;

use App\Http\Controllers\Controller;
use App\Http\Requests\CaseNature\StoreCaseNatureRequest;
use App\Http\Requests\CaseNature\UpdateCaseNatureRequest;
use App\Http\Resources\CaseNatureResource;
use App\Models\CaseNature;
use App\Services\CaseNature\CaseNatureService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CaseNatureController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CaseNatureService $caseNatureService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | List Case Natures
    |--------------------------------------------------------------------------
    */

    #[OA\Get(
        path: '/api/v1/case-natures',
        summary: 'Get case natures',
        description: 'Returns a paginated list of case natures with optional search and status filtering.',
        tags: ['Case Natures'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search by case nature name.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'By Government'
            ),

            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by active/inactive status.',
                schema: new OA\Schema(
                    type: 'boolean'
                ),
                example: true
            ),

            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Number of records per page. Maximum 100.',
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 100,
                    default: 10
                ),
                example: 10
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Case natures retrieved successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $caseNatures = $this->caseNatureService->getAll(
            $request->only([
                'search',
                'status',
                'per_page',
            ])
        );

        return $this->successResponse(
            [
                'items' => CaseNatureResource::collection(
                    $caseNatures->items()
                ),

                'pagination' => [
                    'current_page' => $caseNatures->currentPage(),
                    'last_page' => $caseNatures->lastPage(),
                    'per_page' => $caseNatures->perPage(),
                    'total' => $caseNatures->total(),
                ],
            ],
            'Case natures retrieved successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Case Nature
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: '/api/v1/case-natures',
        summary: 'Create case nature',
        description: 'Create a new case nature.',
        tags: ['Case Natures'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name_en',
                ],
                properties: [
                    new OA\Property(
                        property: 'name_en',
                        type: 'string',
                        maxLength: 255,
                        example: 'By Government'
                    ),

                    new OA\Property(
                        property: 'name_bn',
                        type: 'string',
                        nullable: true,
                        maxLength: 255,
                        example: 'সরকার কর্তৃক'
                    ),

                    new OA\Property(
                        property: 'status',
                        type: 'boolean',
                        default: true,
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Case nature created successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store(
        StoreCaseNatureRequest $request
    ): JsonResponse {
        $caseNature = $this->caseNatureService->create(
            $request->validated()
        );

        return $this->successResponse(
            new CaseNatureResource($caseNature),
            'Case nature created successfully.',
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Case Nature
    |--------------------------------------------------------------------------
    */

    #[OA\Get(
        path: '/api/v1/case-natures/{caseNature}',
        summary: 'Get case nature',
        description: 'Returns a single case nature by ID.',
        tags: ['Case Natures'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        parameters: [
            new OA\Parameter(
                name: 'caseNature',
                in: 'path',
                required: true,
                description: 'Case nature ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Case nature retrieved successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 404,
                description: 'Case nature not found'
            ),
        ]
    )]
    public function show(
        CaseNature $caseNature
    ): JsonResponse {
        return $this->successResponse(
            new CaseNatureResource($caseNature),
            'Case nature retrieved successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Case Nature
    |--------------------------------------------------------------------------
    */

    #[OA\Put(
        path: '/api/v1/case-natures/{caseNature}',
        summary: 'Update case nature',
        description: 'Update an existing case nature.',
        tags: ['Case Natures'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        parameters: [
            new OA\Parameter(
                name: 'caseNature',
                in: 'path',
                required: true,
                description: 'Case nature ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name_en',
                        type: 'string',
                        maxLength: 255,
                        example: 'By Government'
                    ),

                    new OA\Property(
                        property: 'name_bn',
                        type: 'string',
                        nullable: true,
                        maxLength: 255,
                        example: 'সরকার কর্তৃক'
                    ),

                    new OA\Property(
                        property: 'status',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Case nature updated successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 404,
                description: 'Case nature not found'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function update(
        UpdateCaseNatureRequest $request,
        CaseNature $caseNature
    ): JsonResponse {
        $caseNature = $this->caseNatureService->update(
            $caseNature,
            $request->validated()
        );

        return $this->successResponse(
            new CaseNatureResource($caseNature),
            'Case nature updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Case Nature
    |--------------------------------------------------------------------------
    */

    #[OA\Delete(
        path: '/api/v1/case-natures/{caseNature}',
        summary: 'Delete case nature',
        description: 'Delete an existing case nature.',
        tags: ['Case Natures'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        parameters: [
            new OA\Parameter(
                name: 'caseNature',
                in: 'path',
                required: true,
                description: 'Case nature ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Case nature deleted successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 404,
                description: 'Case nature not found'
            ),
        ]
    )]
    public function destroy(
        CaseNature $caseNature
    ): JsonResponse {
        $this->caseNatureService->delete($caseNature);

        return $this->successResponse(
            null,
            'Case nature deleted successfully.'
        );
    }
}
