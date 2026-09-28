<?php

namespace App\Http\Requests\DisputeType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisputeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $disputeType = $this->route('disputeType');

        return [
            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique(
                    'dispute_types',
                    'name_en'
                )->ignore($disputeType?->id),
            ],

            'name_bn' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique(
                    'dispute_types',
                    'name_bn'
                )->ignore($disputeType?->id),
            ],

            'description_en' => [
                'nullable',
                'string',
            ],

            'description_bn' => [
                'nullable',
                'string',
            ],

            'status' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
