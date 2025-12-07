<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte: {{ $tableName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3b82f6;
        }
        
        .header h1 {
            color: #1f2937;
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #6b7280;
            font-size: 11px;
        }
        
        .info-bar {
            background: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
        }
        
        .info-bar div {
            font-size: 9px;
            color: #4b5563;
        }
        
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background-color: #3b82f6;
        }
        
        thead tr {
            background-color: #3b82f6;
        }
        
        th {
            background-color: #3b82f6 !important;
            color: #ffffff !important;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #2563eb;
        }

        
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        td {
            padding: 8px;
            font-size: 9px;
            border: 1px solid #e5e7eb;
            word-wrap: break-word;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .badge-active {
            background-color: #10b981;
            color: white;
        }
        
        .badge-inactive {
            background-color: #ef4444;
            color: white;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            padding: 10px;
            border-top: 1px solid #e5e7eb;
        }
        
        .page-number:before {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $tableName }}</h1>
        <div class="subtitle">Reporte generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>
    
    <div class="info-bar">
        <div><strong>Total de registros:</strong> {{ count($data) }}</div>
        <div><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</div>
    </div>
    
    <table>
        <thead>
            <tr>
                @foreach($headers as $field => $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($displayFields as $field)
                        <td>
                            @php
                                $displayValue = null;
                                $rawValue = is_object($row) ? ($row->$field ?? null) : ($row[$field] ?? null);
                                
                                if (str_ends_with($field, '_id')) {
                                    $nameField = $field . '_name';
                                    $relatedName = is_object($row) ? ($row->$nameField ?? null) : ($row[$nameField] ?? null);
                                    $displayValue = $relatedName ?: $rawValue;
                                } else {
                                    $displayValue = $rawValue;
                                }
                            @endphp
                            
                            @if($field === 'status' && !is_null($displayValue))
                                <span class="badge {{ $displayValue ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $displayValue ? 'Activo' : 'Inactivo' }}
                                </span>
                            @elseif($field === 'gender' && !is_null($displayValue))
                                {{ $displayValue === 'male' ? 'Masculino' : 'Femenino' }}
                            @elseif(($field === 'created_at' || $field === 'updated_at') && !is_null($displayValue))
                                {{ \Carbon\Carbon::parse($displayValue)->format('d/m/Y H:i') }}
                            @elseif(str_ends_with($field, '_id') && !is_null($displayValue) && !is_numeric($displayValue))
                                {{ $displayValue }}
                            @elseif(is_numeric($displayValue) && in_array($field, ['price', 'total', 'subtotal', 'discount']))
                                Bs. {{ number_format($displayValue, 2) }}
                            @else
                                {{ $displayValue ?? 'N/A' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; padding: 20px;">
                        No hay registros disponibles
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <div class="page-number"></div>
    </div>
</body>
</html>
