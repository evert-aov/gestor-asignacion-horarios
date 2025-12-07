<?php

namespace App\Models;

use App\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniversityCareer extends Model
{
    use Auditable;
    protected $fillable = [
        'name',
        'code',
        'study_level',
        'duration_years',
        'faculty',
        'language',
    ];

    protected $casts = [
        'duration_years' => 'integer',
    ];

    // Relationships
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_university_career')
            ->withPivot(['semester', 'is_required'])
            ->withTimestamps();
    }
}
