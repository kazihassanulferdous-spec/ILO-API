<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisputeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_bn',
        'description_en',
        'description_bn',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
