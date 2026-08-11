<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'project_list',
    ];

    protected $attributes = [
        'project_list' => '[]',
    ];

    protected function casts(): array
    {
        return [
            'project_list' => 'array',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SectionTranslation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
