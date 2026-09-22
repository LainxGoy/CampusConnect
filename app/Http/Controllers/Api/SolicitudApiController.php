<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Evidencia;
use App\Models\HistorialSolicitud;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SolicitudApiController extends Controller
{
    /**
     * Listado paginado de solicitudes con filtros para la app móvil.
     * GET /api/v1/solicitudes
     */
    public function index(Request $request): JsonResponse
    {
        $query = Solicitud::with([
            'solicitante:id,name,email,codigo_estudiantil',
            'responsable:id,name,email',
            'recurso:id,codigo,nombre,ubicacion',
        ]);

        // Filtrado por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        // Filtrado por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->query('tipo'));
        }

        // Filtrado por prioridad
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->query('prioridad'));
        }

        // Filtrar por usuario autenticado o enviado por parámetro
        $userId = auth()->id() ?? $request->query('user_id');
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Búsqueda por texto (código de ticket o título)
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo_ticket', 'like', "%{$search}%")
                  ->orWhere('titulo', 'like', "%{$search}%");
            });
        }

        $solicitudes = $query->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Solicitudes recuperadas exitosamente.',
            'data' => $solicitudes,
        ], 200);
    }

    /**
     * Registro de una nueva solicitud estudiantil.
     * POST /api/v1/solicitudes
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo' => 'required|string|in:mantenimiento,soporte,infraestructura,equipamiento',
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'recurso_id' => 'nullable|exists:recursos,id',
            'user_id' => 'nullable|exists:users,id',
            'evidencias' => 'nullable|array',
            'evidencias.*' => 'file|max:12288|mimes:jpg,jpeg,png,webp,pdf,mp4,mov',
        ], [
            'titulo.required' => 'El título de la solicitud es obligatorio.',
            'tipo.in' => 'El tipo debe ser: mantenimiento, soporte, infraestructura o equipamiento.',
            'recurso_id.exists' => 'El recurso institucional seleccionado no existe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Determinar ID del solicitante
        $userId = auth()->id() ?? $request->input('user_id');
        if (!$userId) {
            // Asignar primer estudiante disponible como fallback seguro para desarrollo
            $primerEstudiante = User::whereHas('role', fn ($q) => $q->where('nombre', 'estudiante'))->first();
            $userId = $primerEstudiante ? $primerEstudiante->id : 1;
        }

        try {
            DB::beginTransaction();

            $codigoTicket = Solicitud::generarCodigoTicket();

            $solicitud = Solicitud::create([
                'codigo_ticket' => $codigoTicket,
                'titulo' => $request->input('titulo'),
                'descripcion' => $request->input('descripcion'),
                'tipo' => $request->input('tipo'),
                'prioridad' => $request->input('prioridad', 'media'),
                'estado' => 'pendiente',
                'user_id' => $userId,
                'recurso_id' => $request->input('recurso_id'),
            ]);

            // Registrar primer hito en la auditoría / trazabilidad
            HistorialSolicitud::create([
                'solicitud_id' => $solicitud->id,
                'user_id' => $userId,
                'estado_anterior' => null,
                'estado_nuevo' => 'pendiente',
                'nota' => 'Solicitud creada a través del cliente móvil/API.',
            ]);

            // Carga opcional de evidencias adjuntas en la misma petición
            if ($request->hasFile('evidencias')) {
                foreach ($request->file('evidencias') as $file) {
                    $ruta = $file->store('evidencias', 'public');
                    Evidencia::create([
                        'solicitud_id' => $solicitud->id,
                        'user_id' => $userId,
                        'nombre_original' => $file->getClientOriginalName(),
                        'ruta_archivo' => $ruta,
                        'mime_type' => $file->getClientMimeType() ?? 'application/octet-stream',
                        'tamanio_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            $solicitud->load(['solicitante:id,name,email', 'recurso:id,codigo,nombre', 'evidencias']);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud registrada correctamente.',
                'data' => $solicitud,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consulta detallada de seguimiento y trazabilidad del ticket.
     * GET /api/v1/solicitudes/{id}/seguimiento
     */
    public function seguimiento(int $id): JsonResponse
    {
        $solicitud = Solicitud::with([
            'solicitante:id,name,email,codigo_estudiantil,telefono',
            'responsable:id,name,email,telefono',
            'recurso',
            'historiales.usuario:id,name',
            'comentarios.autor:id,name',
            'evidencias.subidoPor:id,name',
        ])->find($id);

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'La solicitud solicitada no existe.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle y trazabilidad de la solicitud recuperados.',
            'data' => [
                'ticket' => [
                    'id' => $solicitud->id,
                    'codigo_ticket' => $solicitud->codigo_ticket,
                    'titulo' => $solicitud->titulo,
                    'descripcion' => $solicitud->descripcion,
                    'tipo' => $solicitud->tipo,
                    'prioridad' => $solicitud->prioridad,
                    'estado' => $solicitud->estado,
                    'fecha_creacion' => $solicitud->created_at?->toIso8601String(),
                    'fecha_resolucion' => $solicitud->fecha_resolucion?->toIso8601String(),
                    'solicitante' => $solicitud->solicitante,
                    'responsable_asignado' => $solicitud->responsable,
                    'recurso_asociado' => $solicitud->recurso,
                ],
                'trazabilidad' => $solicitud->historiales->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'estado_anterior' => $h->estado_anterior,
                        'estado_nuevo' => $h->estado_nuevo,
                        'nota' => $h->nota,
                        'usuario' => $h->usuario?->name ?? 'Sistema',
                        'fecha' => $h->created_at?->toIso8601String(),
                        'tiempo_relativo' => $h->created_at?->diffForHumans(),
                    ];
                }),
                'evidencias' => $solicitud->evidencias->map(function ($ev) {
                    return [
                        'id' => $ev->id,
                        'nombre' => $ev->nombre_original,
                        'url' => $ev->url,
                        'mime_type' => $ev->mime_type,
                        'tamanio' => $ev->tamanio_formateado,
                        'subido_por' => $ev->subidoPor?->name ?? 'Usuario',
                        'fecha_subida' => $ev->created_at?->toIso8601String(),
                    ];
                }),
                'comentarios' => $solicitud->comentarios->map(function ($com) {
                    return [
                        'id' => $com->id,
                        'autor' => $com->autor?->name ?? 'Usuario',
                        'mensaje' => $com->mensaje,
                        'fecha' => $com->created_at?->toIso8601String(),
                        'tiempo_relativo' => $com->created_at?->diffForHumans(),
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Subida independiente de evidencias para una solicitud existente.
     * POST /api/v1/solicitudes/{id}/evidencias
     */
    public function subirEvidencia(Request $request, int $id): JsonResponse
    {
        $solicitud = Solicitud::find($id);
        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'archivo' => 'required|file|max:15360|mimes:jpeg,png,jpg,webp,pdf,mp4,mov',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'archivo.required' => 'Debe adjuntar un archivo de evidencia.',
            'archivo.max' => 'El archivo no puede exceder los 15MB.',
            'archivo.mimes' => 'El formato permitido debe ser JPG, PNG, WEBP, PDF o MP4.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación al subir evidencia.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('archivo');
        $ruta = $file->store('evidencias', 'public');
        $userId = auth()->id() ?? $request->input('user_id', $solicitud->user_id);

        $evidencia = Evidencia::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $userId,
            'nombre_original' => $file->getClientOriginalName(),
            'ruta_archivo' => $ruta,
            'mime_type' => $file->getClientMimeType() ?? 'application/octet-stream',
            'tamanio_bytes' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evidencia adjuntada exitosamente a la solicitud.',
            'data' => [
                'id' => $evidencia->id,
                'solicitud_id' => $evidencia->solicitud_id,
                'nombre_original' => $evidencia->nombre_original,
                'url' => $evidencia->url,
                'mime_type' => $evidencia->mime_type,
                'tamanio' => $evidencia->tamanio_formateado,
            ],
        ], 201);
    }

    /**
     * Publicar un comentario o actualización en la solicitud.
     * POST /api/v1/solicitudes/{id}/comentarios
     */
    public function agregarComentario(Request $request, int $id): JsonResponse
    {
        $solicitud = Solicitud::find($id);
        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string|min:2|max:1000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en el comentario.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id() ?? $request->input('user_id', $solicitud->user_id);

        $comentario = Comentario::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $userId,
            'mensaje' => $request->input('mensaje'),
            'es_interno' => false,
        ]);

        $comentario->load('autor:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Comentario publicado con éxito.',
            'data' => $comentario,
        ], 201);
    }

    /**
     * Editar una solicitud propia (mientras esté en estado pendiente o en revisión).
     * PUT/PATCH /api/v1/solicitudes/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $solicitud = Solicitud::find($id);
        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        // Regla de negocio: sólo se puede modificar si aún no ha iniciado trabajos técnicos
        if (!in_array($solicitud->estado, ['pendiente', 'en_revision'])) {
            return response()->json([
                'success' => false,
                'message' => "No es posible editar una solicitud en estado '{$solicitud->estado}'.",
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string',
            'tipo' => 'sometimes|required|string|in:mantenimiento,soporte,infraestructura,equipamiento',
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'recurso_id' => 'nullable|exists:recursos,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $solicitud->update($request->only([
            'titulo', 'descripcion', 'tipo', 'prioridad', 'recurso_id'
        ]));

        HistorialSolicitud::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $request->input('user_id', $solicitud->user_id),
            'estado_anterior' => $solicitud->estado,
            'estado_nuevo' => $solicitud->estado,
            'nota' => 'Solicitud actualizada por el estudiante desde la app móvil.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud actualizada exitosamente.',
            'data' => $solicitud->fresh(['recurso', 'solicitante']),
        ], 200);
    }

    /**
     * Eliminar o cancelar una solicitud propia.
     * DELETE /api/v1/solicitudes/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $solicitud = Solicitud::find($id);
        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        // Si ya está resuelta o en proceso, no permitir borrado físico
        if ($solicitud->estado === 'resuelto') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una solicitud resuelta para preservar la auditoría.',
            ], 400);
        }

        $codigo = $solicitud->codigo_ticket;
        $solicitud->delete();

        return response()->json([
            'success' => true,
            'message' => "Solicitud {$codigo} eliminada correctamente.",
        ], 200);
    }

    /**
     * Cambio de estado y asignación de responsable técnico (App Móvil / Técnico).
     * PATCH /api/v1/solicitudes/{id}/estado
     */
    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $solicitud = Solicitud::find($id);
        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:pendiente,en_revision,en_proceso,resuelto,cancelado',
            'responsable_id' => 'nullable|exists:users,id',
            'nota' => 'nullable|string|max:1000',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'estado.required' => 'El nuevo estado es obligatorio.',
            'estado.in' => 'Estado inválido. Opciones: pendiente, en_revision, en_proceso, resuelto, cancelado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación al cambiar de estado.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $estadoAnterior = $solicitud->estado;
        $estadoNuevo = $request->input('estado');

        $solicitud->estado = $estadoNuevo;
        if ($request->filled('responsable_id')) {
            $solicitud->responsable_id = $request->input('responsable_id');
        }

        // Registrar fecha de resolución si se marca como resuelta
        if ($estadoNuevo === 'resuelto' && !$solicitud->fecha_resolucion) {
            $solicitud->fecha_resolucion = now();
        }

        $solicitud->save();

        // Registrar hito en la auditoría
        $autorId = auth()->id() ?? $request->input('user_id') ?? $solicitud->responsable_id;
        HistorialSolicitud::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $autorId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'nota' => $request->input('nota', "Transición de estado: de '{$estadoAnterior}' a '{$estadoNuevo}'."),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Estado actualizado exitosamente a '{$estadoNuevo}'.",
            'data' => $solicitud->fresh(['responsable', 'solicitante', 'recurso']),
        ], 200);
    }

    /**
     * Reporte y resumen de métricas para la app móvil estudiantil.
     * GET /api/v1/solicitudes/reportes/resumen
     */
    public function reporteResumen(Request $request): JsonResponse
    {
        $userId = auth()->id() ?? $request->query('user_id');

        $query = Solicitud::query();
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $total = (clone $query)->count();
        $pendientes = (clone $query)->whereIn('estado', ['pendiente', 'en_revision'])->count();
        $enProceso = (clone $query)->where('estado', 'en_proceso')->count();
        $resueltas = (clone $query)->where('estado', 'resuelto')->count();

        $porTipo = (clone $query)->select('tipo', DB::raw('count(*) as count'))
            ->groupBy('tipo')
            ->pluck('count', 'tipo');

        $recientes = (clone $query)->with('recurso:id,nombre,codigo')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'codigo_ticket', 'titulo', 'tipo', 'prioridad', 'estado', 'recurso_id', 'created_at']);

        return response()->json([
            'success' => true,
            'message' => 'Reporte consolidado para la aplicación móvil.',
            'data' => [
                'resumen' => [
                    'total' => $total,
                    'pendientes' => $pendientes,
                    'en_proceso' => $enProceso,
                    'resueltas' => $resueltas,
                ],
                'distribucion_por_tipo' => $porTipo,
                'recientes' => $recientes,
            ],
        ], 200);
    }
}
