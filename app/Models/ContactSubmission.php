<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactSubmission extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'created_at',
        'mode',
        'name',
        'company',
        'email',
        'phone',
        'message',
        'tech_primary',
        'tech_extra',
        'status',
    ];

    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'tech_primary' => 'array',
            'tech_extra' => 'array',
        ];
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SubmissionAttachment::class, 'submission_id');
    }
}
