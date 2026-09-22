<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampusConnectApiTest extends TestCase
{
    use RefreshDatabase;

    private User $estudiante1;
    private User $estudiante2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->estudiante1 = User::factory()->create([
            'name' => 'Carlos Mendoza',
            'email' => 'carlos@campus.edu',
            'password' => bcrypt('password123'),
        ]);

        $this->estudiante2 = User::factory()->create([
            'name' => 'Ana Morales',
            'email' => 'ana@campus.edu',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * TEST 1: Autenticación exitosa y rechazo con credenciales inválidas.
     */
    public function test_1_autenticacion_exitosa_y_rechazo_con_credenciales_invalidas(): void
    {
        // 1. Caso Fallido: Contraseña errónea
        $responseError = $this->postJson('/api/v1/auth/login', [
            'email' => 'carlos@campus.edu',
            'password' => 'wrong_password',
        ]);
        $responseError->assertStatus(401)
                      ->assertJson(['status' => 'error']);

        // 2. Caso Exitoso: Credenciales válidas
        $responseOk = $this->postJson('/api/v1/auth/login', [
            'email' => 'carlos@campus.edu',
            'password' => 'password123',
        ]);
        $responseOk->assertStatus(200)
                   ->assertJsonStructure([
                       'status',
                       'data' => ['token', 'user' => ['id', 'email', 'name']]
                   ]);
    }

    /**
     * TEST 2: Creación exitosa de una solicitud con archivo de evidencia (multipart).
     */
    public function test_2_creacion_exitosa_de_solicitud_con_evidencia_multipart(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('fuga_laboratorio.jpg', 600, 600);

        $payload = [
            'tipo_solicitud' => 'Mantenimiento',
            'titulo' => 'Fuga de agua en Laboratorio 3',
            'ubicacion' => 'Pabellón B - Lab 3',
            'descripcion' => 'Hay una fuga constante en la tubería debajo del lavamanos.',
            'prioridad_estimada' => 'Alta',
            'evidencia' => $file,
        ];

        $response = $this->actingAs($this->estudiante1, 'sanctum')
                         ->post('/api/v1/solicitudes', $payload, [
                             'Content-Type' => 'multipart/form-data',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.titulo', 'Fuga de agua en Laboratorio 3')
                 ->assertJsonPath('data.estado', 'Pendiente');

        $this->assertDatabaseHas('solicitudes', [
            'user_id' => $this->estudiante1->id,
            'titulo' => 'Fuga de agua en Laboratorio 3',
            'estado' => 'Pendiente',
        ]);

        $this->assertDatabaseHas('evidencias', [
            'nombre_archivo' => 'fuga_laboratorio.jpg',
        ]);
    }

    /**
     * TEST 3: Validación de campos obligatorios al intentar crear solicitud vacía (Error 422).
     */
    public function test_3_validacion_de_campos_obligatorios_retorna_422(): void
    {
        $response = $this->actingAs($this->estudiante1, 'sanctum')
                         ->postJson('/api/v1/solicitudes', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'tipo_solicitud',
                     'titulo',
                     'descripcion',
                     'ubicacion',
                     'prioridad_estimada',
                 ]);
    }

    /**
     * TEST 4: Impedir que un estudiante edite o elimine una solicitud que ya está 'En Proceso'.
     */
    public function test_4_regla_negocio_impedir_editar_o_eliminar_solicitud_en_proceso(): void
    {
        $solicitudEnProceso = Solicitud::factory()->create([
            'user_id' => $this->estudiante1->id,
            'estado' => 'En Proceso',
            'tecnico_asignado' => 'Ing. Roberto Silva',
        ]);

        // Intento de Edición -> Debe ser rechazado (422)
        $responseEdit = $this->actingAs($this->estudiante1, 'sanctum')
                             ->putJson("/api/v1/solicitudes/{$solicitudEnProceso->id}", [
                                 'titulo' => 'Título Modificado Ilegal',
                             ]);
        $responseEdit->assertStatus(422);

        // Intento de Eliminación -> Debe ser rechazado (422)
        $responseDelete = $this->actingAs($this->estudiante1, 'sanctum')
                               ->deleteJson("/api/v1/solicitudes/{$solicitudEnProceso->id}");
        $responseDelete->assertStatus(422);
    }

    /**
     * TEST 5: Aislamiento de datos: verificar que un estudiante no pueda consultar ni modificar la solicitud de otro (Error 404).
     */
    public function test_5_aislamiento_de_datos_entre_estudiantes(): void
    {
        $solicitudEstudiante2 = Solicitud::factory()->create([
            'user_id' => $this->estudiante2->id,
            'titulo' => 'Soporte Laptop Sala C',
            'estado' => 'Pendiente',
        ]);

        // Estudiante 1 intenta CONSULTAR solicitud de Estudiante 2 -> 404 (ocultar existencia)
        $responseGet = $this->actingAs($this->estudiante1, 'sanctum')
                            ->getJson("/api/v1/solicitudes/{$solicitudEstudiante2->id}");
        $responseGet->assertStatus(404);

        // Estudiante 1 intenta MODIFICAR solicitud de Estudiante 2 -> 404
        $responsePut = $this->actingAs($this->estudiante1, 'sanctum')
                            ->putJson("/api/v1/solicitudes/{$solicitudEstudiante2->id}", [
                                'titulo' => 'Intento Hackeo Título',
                            ]);
        $responsePut->assertStatus(404);

        // Estudiante 1 intenta ELIMINAR solicitud de Estudiante 2 -> 404
        $responseDel = $this->actingAs($this->estudiante1, 'sanctum')
                            ->deleteJson("/api/v1/solicitudes/{$solicitudEstudiante2->id}");
        $responseDel->assertStatus(404);
    }
}
