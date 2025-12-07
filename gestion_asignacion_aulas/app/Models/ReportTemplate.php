<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'table_name',
        'selected_fields',
        'filters',
        'is_public',
    ];

    protected $casts = [
        'selected_fields' => 'array',
        'filters' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Relación con el usuario que creó la plantilla
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
