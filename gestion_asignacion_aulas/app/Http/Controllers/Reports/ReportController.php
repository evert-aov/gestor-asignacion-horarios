<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Mapeo de tablas a nombres amigables
     */
    private function getAvailableTables(): array
    {
        return [
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'permissions' => 'Permisos',
            'subjects' => 'Materias',
            'classrooms' => 'Aulas',
            'groups' => 'Grupos',
            'assignments' => 'Asignaciones',
            'schedules' => 'Horarios',
            'days' => 'Días',
            'day_schedules' => 'Horarios por Día',
            'academic_management' => 'Gestión Académica',
            'university_careers' => 'Carreras Universitarias',
            'attendance_records' => 'Registros de Asistencia',
            'audit_logs' => 'Bitácora de Auditoría',
            'special_reservations' => 'Reservas Especiales',
            'qr_tokens' => 'Tokens QR',
            'notifications' => 'Notificaciones',
        ];
    }

    /**
     * Mapeo de relaciones: foreign_key => [tabla_relacionada, campo_nombre_o_expresion, primary_key_opcional]
     * Para tablas sin campo 'name', usar expresión CONCAT
     * Si la tabla relacionada no usa 'id' como PK, especificar el tercer parámetro
     */
    private function getRelationshipMappings(): array
    {
        return [
            'user_id' => ['users', 'CONCAT(name, \' \', last_name)', 'id'],
            'role_id' => ['roles', 'name', 'id'],
            'permission_id' => ['permissions', 'name', 'id'],
            'subject_id' => ['subjects', 'name', 'id'],
            'classroom_id' => ['classrooms', 'name', 'id'],
            'group_id' => ['groups', 'name', 'id'],
            'schedule_id' => ['schedules', 'id', 'id'],
            'day_id' => ['days', 'name', 'id'],
            'academic_management_id' => ['academic_management', 'name', 'id'],
            'university_career_id' => ['university_careers', 'name', 'id'],
            'assignment_id' => ['assignments', 'id', 'id'],
        ];
    }

    /**
     * Obtiene los nombres amigables de las columnas de una tabla
     */
    private function getFieldLabels(string $table): array
    {
        $labels = [
            'id' => 'ID',
            'name' => 'Nombre',
            'last_name' => 'Apellido',
            'email' => 'Email',
            'phone' => 'Teléfono',
            'address' => 'Dirección',
            'status' => 'Estado',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
            'description' => 'Descripción',
            'user_id' => 'Usuario',
            'role_id' => 'Rol',
            'permission_id' => 'Permiso',
            'subject_id' => 'Materia',
            'classroom_id' => 'Aula',
            'group_id' => 'Grupo',
            'schedule_id' => 'Horario',
            'day_id' => 'Día',
            'academic_management_id' => 'Gestión Académica',
            'university_career_id' => 'Carrera Universitaria',
            'assignment_id' => 'Asignación',
            'capacity' => 'Capacidad',
            'location' => 'Ubicación',
            'building' => 'Edificio',
            'floor' => 'Piso',
            'start_time' => 'Hora de Inicio',
            'end_time' => 'Hora de Fin',
            'start_date' => 'Fecha de Inicio',
            'end_date' => 'Fecha de Fin',
            'semester' => 'Semestre',
            'year' => 'Año',
            'level' => 'Nivel',
            'action' => 'Acción',
            'ip_address' => 'Dirección IP',
            'user_agent' => 'Navegador',
            'attendance_date' => 'Fecha de Asistencia',
            'check_in_time' => 'Hora de Entrada',
            'check_out_time' => 'Hora de Salida',
            'is_present' => 'Presente',
            'reason' => 'Razón',
            'token' => 'Token',
            'expires_at' => 'Fecha de Expiración',
            'used_at' => 'Usado en',
            'type' => 'Tipo',
            'data' => 'Datos',
            'read_at' => 'Leído en',
            'notifiable_type' => 'Tipo de Notificable',
            'notifiable_id' => 'ID de Notificable',
        ];

        return $labels;
    }

    /**
     * Prepara la consulta y los datos necesarios para el reporte
     *
     * @throws \Exception
     */
    private function prepareReportQuery(string $table, array $selectedFields, array $filters = []): array
    {
        $availableTables = $this->getAvailableTables();

        if (! array_key_exists($table, $availableTables)) {
            throw new \Exception('Tabla no válida');
        }

        // Validar que los campos existen en la tabla
        $tableColumns = Schema::getColumnListing($table);
        foreach ($selectedFields as $field) {
            if (! in_array($field, $tableColumns)) {
                throw new \Exception('Campo no válido: '.$field);
            }
        }

        $fieldLabels = $this->getFieldLabels($table);
        $relationshipMappings = $this->getRelationshipMappings();

        // Construir la consulta con JOINs automáticos
        $query = DB::table($table);
        $selectFields = [];
        $joinedTables = [];

        foreach ($selectedFields as $field) {
            // Si es una foreign key, hacer JOIN
            if (str_ends_with($field, '_id') && isset($relationshipMappings[$field])) {
                $mapping = $relationshipMappings[$field];
                $relatedTable = $mapping[0];
                $relatedFieldOrExpression = $mapping[1];
                $primaryKey = $mapping[2] ?? 'id';

                // Relación simple
                $joinKey = $relatedTable.'_'.$field;
                if (! in_array($joinKey, $joinedTables)) {
                    $query->leftJoin(
                        $relatedTable.' as '.$joinKey,
                        $table.'.'.$field,
                        '=',
                        $joinKey.'.'.$primaryKey
                    );
                    $joinedTables[] = $joinKey;
                }

                // Seleccionar el campo relacionado con alias
                if (str_contains($relatedFieldOrExpression, 'CONCAT')) {
                    $expression = preg_replace_callback(
                        '/([a-z_]+)([,\s\)])/',
                        function ($matches) use ($joinKey) {
                            if (! in_array($matches[1], ['CONCAT', 'NULL'])) {
                                return $joinKey.'.'.$matches[1].$matches[2];
                            }

                            return $matches[0];
                        },
                        $relatedFieldOrExpression
                    );
                    $selectFields[] = DB::raw($expression.' as '.$field.'_name');
                } else {
                    $selectFields[] = $joinKey.'.'.$relatedFieldOrExpression.' as '.$field.'_name';
                }

                $selectFields[] = $table.'.'.$field; // También incluir el ID original
            } else {
                // Campo normal
                $selectFields[] = $table.'.'.$field;
            }
        }

        // Aplicar filtros
        foreach ($filters as $filter) {
            if (empty($filter['field']) || empty($filter['operator'])) {
                continue;
            }

            $rawField = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'] ?? null;
            $value2 = $filter['value2'] ?? null;

            // Validar que el campo existe en la tabla
            if (! in_array($rawField, $tableColumns)) {
                continue;
            }

            $targetColumn = $table.'.'.$rawField;

            // Si es una foreign key, usar la columna relacionada
            if (str_ends_with($rawField, '_id') && isset($relationshipMappings[$rawField])) {
                $mapping = $relationshipMappings[$rawField];
                $relatedTable = $mapping[0];
                $relatedFieldOrExpression = $mapping[1];
                $primaryKey = $mapping[2] ?? 'id';

                $joinKey = $relatedTable.'_'.$rawField;
                if (! in_array($joinKey, $joinedTables)) {
                    $query->leftJoin(
                        $relatedTable.' as '.$joinKey,
                        $table.'.'.$rawField,
                        '=',
                        $joinKey.'.'.$primaryKey
                    );
                    $joinedTables[] = $joinKey;
                }

                if (str_contains($relatedFieldOrExpression, 'CONCAT')) {
                    $targetColumn = DB::raw(preg_replace_callback(
                        '/([a-z_]+)([,\s\)])/',
                        function ($matches) use ($joinKey) {
                            if (! in_array($matches[1], ['CONCAT', 'NULL'])) {
                                return $joinKey.'.'.$matches[1].$matches[2];
                            }

                            return $matches[0];
                        },
                        $relatedFieldOrExpression
                    ));
                } else {
                    $targetColumn = $joinKey.'.'.$relatedFieldOrExpression;
                }
            }

            switch ($operator) {
                case 'like':
                    if ($targetColumn instanceof \Illuminate\Database\Query\Expression) {
                        $query->whereRaw("$targetColumn like ?", ['%'.$value.'%']);
                    } else {
                        $query->where($targetColumn, 'like', '%'.$value.'%');
                    }
                    break;
                case 'between':
                    if ($value !== null && $value2 !== null) {
                        if ($targetColumn instanceof \Illuminate\Database\Query\Expression) {
                            $query->whereRaw("$targetColumn between ? and ?", [$value, $value2]);
                        } else {
                            $query->whereBetween($targetColumn, [$value, $value2]);
                        }
                    }
                    break;
                case '=':
                case '!=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                    if ($value !== null) {
                        if ($targetColumn instanceof \Illuminate\Database\Query\Expression) {
                            $query->whereRaw("$targetColumn $operator ?", [$value]);
                        } else {
                            $query->where($targetColumn, $operator, $value);
                        }
                    }
                    break;
            }
        }

        // Crear headers con las etiquetas
        $headers = [];
        $displayFields = [];

        foreach ($selectedFields as $field) {
            $headers[$field] = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
            $displayFields[] = $field;
        }

        $query->select($selectFields);

        return [
            'query' => $query,
            'headers' => $headers,
            'displayFields' => $displayFields,
            'tableName' => $availableTables[$table],
        ];
    }

    /**
     * Muestra la interfaz de selección de tabla y campos
     */
    public function index(): View
    {
        $availableTables = $this->getAvailableTables();

        return view('reports.dynamic-report-selector', compact('availableTables'));
    }

    /**
     * Endpoint AJAX: Obtiene los campos de una tabla específica
     */
    public function getTableFields(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
        ]);

        $table = $request->input('table');
        $availableTables = $this->getAvailableTables();

        if (! array_key_exists($table, $availableTables)) {
            return response()->json(['error' => 'Tabla no válida'], 400);
        }

        try {
            // Obtener columnas de la tabla
            $columns = Schema::getColumnListing($table);
            $fieldLabels = $this->getFieldLabels($table);

            // Crear array de campos con etiquetas y tipos
            $fields = [];
            foreach ($columns as $column) {
                // Excluir campos sensibles o no útiles
                if (in_array($column, ['password', 'remember_token'])) {
                    continue;
                }

                $type = Schema::getColumnType($table, $column);

                $fields[$column] = [
                    'label' => $fieldLabels[$column] ?? ucfirst(str_replace('_', ' ', $column)),
                    'type' => $type,
                ];
            }

            return response()->json([
                'success' => true,
                'fields' => $fields,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener campos: '.$e->getMessage()], 500);
        }
    }

    /**
     * Genera el reporte basado en la tabla y campos seleccionados
     */
    public function generate(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'fields' => 'required|array|min:1',
            'fields.*' => 'string',
            'filters' => 'nullable|array',
        ], [
            'table.required' => 'Debe seleccionar una tabla.',
            'fields.required' => 'Debe seleccionar al menos un campo para el reporte.',
            'fields.min' => 'Debe seleccionar al menos un campo para el reporte.',
        ]);

        $table = $request->input('table');
        $selectedFields = $request->input('fields');
        $filters = $request->input('filters', []);

        try {
            $reportData = $this->prepareReportQuery($table, $selectedFields, $filters);

            $query = $reportData['query'];
            $headers = $reportData['headers'];
            $displayFields = $reportData['displayFields'];
            $tableName = $reportData['tableName'];

            // Ejecutar consulta con paginación
            $data = $query->paginate(20)->appends($request->all());

            return view('reports.dynamic-report-result', compact('data', 'headers', 'displayFields', 'selectedFields', 'table', 'tableName', 'filters'));
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->getCode();
            $errorMessage = 'Error al generar reporte.';

            if ($errorCode == '22007') {
                $errorMessage = 'Formato de fecha inválido. Por favor verifique los filtros de fecha.';
            } elseif ($errorCode == '22P02') {
                $errorMessage = 'Valor inválido para el tipo de dato. Por favor verifique que los valores numéricos sean números y las fechas sean válidas.';
            } elseif ($errorCode == '22003') {
                $errorMessage = 'Valor numérico fuera de rango. El número ingresado es demasiado grande.';
            } elseif ($errorCode == '22001') {
                $errorMessage = 'Texto demasiado largo. Por favor reduzca la longitud del texto en el filtro.';
            } else {
                $errorMessage .= ' (Código: '.$errorCode.') '.$e->getMessage();
            }

            return back()->withErrors(['error' => $errorMessage]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar reporte: '.$e->getMessage()]);
        }
    }

    /**
     * Descarga el reporte como PDF
     */
    public function downloadPdf(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'fields' => 'required|array|min:1',
            'fields.*' => 'string',
            'filters' => 'nullable|array',
        ]);

        $table = $request->input('table');
        $selectedFields = $request->input('fields');
        $filters = $request->input('filters', []);

        try {
            $reportData = $this->prepareReportQuery($table, $selectedFields, $filters);

            $query = $reportData['query'];
            $headers = $reportData['headers'];
            $displayFields = $reportData['displayFields'];
            $tableName = $reportData['tableName'];

            // Obtener todos los datos (sin paginación para PDF)
            $data = $query->get();

            // Generar PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-template', compact('data', 'headers', 'displayFields', 'tableName'));

            $fileName = 'Reporte_'.str_replace(' ', '_', $tableName).'_'.now()->format('Y-m-d').'.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar PDF: '.$e->getMessage()]);
        }
    }

    /**
     * Descarga el reporte como Excel
     */
    public function downloadExcel(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'fields' => 'required|array|min:1',
            'fields.*' => 'string',
            'filters' => 'nullable|array',
        ]);

        $table = $request->input('table');
        $selectedFields = $request->input('fields');
        $filters = $request->input('filters', []);

        try {
            $reportData = $this->prepareReportQuery($table, $selectedFields, $filters);

            $query = $reportData['query'];
            $headers = $reportData['headers'];
            $displayFields = $reportData['displayFields'];
            $tableName = $reportData['tableName'];

            // Obtener todos los datos (sin paginación para Excel)
            $data = $query->get();

            $fileName = 'Reporte_'.str_replace(' ', '_', $tableName).'_'.now()->format('Y-m-d').'.xlsx';

            // Generar Excel usando la clase export
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\DynamicReportExport($data, $headers, $displayFields, $tableName),
                $fileName
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar Excel: '.$e->getMessage()]);
        }
    }

    /**
     * Descarga el reporte como HTML
     */
    public function downloadHtml(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'fields' => 'required|array|min:1',
            'fields.*' => 'string',
            'filters' => 'nullable|array',
        ]);

        $table = $request->input('table');
        $selectedFields = $request->input('fields');
        $filters = $request->input('filters', []);

        try {
            $reportData = $this->prepareReportQuery($table, $selectedFields, $filters);

            $query = $reportData['query'];
            $headers = $reportData['headers'];
            $displayFields = $reportData['displayFields'];
            $tableName = $reportData['tableName'];

            // Obtener todos los datos (sin paginación para HTML)
            $data = $query->get();

            $fileName = 'Reporte_'.str_replace(' ', '_', $tableName).'_'.now()->format('Y-m-d').'.html';

            // Generar HTML
            $html = view('reports.html-export', compact('data', 'headers', 'displayFields', 'tableName'))->render();

            // Retornar como descarga
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar HTML: '.$e->getMessage()]);
        }
    }

    /**
     * =========================================
     * GESTIÓN DE PLANTILLAS DE REPORTES
     * =========================================
     */

    /**
     * Listar plantillas del usuario actual y públicas
     */
    public function listTemplates()
    {
        $templates = ReportTemplate::where('user_id', auth()->id())
            ->orWhere('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $availableTables = $this->getAvailableTables();

        return view('reports.templates-list', compact('templates', 'availableTables'));
    }

    /**
     * Guardar nueva plantilla
     */
    public function saveTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'table' => 'required|string',
            'fields' => 'required|array|min:1',
            'filters' => 'nullable|array',
            'is_public' => 'nullable|boolean',
        ]);

        $template = ReportTemplate::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'table_name' => $request->table,
            'selected_fields' => $request->fields,
            'filters' => $request->filters ?? [],
            'is_public' => $request->is_public ?? false,
        ]);

        return redirect()->route('reports.templates.list')
            ->with('success', 'Plantilla guardada exitosamente');
    }

    /**
     * Cargar plantilla existente
     */
    public function loadTemplate($id)
    {
        $template = ReportTemplate::where('id', $id)
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('is_public', true);
            })
            ->firstOrFail();

        // Retornar vista con datos precargados
        $availableTables = $this->getAvailableTables();

        return view('reports.dynamic-report-selector',
            compact('availableTables', 'template'));
    }

    /**
     * Actualizar plantilla existente
     */
    public function updateTemplate(Request $request, $id)
    {
        $template = ReportTemplate::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'filters' => 'nullable|array',
            'is_public' => 'nullable|boolean',
        ]);

        $template->update([
            'name' => $request->name,
            'description' => $request->description,
            'selected_fields' => $request->fields,
            'filters' => $request->filters ?? [],
            'is_public' => $request->is_public ?? false,
        ]);

        return redirect()->route('reports.templates.list')
            ->with('success', 'Plantilla actualizada exitosamente');
    }

    /**
     * Eliminar plantilla
     */
    public function deleteTemplate($id)
    {
        $template = ReportTemplate::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $template->delete();

        return redirect()->route('reports.templates.list')
            ->with('success', 'Plantilla eliminada exitosamente');
    }
}
