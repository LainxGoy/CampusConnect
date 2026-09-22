<?php

namespace Database\Seeders;

use App\Models\Comentario;
use App\Models\HistorialSolicitud;
use App\Models\Recurso;
use App\Models\Role;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles institucionales
        $rolEstudiante = Role::updateOrCreate(
            ['nombre' => 'estudiante'],
            ['etiqueta' => 'Estudiante', 'descripcion' => 'Alumnos que registran solicitudes y consultan seguimiento']
        );

        $rolAdmin = Role::updateOrCreate(
            ['nombre' => 'administrativo'],
            ['etiqueta' => 'Personal Administrativo', 'descripcion' => 'Gestores de recursos, priorización y métricas']
        );

        $rolTecnico = Role::updateOrCreate(
            ['nombre' => 'tecnico'],
            ['etiqueta' => 'Responsable Técnico', 'descripcion' => 'Técnicos encargados de soporte y mantenimiento']
        );

        // 2. Usuarios de prueba
        $admin = User::updateOrCreate(
            ['email' => 'admin@campusconnect.edu'],
            [
                'name' => 'Coordinación Administrativa',
                'password' => Hash::make('password123'),
                'role_id' => $rolAdmin->id,
                'telefono' => '+52 555 123 4567',
                'is_active' => true,
            ]
        );

        $tecnico = User::updateOrCreate(
            ['email' => 'tecnico@campusconnect.edu'],
            [
                'name' => 'Ing. Carlos Mendoza (Soporte TI)',
                'password' => Hash::make('password123'),
                'role_id' => $rolTecnico->id,
                'telefono' => '+52 555 987 6543',
                'is_active' => true,
            ]
        );

        $estudiante = User::updateOrCreate(
            ['email' => 'estudiante@campusconnect.edu'],
            [
                'name' => 'Ana Morales Gómez',
                'codigo_estudiantil' => 'EST-2026-904',
                'password' => Hash::make('password123'),
                'role_id' => $rolEstudiante->id,
                'telefono' => '+52 555 333 2211',
                'is_active' => true,
            ]
        );

        // 3. Catálogo de Recursos (Infraestructura y Equipamiento)
        $aula101 = Recurso::updateOrCreate(
            ['codigo' => 'AUL-101'],
            [
                'nombre' => 'Aula Magna 101',
                'tipo' => 'infraestructura',
                'categoria' => 'Aulas',
                'ubicacion' => 'Edificio Central, Piso 1',
                'estado' => 'operativo',
                'descripcion' => 'Capacidad para 80 estudiantes, con pizarra interactiva y acústica tratada.',
            ]
        );

        $lab02 = Recurso::updateOrCreate(
            ['codigo' => 'LAB-02'],
            [
                'nombre' => 'Laboratorio de Cómputo Avanzado',
                'tipo' => 'infraestructura',
                'categoria' => 'Laboratorios',
                'ubicacion' => 'Edificio de Ingeniería, Piso 2',
                'estado' => 'operativo',
                'descripcion' => '30 puestos con workstations para diseño e ingeniería de software.',
            ]
        );

        $proyector = Recurso::updateOrCreate(
            ['codigo' => 'EQ-PROY-01'],
            [
                'nombre' => 'Proyector Láser Epson 4K',
                'tipo' => 'equipamiento',
                'categoria' => 'Audiovisual',
                'ubicacion' => 'Aula Magna 101',
                'estado' => 'mantenimiento',
                'descripcion' => 'Presenta parpadeo ocasional en el puerto HDMI 1.',
            ]
        );

        $aireAcond = Recurso::updateOrCreate(
            ['codigo' => 'EQ-AC-102'],
            [
                'nombre' => 'Aire Acondicionado Central 24000 BTU',
                'tipo' => 'equipamiento',
                'categoria' => 'Climatización',
                'ubicacion' => 'Laboratorio de Cómputo Avanzado',
                'estado' => 'operativo',
                'descripcion' => 'Mantenimiento preventivo semestral al día.',
            ]
        );

        // 4. Solicitud de prueba con trazabilidad y comentarios
        $solicitud1 = Solicitud::updateOrCreate(
            ['codigo_ticket' => 'TKT-202609-0001'],
            [
                'titulo' => 'Falla en proyección HDMI en Aula Magna 101',
                'descripcion' => 'El proyector no reconoce la señal de laptops por cable HDMI y la imagen se distorsiona con líneas verdes.',
                'tipo' => 'equipamiento',
                'prioridad' => 'alta',
                'estado' => 'en_proceso',
                'user_id' => $estudiante->id,
                'responsable_id' => $tecnico->id,
                'recurso_id' => $proyector->id,
            ]
        );

        HistorialSolicitud::firstOrCreate([
            'solicitud_id' => $solicitud1->id,
            'estado_nuevo' => 'pendiente',
        ], [
            'user_id' => $estudiante->id,
            'estado_anterior' => null,
            'nota' => 'Solicitud registrada desde la aplicación móvil.',
        ]);

        HistorialSolicitud::firstOrCreate([
            'solicitud_id' => $solicitud1->id,
            'estado_nuevo' => 'en_proceso',
        ], [
            'user_id' => $tecnico->id,
            'estado_anterior' => 'pendiente',
            'nota' => 'Ticket asignado al técnico de turno. Se programa inspección del cableado y lente.',
        ]);

        Comentario::firstOrCreate([
            'solicitud_id' => $solicitud1->id,
            'mensaje' => 'Hola, ya reemplazamos el cable HDMI de pared y estamos diagnosticando el módulo de entrada del proyector.',
        ], [
            'user_id' => $tecnico->id,
            'es_interno' => false,
        ]);

        // Solicitud 2: Pendiente
        Solicitud::updateOrCreate(
            ['codigo_ticket' => 'TKT-202609-0002'],
            [
                'titulo' => 'Filtro de aire acondicionado requiere limpieza',
                'descripcion' => 'Se percibe olor a humedad al encender la unidad de climatización del Laboratorio 2.',
                'tipo' => 'mantenimiento',
                'prioridad' => 'media',
                'estado' => 'pendiente',
                'user_id' => $estudiante->id,
                'responsable_id' => null,
                'recurso_id' => $aireAcond->id,
            ]
        );

        // Solicitud 3: Resuelta
        Solicitud::updateOrCreate(
            ['codigo_ticket' => 'TKT-202609-0003'],
            [
                'titulo' => 'Reparación de cerradura en puerta lateral Aula 101',
                'descripcion' => 'La manija de la puerta de emergencia se encontraba atascada.',
                'tipo' => 'infraestructura',
                'prioridad' => 'urgente',
                'estado' => 'resuelto',
                'user_id' => $estudiante->id,
                'responsable_id' => $tecnico->id,
                'recurso_id' => $aula101->id,
                'fecha_resolucion' => now()->subDay(),
            ]
        );
    }
}
