<?php

namespace App\Http\Requests\DisputeType;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => [
                'required',
                'string',
                'max:150',
                'unique:dispute_types,name_en',
            ],

            'name_bn' => [
                'required',
                'string',
                'max:150',
                'unique:dispute_types,name_bn',
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
