<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceScanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Reports\ReportController as DynamicReportController;
use App\Http\Controllers\SecurityAccess\UserImportController;
use App\Livewire\AcademicLogistics\ManualScheduleAssignment;
use App\Livewire\AcademicLogistics\Attendance\AttendanceQrManager;
use App\Livewire\AcademicProcesses\GroupManager;
use App\Livewire\AcademicProcesses\SubjectManager;
use App\Livewire\AcademicProcesses\AcademicPeriodManager;
use App\Livewire\AcademicProcesses\TeacherScheduleView;
use App\Livewire\AcademicLogistics\ScheduleBlockManager;
use App\Livewire\AcademicLogistics\ClassroomManager;
use App\Livewire\AcademicLogistics\InfrastructureManager;
use App\Livewire\AcademicLogistics\SpecialReservationManager;
use App\Livewire\AcademicManagement\UniversityCareerManager;
use App\Livewire\SecurityAccess\AuditLogManager;
use App\Livewire\SecurityAccess\RoleManager;
use App\Livewire\SecurityAccess\UserManager;
use Illuminate\Support\Facades\Route;
use App\Livewire\AcademicProcesses\TeacherSubjectManager;
use App\Livewire\Notifications\NotificationCenter;
use App\Livewire\Notifications\CreateNotification;
use App\Livewire\Notifications\ViewNotification;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        // Si es solo docente, redirigir a su horario
        if ($user->hasRole('Docente') && !$user->hasRole('Administrador')) {
            return redirect()->route('my-schedule.index');
        }
        
        // Si es administrador, redirigir al dashboard
        if ($user->hasRole('Administrador')) {
            return redirect()->route('dashboard');
        }
        
        // Por defecto, ir al dashboard
        return redirect()->route('dashboard');
    }
    
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'role:Administrador'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rutas de perfil - accesibles para todos
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Ruta de horario personal - accesible para docentes
    Route::get('/my-schedule', TeacherScheduleView::class)->name('my-schedule.index');
    
    // Rutas compartidas - accesibles para Docentes y Administradores
    Route::get('/academic-logistics/attendance', AttendanceQrManager::class)->name('attendance.index');
    Route::get('/academic-logistics/special-reservations', SpecialReservationManager::class)->name('special-reservations.index');
    
    // Rutas administrativas - solo para Administradores
    Route::middleware(['role:Administrador'])->group(function () {
        Route::get('/user', UserManager::class)->name('user.index');
        Route::get('/role', RoleManager::class)->name('role.index');
        Route::get('/subject', SubjectManager::class)->name('subject.index');
        Route::get('/teacher-subject', TeacherSubjectManager::class)->name('teacher-subject.index');

        Route::get('/academic-logistics/infrastructure', InfrastructureManager::class)->name('infrastructure.index');
        Route::get('/academic-logistics/classroom', ClassroomManager::class)->name('classroom.index');
        Route::get('/academic-logistics/schedule-block', ScheduleBlockManager::class)->name('schedule-block.index');
        Route::get('/academic-logistics/manual-schedule-assignment', ManualScheduleAssignment::class)->name('manual-schedule-assignment.index');

        Route::get('/academic-process/academic-periods', AcademicPeriodManager::class)->name('academic-periods.index');
        Route::get('/academic-process/group', GroupManager::class)->name('group.index');
        
        Route::get('/academic-management/university-careers', UniversityCareerManager::class)->name('university-careers.index');
        
        Route::get('/security-access/auditLog', AuditLogManager::class)->name('auditLog.index');
        Route::get('/security-access/user-import', [UserImportController::class, 'index'])->name('users.import.index');
        Route::post('/security-access/user-import', [UserImportController::class, 'import'])->name('users.import.process');
        Route::get('/security-access/user-import/template', [UserImportController::class, 'downloadTemplate'])->name('users.import.template');

    });

    // Rutas de Reportes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/weekly-schedules', [ReportController::class, 'weeklySchedules'])->name('reports.weekly-schedules');
    Route::get('/reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
    Route::get('/reports/available-classrooms', [ReportController::class, 'availableClassrooms'])->name('reports.available-classrooms');

    // Exportación a Excel (CSV)
    Route::get('/reports/weekly-schedules/export', [ReportController::class, 'exportWeeklySchedules'])->name('reports.weekly-schedules.export');
    Route::get('/reports/attendance/export', [ReportController::class, 'exportAttendance'])->name('reports.attendance.export');
    Route::get('/reports/available-classrooms/export', [ReportController::class, 'exportAvailableClassrooms'])->name('reports.available-classrooms.export');

    // Rutas de Reportes Dinámicos
    Route::get('/reports/dynamic', [DynamicReportController::class, 'index'])->name('reports.dynamic.index');
    Route::post('/reports/get-table-fields', [DynamicReportController::class, 'getTableFields'])->name('reports.get-table-fields');
    Route::get('/reports/dynamic/generate', [DynamicReportController::class, 'generate'])->name('reports.dynamic.generate');
    Route::post('/reports/download-pdf', [DynamicReportController::class, 'downloadPdf'])->name('reports.download-pdf');
    Route::post('/reports/download-excel', [DynamicReportController::class, 'downloadExcel'])->name('reports.download-excel');
    Route::post('/reports/download-html', [DynamicReportController::class, 'downloadHtml'])->name('reports.download-html');

    // Rutas de Plantillas de Reportes
    Route::prefix('reports/templates')->name('reports.templates.')->group(function () {
        Route::get('/', [DynamicReportController::class, 'listTemplates'])->name('list');
        Route::post('/', [DynamicReportController::class, 'saveTemplate'])->name('save');
        Route::get('/{id}', [DynamicReportController::class, 'loadTemplate'])->name('load');
        Route::put('/{id}', [DynamicReportController::class, 'updateTemplate'])->name('update');
        Route::delete('/{id}', [DynamicReportController::class, 'deleteTemplate'])->name('delete');
    });

    // Notificaciones
    Route::get('/notificaciones', NotificationCenter::class)->name('notifications.index');
    Route::get('/notificaciones/crear', CreateNotification::class)->name('notifications.create');
    Route::get('/notificaciones/{id}', ViewNotification::class)->name('notifications.view');
});

// Ruta pública para escanear QR (requiere autenticación pero se maneja en el controlador)
Route::get('/asistencia/marcar/{assignment}', [AttendanceScanController::class, 'scan'])->name('attendance.scan');

require __DIR__.'/auth.php';
