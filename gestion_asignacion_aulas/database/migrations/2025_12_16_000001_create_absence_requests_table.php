<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignment_id')->nullable()->constrained('assignments')->onDelete('cascade');
            $table->date('absence_date');
            $table->enum('absence_type', ['enfermedad', 'personal', 'familiar', 'medico', 'emergencia', 'otro']);
            $table->text('reason');
            $table->string('evidence_path')->nullable();
            $table->enum('status', ['pendiente', 'en_revision', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Índice único: un docente no puede tener múltiples solicitudes para el mismo assignment y fecha
            $table->unique(['teacher_id', 'assignment_id', 'absence_date'], 'unique_teacher_assignment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};
