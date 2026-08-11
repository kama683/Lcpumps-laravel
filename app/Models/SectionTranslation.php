<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Composite primary key (section_id, locale) — Eloquent has no native support
 * for composite keys. Always write via updateOrCreate()/query builder, never
 * ->save() on a fetched instance or find(), since both rely on a single $id.
 */
class SectionTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'section_id',
        'locale',
        'title',
        'paragraphs',
        'bullets',
    ];

    protected $attributes = [
        'paragraphs' => '[]',
    ];

    protected function casts(): array
    {
        return [
            'paragraphs' => 'array',
            'bullets' => 'array',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
