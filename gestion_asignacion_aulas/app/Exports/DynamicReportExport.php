<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DynamicReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $headers;
    protected $displayFields;
    protected $tableName;

    public function __construct($data, $headers, $displayFields, $tableName)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->displayFields = $displayFields;
        $this->tableName = $tableName;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->data->map(function ($row) {
            $processedRow = [];
            
            foreach ($this->displayFields as $field) {
                $rawValue = is_object($row) ? ($row->$field ?? null) : ($row[$field] ?? null);
                $displayValue = null;
                
                // Para foreign keys, intentar obtener el nombre relacionado
                if (str_ends_with($field, '_id')) {
                    $nameField = $field . '_name';
                    $relatedName = is_object($row) ? ($row->$nameField ?? null) : ($row[$nameField] ?? null);
                    $displayValue = $relatedName ?: $rawValue;
                } else {
                    $displayValue = $rawValue;
                }
                
                // Formatear valores especiales
                if ($field === 'status' && !is_null($displayValue)) {
                    $processedRow[] = $displayValue ? 'Activo' : 'Inactivo';
                } elseif ($field === 'is_present' && !is_null($displayValue)) {
                    $processedRow[] = $displayValue ? 'Presente' : 'Ausente';
                } elseif (in_array($field, ['created_at', 'updated_at', 'attendance_date', 'start_date', 'end_date', 'expires_at', 'used_at', 'read_at']) && !is_null($displayValue)) {
                    $processedRow[] = \Carbon\Carbon::parse($displayValue)->format('d/m/Y H:i');
                } elseif (in_array($field, ['start_time', 'end_time', 'check_in_time', 'check_out_time']) && !is_null($displayValue)) {
                    $processedRow[] = \Carbon\Carbon::parse($displayValue)->format('H:i');
                } else {
                    $processedRow[] = $displayValue ?? 'N/A';
                }
            }
            
            return $processedRow;
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return array_values($this->headers);
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados de columnas
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'], // Azul
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $totalRecords = $this->data->count();
                
                // Insertar 4 filas al inicio
                $sheet->insertNewRowBefore(1, 4);
                
                // FILA 1: Título del reporte
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->setCellValue('A1', $this->tableName);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1F2937'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // FILA 2: Fecha de generación
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->setCellValue('A2', 'Reporte generado el ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '6B7280'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                
                // FILA 3: Línea separadora (vacía con borde inferior azul)
                $sheet->mergeCells('A3:' . $highestColumn . '3');
                $sheet->getStyle('A3:' . $highestColumn . '3')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '3B82F6'],
                        ],
                    ],
                ]);
                
                // FILA 4: Información (Total de registros y Fecha)
                $halfColumn = chr(ord('A') + floor((ord($highestColumn) - ord('A')) / 2));
                $sheet->mergeCells('A4:' . $halfColumn . '4');
                $sheet->mergeCells(chr(ord($halfColumn) + 1) . '4:' . $highestColumn . '4');
                
                $sheet->setCellValue('A4', 'Total de registros: ' . $totalRecords);
                $sheet->setCellValue(chr(ord($halfColumn) + 1) . '4', 'Fecha: ' . now()->format('d/m/Y'));
                
                $sheet->getStyle('A4:' . $highestColumn . '4')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '4B5563'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Alinear a la izquierda el total y a la derecha la fecha
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle(chr(ord($halfColumn) + 1) . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Ajustar altura de fila 4
                $sheet->getRowDimension(4)->setRowHeight(25);
                
                // Aplicar bordes a toda la tabla de datos (desde fila 5 en adelante)
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A5:' . $highestColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
