<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte: {{ $tableName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: #e5e7eb;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #1f2937;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            padding: 20px;
            text-align: center;
            border-bottom: 4px solid #2563eb;
        }

        .header h1 {
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }

        .info-bar {
            background: #374151;
            padding: 20px 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            border-bottom: 1px solid #4b5563;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item strong {
            color: #3b82f6;
            font-size: 14px;
        }

        .info-item span {
            color: #d1d5db;
            font-size: 14px;
        }

        .table-container {
            padding: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #111827;
            border-radius: 8px;
            overflow: hidden;
        }

        thead {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        }

        th {
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 3px solid #2563eb;
        }

        tbody tr {
            border-bottom: 1px solid #374151;
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: linear-gradient(90deg, rgba(249, 115, 22, 0.1) 0%, rgba(251, 146, 60, 0.1) 100%);
        }

        tbody tr:nth-child(even) {
            background: rgba(31, 41, 55, 0.5);
        }

        tbody tr:nth-child(even):hover {
            background: linear-gradient(90deg, rgba(249, 115, 22, 0.15) 0%, rgba(251, 146, 60, 0.15) 100%);
        }

        td {
            padding: 14px 12px;
            font-size: 13px;
            color: #d1d5db;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .badge-inactive {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .icon-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icon {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        .price {
            color: #10b981;
            font-weight: 600;
        }

        .foreign-key {
            color: #60a5fa;
            font-weight: 500;
        }

        .footer {
            background: #111827;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #374151;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        @media print {
            body {
                background: white;
                color: #000;
            }

            .container {
                box-shadow: none;
            }

            tbody tr:hover {
                background: transparent !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $tableName }}</h1>
            <div class="subtitle">Reporte generado el {{ now()->format('d/m/Y H:i') }}</div>
        </div>

        <div class="info-bar">
            <div class="info-item">
                <strong>📊 Total de registros:</strong>
                <span>{{ count($data) }}</span>
            </div>
            <div class="info-item">
                <strong>📅 Fecha:</strong>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        @foreach ($headers as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr>
                            @foreach ($displayFields as $field)
                                <td>
                                    @php
                                        $displayValue = null;
                                        $rawValue = is_object($row) ? $row->$field ?? null : $row[$field] ?? null;

                                        if (str_ends_with($field, '_id')) {
                                            $nameField = $field . '_name';
                                            $relatedName = is_object($row)
                                                ? $row->$nameField ?? null
                                                : $row[$nameField] ?? null;
                                            $displayValue = $relatedName ?: $rawValue;
                                        } else {
                                            $displayValue = $rawValue;
                                        }
                                    @endphp

                                    @if ($field === 'status' && !is_null($displayValue))
                                        <span class="badge {{ $displayValue ? 'badge-active' : 'badge-inactive' }}">
                                            {{ $displayValue ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @elseif($field === 'gender' && !is_null($displayValue))
                                        {{ $displayValue === 'male' ? 'Masculino' : 'Femenino' }}
                                    @elseif($field === 'email' && !is_null($displayValue))
                                        {{ $displayValue }}
                                    @elseif($field === 'phone' && !is_null($displayValue))
                                        {{ $displayValue }}
                                    @elseif(($field === 'created_at' || $field === 'updated_at') && !is_null($displayValue))
                                        {{ \Carbon\Carbon::parse($displayValue)->format('d/m/Y H:i') }}
                                    @elseif(str_ends_with($field, '_id') && !is_null($displayValue) && !is_numeric($displayValue))
                                        <span class="foreign-key">{{ $displayValue }}</span>
                                    @elseif(is_numeric($displayValue) && in_array($field, ['price', 'total', 'subtotal', 'discount']))
                                        <span class="price">UDS. {{ number_format($displayValue, 2) }}</span>
                                    @else
                                        {{ $displayValue ?? 'N/A' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headers) }}" class="no-data">
                                No hay registros disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            Sistema de Reportes Dinámicos - Generado automáticamente
        </div>
    </div>
</body>

</html>
