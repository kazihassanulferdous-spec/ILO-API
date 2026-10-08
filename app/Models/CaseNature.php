<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseNature extends Model
{
    protected $fillable = [
        'name_en',
        'name_bn',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

}
