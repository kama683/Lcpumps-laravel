<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Composite primary key (product_slug, locale) — see SectionTranslation for
 * the same caveat: no ->save() on a fetched instance, no find().
 */
class ProductTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_slug',
        'locale',
        'name',
        'description',
        'specs',
        'applications',
    ];

    protected $attributes = [
        'description' => '[]',
        'specs' => '[]',
        'applications' => '[]',
    ];

    protected function casts(): array
    {
        return [
            'description' => 'array',
            'specs' => 'array',
            'applications' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_slug', 'slug');
    }
}
