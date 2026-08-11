<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $primaryKey = 'slug';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'slug',
        'code',
        'category',
        'section_id',
        'image',
        'stock_quantity',
        'price',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'price' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Attributes safe to expose on public (non-admin) responses/views.
     * `price` and `stock_quantity` are admin-only and must never appear here.
     */
    public const array PUBLIC_VISIBLE = [
        'slug', 'code', 'category', 'section_id', 'image', 'sort_order', 'created_at', 'updated_at',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class, 'product_slug', 'slug');
    }
}
