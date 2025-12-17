<?php

namespace App\Http\Controllers;

use App\Models\AbsenceRequest;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;

class AbsenceRequestController extends Controller
{
    /**
     * Mostrar listado de solicitudes de ausencia
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrador');
        $isTeacher = $user->hasRole('Docente');

        $query = AbsenceRequest::with([
            'teacher',
            'reviewedBy',
            'assignment.subject',
            'assignment.daySchedule.schedule',
            'assignment.daySchedule.day',
            'assignment.classroom',
            'assignment.group'
        ]);

        // Si es admin (con o sin rol docente), ve todas las solicitudes
        if ($isAdmin) {
            // Aplicar filtros opcionales
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        // Si es solo docente (no admin), solo ve sus solicitudes
        elseif ($isTeacher) {
            $query->where('teacher_id', $user->id);
        }

        $absenceRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('absence-requests.index', compact('absenceRequests', 'isAdmin'));
    }

    /**
     * Mostrar detalles de una solicitud específica
     */
    public function show($id)
    {
        $absenceRequest = AbsenceRequest::with([
            'teacher',
            'reviewedBy',
            'assignment.subject',
            'assignment.daySchedule.schedule',
            'assignment.daySchedule.day',
            'assignment.classroom'
        ])->findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrador');

        // Verificar permisos: admin puede ver todo, docente solo sus solicitudes
        if (!$isAdmin && $absenceRequest->teacher_id !== $user->id) {
            abort(403, 'No autorizado para ver esta solicitud');
        }

        return view('absence-requests.show', compact('absenceRequest', 'isAdmin'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Verificar límite diario (3 solicitudes por día)
        $today = now()->startOfDay();
        $requestsToday = AbsenceRequest::where('teacher_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        $remainingRequests = 3 - $requestsToday;

        if ($remainingRequests <= 0) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Has alcanzado el límite de 3 solicitudes por día'], 400);
            }
            return redirect()->route('absence-requests.index')
                ->with('error', 'Has alcanzado el límite de 3 solicitudes por día');
        }

        // Cargar assignment con relaciones si se proporciona
        $assignment = null;
        $classDate = null;

        if ($request->has('assignment_id')) {
            $assignment = Assignment::with([
                'subject',
                'daySchedule.schedule',
                'daySchedule.day',
                'classroom',
                'group'
            ])->find($request->assignment_id);
        }

        if ($request->has('class_date')) {
            $classDate = $request->class_date;
        }

        if ($request->ajax()) {
            return view('absence-requests.create-modal', compact('remainingRequests', 'assignment', 'classDate'));
        }

        return redirect()->route('absence-requests.index');
    }

    /**
     * Guardar nueva solicitud de ausencia
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validar datos
        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'absence_date' => 'required|date',
            'absence_type' => 'required|in:enfermedad,personal,familiar,medico,emergencia,otro',
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB
        ]);

        // Verificar límite diario (3 solicitudes creadas hoy)
        $today = now()->startOfDay();
        $requestsToday = AbsenceRequest::where('teacher_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        if ($requestsToday >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Has alcanzado el límite de 3 solicitudes por día'
            ], 400);
        }

        // Verificar si ya existe una solicitud para esta clase específica
        $existingRequest = AbsenceRequest::where('teacher_id', $user->id)
            ->where('assignment_id', $validated['assignment_id'])
            ->whereDate('absence_date', $validated['absence_date'])
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una solicitud para esta clase'
            ], 400);
        }

        // Subir evidencia a Cloudinary si existe
        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            try {
                $file = $request->file('evidence');
                $extension = $file->getClientOriginalExtension();

                // Determinar tipo de recurso
                $resourceType = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'raw';

                // Configurar Cloudinary
                $cloudinary = new Cloudinary([
                    'cloud' => [
                        'cloud_name' => config('cloudinary.cloud_name'),
                        'api_key' => config('cloudinary.api_key'),
                        'api_secret' => config('cloudinary.api_secret'),
                    ],
                ]);

                // Generar public_id único
                $publicId = 'absence_' . $user->id . '_' . time();

                // Subir archivo
                $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'absences',
                    'public_id' => $publicId,
                    'resource_type' => $resourceType,
                ]);

                $evidencePath = $result['secure_url'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir la evidencia'
                ], 500);
            }
        }

        // Crear la solicitud
        $absenceRequest = AbsenceRequest::create([
            'teacher_id' => $user->id,
            'assignment_id' => $validated['assignment_id'],
            'absence_date' => $validated['absence_date'],
            'absence_type' => $validated['absence_type'],
            'reason' => $validated['reason'],
            'evidence_path' => $evidencePath,
            'status' => 'pendiente',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud de ausencia creada exitosamente'
            ]);
        }

        return redirect()->route('absence-requests.index')
            ->with('success', 'Solicitud de ausencia creada exitosamente');

    }

    /**
     * Actualizar estado de la solicitud (solo admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('Administrador')) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'status' => 'required|in:pendiente,en_revision,aprobada,rechazada',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $absenceRequest = AbsenceRequest::findOrFail($id);

        $absenceRequest->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'],
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud actualizada exitosamente'
            ]);
        }

        return redirect()->route('absence-requests.show', $id)
            ->with('success', 'Solicitud actualizada exitosamente');
    }

    /**
     * Eliminar solicitud (solo admin)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->hasRole('Administrador')) {
            abort(403, 'No autorizado');
        }

        $absenceRequest = AbsenceRequest::findOrFail($id);
        $absenceRequest->delete();

        return redirect()->route('absence-requests.index')
            ->with('success', 'Solicitud eliminada exitosamente');
    }
}
