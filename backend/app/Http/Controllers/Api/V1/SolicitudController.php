<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComentarioRequest;
use App\Http\Requests\StoreSolicitudRequest;
use App\Http\Requests\UpdateSolicitudRequest;
use App\Models\Evidencia;
use App\Models\Solicitud;
use App\Models\SolicitudHistorialEstado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SolicitudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Solicitud::where('user_id', $request->user()->id)
            ->with(['evidencias']);

        // Filtro por Estado
        if ($request->filled('status') && $request->status !== 'Todos') {
            $query->where('estado', $request->status);
        }

        $solicitudes = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $solicitudes->items(),
            'meta' => [
                'current_page' => $solicitudes->currentPage(),
                'last_page' => $solicitudes->lastPage(),
                'total' => $solicitudes->total()
            ]
        ], 200);
    }

    public function store(StoreSolicitudRequest $request): JsonResponse
    {
        $solicitud = DB::transaction(function () use ($request) {
            $solicitud = Solicitud::create([
                'user_id' => $request->user()->id,
                'tipo_solicitud' => $request->tipo_solicitud,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'ubicacion' => $request->ubicacion,
                'prioridad_estimada' => $request->prioridad_estimada,
                'estado' => 'Pendiente',
            ]);

            // Registro en Historial
            SolicitudHistorialEstado::create([
                'solicitud_id' => $solicitud->id,
                'estado_nuevo' => 'Pendiente',
                'cambiado_por' => $request->user()->name,
                'observacion' => 'Registro inicial de solicitud desde la app móvil.'
            ]);

            // Guardar Evidencia Multipart si existe
            if ($request->hasFile('evidencia')) {
                $file = $request->file('evidencia');
                $path = $file->store('evidencias/' . $solicitud->id, 'public');

                Evidencia::create([
                    'solicitud_id' => $solicitud->id,
                    'nombre_archivo' => $file->getClientOriginalName(),
                    'ruta_almacenamiento' => Storage::url($path),
                    'mime_type' => $file->getClientMimeType(),
                    'tamano_bytes' => $file->getSize(),
                ]);
            }

            return $solicitud->load('evidencias');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Solicitud registrada exitosamente.',
            'data' => $solicitud
        ], 201);
    }

    public function show(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->authorize('view', $solicitud);

        $solicitud->load(['evidencias', 'historialEstados', 'comentarios.user']);

        return response()->json([
            'status' => 'success',
            'data' => $solicitud
        ], 200);
    }

    public function update(UpdateSolicitudRequest $request, Solicitud $solicitud): JsonResponse
    {
        $this->authorize('update', $solicitud);

        $solicitud->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Solicitud actualizada correctamente.',
            'data' => $solicitud
        ], 200);
    }

    public function destroy(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->authorize('delete', $solicitud);

        $solicitud->update(['estado' => 'Cancelado']);
        $solicitud->delete(); // Soft delete

        return response()->json([
            'status' => 'success',
            'message' => 'Solicitud cancelada exitosamente.'
        ], 200);
    }

    public function storeEvidencia(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->authorize('update', $solicitud);

        $request->validate([
            'evidencia' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240'
        ]);

        $file = $request->file('evidencia');
        $path = $file->store('evidencias/' . $solicitud->id, 'public');

        $evidencia = Evidencia::create([
            'solicitud_id' => $solicitud->id,
            'nombre_archivo' => $file->getClientOriginalName(),
            'ruta_almacenamiento' => Storage::url($path),
            'mime_type' => $file->getClientMimeType(),
            'tamano_bytes' => $file->getSize(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Evidencia adjuntada exitosamente.',
            'data' => $evidencia
        ], 201);
    }

    public function seguimiento(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->authorize('view', $solicitud);

        return response()->json([
            'status' => 'success',
            'data' => $solicitud->historialEstados()->get()
        ], 200);
    }

    public function comentarios(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->authorize('view', $solicitud);

        return response()->json([
            'status' => 'success',
            'data' => $solicitud->comentarios()->with('user:id,name,role')->get()
        ], 200);
    }

    public function storeComentario(StoreComentarioRequest $request, Solicitud $solicitud): JsonResponse
    {
        $this->authorize('view', $solicitud);

        $comentario = $solicitud->comentarios()->create([
            'user_id' => $request->user()->id,
            'contenido' => $request->contenido
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $comentario->load('user:id,name,role')
        ], 201);
    }
}
