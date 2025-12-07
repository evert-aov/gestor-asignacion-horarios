<?php

namespace App\Http\Controllers\SecurityAccess;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserImportController extends Controller
{
    /**
     * Mostrar formulario de importación
     */
    public function index()
    {
        return view('security-access.user-import');
    }

    /**
     * Procesar importación de usuarios
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'file.required' => 'Debe seleccionar un archivo para importar.',
            'file.mimes' => 'El archivo debe ser CSV o TXT.',
            'file.max' => 'El archivo no debe superar los 2MB.',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            
            // Parsear CSV
            $data = $this->parseCsv($path);
            
            // Procesar importación
            $results = $this->processImport($data);
            
            if ($results['successCount'] > 0) {
                return redirect()->route('users.import.index')
                    ->with('success', "✅ Importación completada: {$results['successCount']} usuarios creados exitosamente.")
                    ->with('importResults', $results);
            }
            
            return redirect()->route('users.import.index')
                ->with('error', 'No se pudo importar ningún usuario.')
                ->with('importResults', $results);
                
        } catch (\Exception $e) {
            return redirect()->route('users.import.index')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Parsear archivo CSV
     */
    private function parseCsv($path)
    {
        $data = [];
        $handle = fopen($path, 'r');
        
        if ($handle === false) {
            throw new \Exception('No se pudo abrir el archivo.');
        }

        // Detectar y omitir BOM UTF-8 si existe
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $row = 0;
        $errors = [];
        
        while (($line = fgetcsv($handle, 1000, ',')) !== false) {
            $row++;
            
            // Omitir primera fila si parece ser encabezado
            if ($row === 1 && $this->isHeaderRow($line)) {
                continue;
            }

            // Validar que tenga al menos 5 columnas
            if (count($line) < 5) {
                $errors[] = "Fila {$row}: Formato incorrecto (se esperan 5 columnas).";
                continue;
            }

            $data[] = [
                'name' => trim($line[0]),
                'last_name' => trim($line[1]),
                'phone' => trim($line[2]),
                'email' => trim($line[3]),
                'document_number' => trim($line[4]),
                'row' => $row,
            ];
        }

        fclose($handle);
        
        if (!empty($errors)) {
            session()->flash('importErrors', $errors);
        }
        
        return $data;
    }

    /**
     * Verificar si es fila de encabezado
     */
    private function isHeaderRow($line)
    {
        $firstColumn = strtolower(trim($line[0]));
        return in_array($firstColumn, ['name', 'nombre', 'names']);
    }

    /**
     * Procesar datos de importación
     */
    private function processImport($data)
    {
        // Obtener el rol de Docente
        $docenteRole = Role::where('name', 'Docente')->first();

        if (!$docenteRole) {
            throw new \Exception('No se encontró el rol "Docente" en el sistema. Por favor créelo primero.');
        }

        $importResults = [];
        $importErrors = [];
        $successCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            foreach ($data as $userData) {
                try {
                    // Validar datos
                    $validation = $this->validateUserData($userData);
                    
                    if (!$validation['valid']) {
                        $importErrors[] = "Fila {$userData['row']}: {$validation['message']}";
                        $errorCount++;
                        continue;
                    }

                    // Verificar si el usuario ya existe
                    $existingUser = User::where('email', $userData['email'])
                        ->orWhere('document_number', $userData['document_number'])
                        ->first();

                    if ($existingUser) {
                        $importErrors[] = "Fila {$userData['row']}: Usuario ya existe (email: {$userData['email']} o CI: {$userData['document_number']}).";
                        $errorCount++;
                        continue;
                    }

                    // Crear usuario
                    $user = User::create([
                        'name' => $userData['name'],
                        'last_name' => $userData['last_name'],
                        'phone' => $userData['phone'],
                        'email' => $userData['email'],
                        'document_type' => 'CI',
                        'document_number' => $userData['document_number'],
                        'password' => Hash::make($userData['document_number']),
                        'is_active' => true,
                    ]);

                    // Asignar rol de Docente
                    $user->roles()->attach($docenteRole->id);

                    $importResults[] = [
                        'success' => true,
                        'row' => $userData['row'],
                        'name' => $userData['name'] . ' ' . $userData['last_name'],
                        'email' => $userData['email'],
                    ];

                    $successCount++;

                } catch (\Exception $e) {
                    $importErrors[] = "Fila {$userData['row']}: Error al crear usuario - {$e->getMessage()}";
                    $errorCount++;
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Guardar errores en sesión si existen
        if (!empty($importErrors)) {
            session()->flash('importErrors', $importErrors);
        }

        return [
            'results' => $importResults,
            'errors' => $importErrors,
            'successCount' => $successCount,
            'errorCount' => $errorCount,
        ];
    }

    /**
     * Validar datos de usuario
     */
    private function validateUserData($data)
    {
        if (empty($data['name'])) {
            return ['valid' => false, 'message' => 'El nombre es requerido.'];
        }

        if (empty($data['last_name'])) {
            return ['valid' => false, 'message' => 'El apellido es requerido.'];
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Email inválido o vacío.'];
        }

        if (empty($data['document_number'])) {
            return ['valid' => false, 'message' => 'El número de documento es requerido.'];
        }

        if (!is_numeric($data['document_number'])) {
            return ['valid' => false, 'message' => 'El número de documento debe ser numérico.'];
        }

        return ['valid' => true];
    }

    /**
     * Descargar plantilla CSV
     */
    public function downloadTemplate()
    {
        $filename = 'plantilla_importacion_docentes.csv';

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8 en Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($output, ['name', 'last_name', 'phone', 'email', 'document_number']);
            
            // Ejemplos
            fputcsv($output, ['Juan', 'Pérez', '70123456', 'juan.perez@example.com', '12345678']);
            fputcsv($output, ['María', 'González', '71234567', 'maria.gonzalez@example.com', '87654321']);
            
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
