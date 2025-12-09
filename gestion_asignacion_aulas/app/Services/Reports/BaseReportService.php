<?php

namespace App\Services\Reports;

use App\Models\AcademicManagement;
use App\Models\Group;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseReportService
{
    /**
     * Obtener periodo académico activo o el seleccionado
     */
    protected function getAcademicManagement($academicManagementId = null)
    {
        if ($academicManagementId) {
            return AcademicManagement::find($academicManagementId);
        }

        return AcademicManagement::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Obtener datos comunes para filtros
     */
    public function getFilterData()
    {
        return [
            'academicManagements' => AcademicManagement::orderBy('start_date', 'desc')->get(),
            'groups' => Group::where('is_active', true)->orderBy('name')->get(),
            'teachers' => User::role('Docente')->orderBy('name')->get(),
        ];
    }

    /**
     * Generar PDF usando una plantilla
     */
    protected function generatePDF(string $view, array $data, string $filename, string $orientation = 'portrait')
    {
        $data['generatedAt'] = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }

    /**
     * Generar Excel usando clase anónima
     */
    protected function generateExcel(string $filename, $data, array $headers, \Closure $collectionCallback = null)
    {
        return Excel::download(new class($data, $headers, $collectionCallback) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
            private $data;
            private $headers;
            private $collectionCallback;

            public function __construct($data, $headers, $collectionCallback = null)
            {
                $this->data = $data;
                $this->headers = $headers;
                $this->collectionCallback = $collectionCallback;
            }

            public function collection()
            {
                if ($this->collectionCallback) {
                    return call_user_func($this->collectionCallback, $this->data);
                }

                return collect($this->data);
            }

            public function headings(): array
            {
                return $this->headers;
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '3B82F6']
                        ],
                    ],
                ];
            }
        }, $filename);
    }

    /**
     * Aplicar filtros comunes a una consulta
     */
    protected function applyCommonFilters($query, Request $request)
    {
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->input('group_id'));
        }

        return $query;
    }
}
