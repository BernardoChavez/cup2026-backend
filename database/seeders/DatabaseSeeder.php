<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\Docente;
use App\Models\GestionAcademica;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Clear tables (in order of dependencies)
        DB::table('inscritos_grupos')->delete();
        DB::table('asistencias')->delete();
        DB::table('grupos_nivelacion')->delete();
        DB::table('calificaciones')->delete();
        DB::table('postulantes')->delete();
        DB::table('docentes')->delete();
        DB::table('usuarios')->delete();
        DB::table('carreras')->delete();
        DB::table('gestiones_academicas')->delete();
        DB::table('materias')->delete();
        DB::table('aulas')->delete();

        // 2. Seed Gestiones Académicas
        $gestion = GestionAcademica::create([
            'anio' => 2026,
            'periodo' => 'CUP-I',
            'activo' => true
        ]);

        // 3. Seed Carreras
        $carreraSistemas = Carrera::create([
            'codigo' => 'SIST',
            'nombre' => 'Ingeniería de Sistemas',
            'cupos_maximos' => 5
        ]);

        $carreraInformatica = Carrera::create([
            'codigo' => 'INF',
            'nombre' => 'Ingeniería Informática',
            'cupos_maximos' => 5
        ]);

        $carreraRedes = Carrera::create([
            'codigo' => 'REDES',
            'nombre' => 'Ingeniería de Redes y Telecomunicaciones',
            'cupos_maximos' => 3
        ]);

        // 4. Seed Materias
        Materia::create(['codigo' => 'COMP', 'nombre' => 'Computación']);
        Materia::create(['codigo' => 'MAT', 'nombre' => 'Matemáticas']);
        Materia::create(['codigo' => 'ING', 'nombre' => 'Inglés']);
        Materia::create(['codigo' => 'FIS', 'nombre' => 'Física']);

        // 5. Seed Aulas
        Aula::create(['nombre' => 'Aula 101 - FICCT', 'capacidad_maxima' => 70]);
        Aula::create(['nombre' => 'Aula 102 - FICCT', 'capacidad_maxima' => 70]);
        Aula::create(['nombre' => 'Aula 103 - FICCT', 'capacidad_maxima' => 70]);

        // 6. Seed Usuarios
        // A. Admin User
        $admin = User::create([
            'nombre' => 'Administrador',
            'apellido' => 'General',
            'email' => 'admin@cup.edu.bo',
            'password' => Hash::make('admin123'),
            'rol' => 'ADMIN',
            'activo' => true
        ]);

        // B. Coordinator User
        $coordinator = User::create([
            'nombre' => 'Coordinador',
            'apellido' => 'CUP',
            'email' => 'coordinador@cup.edu.bo',
            'password' => Hash::make('coord123'),
            'rol' => 'COORDINADOR',
            'activo' => true
        ]);

        // C. Docente User & Profile
        $userDocente = User::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'docente@cup.edu.bo',
            'password' => Hash::make('docente123'),
            'rol' => 'DOCENTE',
            'activo' => true
        ]);

        Docente::create([
            'id_usuario' => $userDocente->id,
            'profesional_area' => true,
            'maestria' => true,
            'diplomado_edu_sup' => true,
            'contratado' => true
        ]);
    }
}
