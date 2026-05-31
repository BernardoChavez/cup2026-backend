<?php

namespace App\Modules\Account\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Postulante;
use App\Models\Docente;
use App\Modules\Audit\Services\AuditLogger;

class CargaMasivaService
{
    /**
     * Import postulantes from a CSV file.
     */
    public function importPostulantes(string $filePath, int $adminUserId)
    {
        return DB::transaction(function () use ($filePath, $adminUserId) {
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \Exception('No se pudo abrir el archivo CSV.');
            }

            // Read header
            $header = fgetcsv($handle, 1000, ',');
            if ($header === false) {
                fclose($handle);
                throw new \Exception('El archivo CSV está vacío.');
            }

            // Expected columns: nombre,apellido,email,password,ci,fecha_nacimiento,sexo,direccion,telefono,colegio_procedencia,ciudad,id_gestion_academica,id_carrera_opcion1,id_carrera_opcion2
            $rowsImported = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Map columns
                $row = array_combine($header, $data);
                if (!$row) continue;

                // Validate basic fields
                if (empty($row['email']) || empty($row['ci'])) {
                    throw new \Exception("Fila con datos vacíos detectada (email/ci obligatorios).");
                }

                // Check duplicate email or CI
                if (User::where('email', $row['email'])->exists()) {
                    throw new \Exception("El correo electrónico '{$row['email']}' ya existe en el sistema.");
                }
                if (Postulante::where('ci', $row['ci'])->exists()) {
                    throw new \Exception("La Cédula de Identidad '{$row['ci']}' ya está registrada.");
                }

                // Create user
                $user = User::create([
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido'],
                    'email' => $row['email'],
                    'password' => Hash::make($row['password'] ?? '123456'), // default password if not provided
                    'rol' => 'POSTULANTE',
                    'activo' => true
                ]);

                // Create postulante profile
                $postulante = Postulante::create([
                    'id_usuario' => $user->id,
                    'id_gestion_academica' => intval($row['id_gestion_academica']),
                    'ci' => $row['ci'],
                    'fecha_nacimiento' => $row['fecha_nacimiento'],
                    'sexo' => $row['sexo'],
                    'direccion' => $row['direccion'] ?? null,
                    'telefono' => $row['telefono'] ?? null,
                    'colegio_procedencia' => $row['colegio_procedencia'] ?? null,
                    'ciudad' => $row['ciudad'] ?? null,
                    'titulo_bachiller' => filter_var($row['titulo_bachiller'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'otros_requisitos' => $row['otros_requisitos'] ?? null,
                    'id_carrera_opcion1' => intval($row['id_carrera_opcion1']),
                    'id_carrera_opcion2' => intval($row['id_carrera_opcion2']),
                    'id_carrera_asignada' => null,
                    'pago_procesado' => false,
                    'estado_final' => 'CURSANDO'
                ]);

                $rowsImported++;
            }
            fclose($handle);

            // Audit log
            AuditLogger::log(
                $adminUserId,
                'CARGA_MASIVA',
                'postulantes',
                null,
                "Se cargaron de forma masiva {$rowsImported} postulantes desde archivo CSV."
            );

            return $rowsImported;
        });
    }

    /**
     * Import docentes from a CSV file.
     */
    public function importDocentes(string $filePath, int $adminUserId)
    {
        return DB::transaction(function () use ($filePath, $adminUserId) {
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \Exception('No se pudo abrir el archivo CSV.');
            }

            // Read header
            $header = fgetcsv($handle, 1000, ',');
            if ($header === false) {
                fclose($handle);
                throw new \Exception('El archivo CSV está vacío.');
            }

            // Expected columns: nombre,apellido,email,password,profesional_area,maestria,diplomado_edu_sup,contratado
            $rowsImported = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row = array_combine($header, $data);
                if (!$row) continue;

                // Validate basic fields
                if (empty($row['email'])) {
                    throw new \Exception("Fila con datos vacíos detectada (email obligatorio).");
                }

                // Check duplicate email
                if (User::where('email', $row['email'])->exists()) {
                    throw new \Exception("El correo electrónico '{$row['email']}' ya existe en el sistema.");
                }

                // Create user
                $user = User::create([
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido'],
                    'email' => $row['email'],
                    'password' => Hash::make($row['password'] ?? '123456'),
                    'rol' => 'DOCENTE',
                    'activo' => true
                ]);

                // Create docente profile
                Docente::create([
                    'id_usuario' => $user->id,
                    'profesional_area' => filter_var($row['profesional_area'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'maestria' => filter_var($row['maestria'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'diplomado_edu_sup' => filter_var($row['diplomado_edu_sup'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'contratado' => filter_var($row['contratado'] ?? false, FILTER_VALIDATE_BOOLEAN)
                ]);

                $rowsImported++;
            }
            fclose($handle);

            // Audit log
            AuditLogger::log(
                $adminUserId,
                'CARGA_MASIVA',
                'docentes',
                null,
                "Se cargaron de forma masiva {$rowsImported} docentes desde archivo CSV."
            );

            return $rowsImported;
        });
    }
}
