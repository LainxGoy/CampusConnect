<?php

namespace Tests\Feature;

use App\Models\Recurso;
use App\Models\Role;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampusConnectApiAndWebTest extends TestCase
{
    use RefreshDatabase;

    protected User $estudiante;
    protected Recurso $recurso;

    protected function setUp(): void
    {
        parent::setUp();

        $rolEstudiante = Role::firstOrCreate(['nombre' => 'estudiante'], ['etiqueta' => 'Estudiante']);
        Role::firstOrCreate(['nombre' => 'administrativo'], ['etiqueta' => 'Administrativo']);
        Role::firstOrCreate(['nombre' => 'tecnico'], ['etiqueta' => 'Técnico']);

        $this->estudiante = User::firstOrCreate(
            ['email' => 'alumno@campusconnect.edu'],
            ['name' => 'Alumno Test', 'password' => 'secret123', 'role_id' => $rolEstudiante->id]
        );

        $this->recurso = Recurso::firstOrCreate(
            ['codigo' => 'REC-TEST-01'],
            [
                'nombre' => 'Proyector Sala 1',
                'tipo' => 'equipamiento',
                'categoria' => 'Audiovisual',
                'ubicacion' => 'Edificio Central',
                'estado' => 'operativo',
            ]
        );
    }

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Panel de Métricas y Operaciones');
    }

    public function test_recursos_index_and_creation(): void
    {
        $response = $this->get('/recursos');
        $response->assertStatus(200);
        $response->assertSee('Catálogo de Recursos');

        $recursoData = [
            'codigo' => 'TEST-AC-01',
            'nombre' => 'Aire Split Test',
            'tipo' => 'equipamiento',
            'categoria' => 'Climatización',
            'ubicacion' => 'Edificio Test Piso 1',
            'estado' => 'operativo',
            'descripcion' => 'Equipo de pruebas automatizadas',
        ];

        $postResponse = $this->post('/recursos', $recursoData);
        $postResponse->assertRedirect('/recursos');
        $this->assertDatabaseHas('recursos', ['codigo' => 'TEST-AC-01']);
    }

    public function test_api_can_list_solicitudes(): void
    {
        $response = $this->getJson('/api/v1/solicitudes');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                ],
            ]);
    }

    public function test_api_can_create_solicitud(): void
    {
        $payload = [
            'titulo' => 'Proyector sin señal en Sala 3',
            'descripcion' => 'El proyector no enciende la lámpara principal.',
            'tipo' => 'equipamiento',
            'prioridad' => 'alta',
            'user_id' => $this->estudiante->id,
            'recurso_id' => $this->recurso->id,
        ];

        $response = $this->postJson('/api/v1/solicitudes', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('solicitudes', [
            'titulo' => 'Proyector sin señal en Sala 3',
            'estado' => 'pendiente',
        ]);
    }

    public function test_api_can_consult_seguimiento_and_upload_evidence(): void
    {
        Storage::fake('public');

        // Crear una solicitud previa
        $solicitud = Solicitud::create([
            'codigo_ticket' => 'TKT-TEST-001',
            'titulo' => 'Aire acondicionado con fuga de agua',
            'descripcion' => 'Gotea sobre el escritorio del profesor.',
            'tipo' => 'mantenimiento',
            'prioridad' => 'urgente',
            'estado' => 'en_proceso',
            'user_id' => $this->estudiante->id,
            'recurso_id' => $this->recurso->id,
        ]);

        // 1. Consultar seguimiento
        $response = $this->getJson("/api/v1/solicitudes/{$solicitud->id}/seguimiento");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'ticket',
                    'trazabilidad',
                    'evidencias',
                    'comentarios',
                ],
            ]);

        // 2. Subir evidencia
        $file = UploadedFile::fake()->create('foto_falla.jpg', 500, 'image/jpeg');

        $uploadResponse = $this->postJson("/api/v1/solicitudes/{$solicitud->id}/evidencias", [
            'archivo' => $file,
            'user_id' => $this->estudiante->id,
        ]);

        $uploadResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nombre_original' => 'foto_falla.jpg',
                ],
            ]);
    }

    public function test_api_can_update_and_delete_solicitud(): void
    {
        $solicitud = Solicitud::create([
            'codigo_ticket' => 'TKT-EDIT-001',
            'titulo' => 'Título Original',
            'descripcion' => 'Descripción Original',
            'tipo' => 'soporte',
            'prioridad' => 'baja',
            'estado' => 'pendiente',
            'user_id' => $this->estudiante->id,
        ]);

        // Editar
        $updateResponse = $this->putJson("/api/v1/solicitudes/{$solicitud->id}", [
            'titulo' => 'Título Modificado por Alumno',
            'prioridad' => 'alta',
        ]);
        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('solicitudes', ['titulo' => 'Título Modificado por Alumno']);

        // Eliminar
        $deleteResponse = $this->deleteJson("/api/v1/solicitudes/{$solicitud->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('solicitudes', ['id' => $solicitud->id]);
    }

    public function test_api_can_change_estado_and_get_reports(): void
    {
        $tecnico = User::create([
            'name' => 'Tecnico Juan',
            'email' => 'tecnico_juan@campusconnect.edu',
            'password' => 'secret123',
        ]);

        $solicitud = Solicitud::create([
            'codigo_ticket' => 'TKT-STATUS-001',
            'titulo' => 'Reparar lámpara',
            'descripcion' => 'Lámpara fundida',
            'tipo' => 'mantenimiento',
            'prioridad' => 'media',
            'estado' => 'pendiente',
            'user_id' => $this->estudiante->id,
        ]);

        // Cambiar estado a 'en_proceso' con técnico asignado
        $statusResponse = $this->patchJson("/api/v1/solicitudes/{$solicitud->id}/estado", [
            'estado' => 'en_proceso',
            'responsable_id' => $tecnico->id,
            'nota' => 'Técnico se traslada al lugar.',
        ]);
        $statusResponse->assertStatus(200);
        $this->assertEquals('en_proceso', $solicitud->fresh()->estado);
        $this->assertEquals($tecnico->id, $solicitud->fresh()->responsable_id);

        // Reporte resumen para móvil
        $reportResponse = $this->getJson('/api/v1/solicitudes/reportes/resumen');
        $reportResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'resumen' => ['total', 'pendientes', 'en_proceso', 'resueltas'],
                    'distribucion_por_tipo',
                    'recientes',
                ],
            ]);
    }
}
