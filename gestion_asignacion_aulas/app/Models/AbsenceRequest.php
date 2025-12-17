<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cloudinary\Cloudinary;

class AbsenceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'assignment_id',
        'absence_date',
        'absence_type',
        'reason',
        'evidence_path',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'absence_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Relación con el docente que crea la solicitud
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relación con la asignación (clase específica)
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    /**
     * Relación con el admin que revisó la solicitud
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Verifica si se puede crear una solicitud de ausencia para una fecha específica
     */
    public static function canCreateAbsenceRequest($teacherId, $absenceDate)
    {
        // Verificar que no exista ya una solicitud para esta fecha
        $existingRequest = self::where('teacher_id', $teacherId)
            ->where('absence_date', $absenceDate)
            ->exists();

        if ($existingRequest) {
            return false;
        }

        // Verificar que la fecha no sea pasada (excepto hoy)
        $today = now()->startOfDay();
        $requestDate = \Carbon\Carbon::parse($absenceDate)->startOfDay();

        if ($requestDate->lt($today)) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene la URL completa de Cloudinary de la evidencia
     */
    public function getEvidenceUrlAttribute()
    {
        return $this->evidence_path;
    }

    /**
     * Boot del modelo para manejar eventos
     */
    protected static function booted()
    {
        // Al eliminar una solicitud, eliminar la evidencia de Cloudinary
        static::deleting(function ($absenceRequest) {
            if ($absenceRequest->evidence_path) {
                try {
                    // Extraer el public_id de la URL de Cloudinary
                    $urlParts = explode('/', $absenceRequest->evidence_path);
                    $fileName = end($urlParts);
                    $publicId = 'absences/' . pathinfo($fileName, PATHINFO_FILENAME);

                    // Configurar Cloudinary
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => config('cloudinary.cloud_name'),
                            'api_key' => config('cloudinary.api_key'),
                            'api_secret' => config('cloudinary.api_secret'),
                        ],
                    ]);

                    // Eliminar el archivo
                    $cloudinary->uploadApi()->destroy($publicId);


                } catch (\Exception $e) {
                    \Log::error('Error al eliminar evidencia de Cloudinary', [
                        'error' => $e->getMessage(),
                        'evidence_path' => $absenceRequest->evidence_path,
                    ]);
                }
            }
        });
    }
}
