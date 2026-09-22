<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecursoController extends Controller
{
    /**
     * Muestra el catálogo de recursos institucionales.
     */
    public function index(Request $request): View
    {
        $query = Recurso::query();

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->query('tipo'));
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        // Búsqueda por texto libre
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('ubicacion', 'like', "%{$search}%");
            });
        }

        $recursos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('recursos.index', compact('recursos'));
    }

    /**
     * Formulario para registrar un nuevo recurso.
     */
    public function create(): View
    {
        return view('recursos.create');
    }

    /**
     * Guarda el nuevo recurso en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:recursos,codigo',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:infraestructura,equipamiento',
            'categoria' => 'nullable|string|max:100',
            'ubicacion' => 'required|string|max:255',
            'estado' => 'required|string|in:operativo,mantenimiento,fuera_servicio',
            'descripcion' => 'nullable|string|max:1000',
        ], [
            'codigo.required' => 'El código de identificación es obligatorio.',
            'codigo.unique' => 'Ya existe un recurso con este código.',
            'nombre.required' => 'El nombre del recurso es obligatorio.',
            'ubicacion.required' => 'La ubicación física es requerida.',
        ]);

        Recurso::create($validated);

        return redirect()->route('recursos.index')
            ->with('success', '¡Recurso institucional registrado exitosamente!');
    }

    /**
     * Formulario de edición del recurso.
     */
    public function edit(Recurso $recurso): View
    {
        return view('recursos.edit', compact('recurso'));
    }

    /**
     * Actualiza la información del recurso.
     */
    public function update(Request $request, Recurso $recurso): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:recursos,codigo,' . $recurso->id,
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:infraestructura,equipamiento',
            'categoria' => 'nullable|string|max:100',
            'ubicacion' => 'required|string|max:255',
            'estado' => 'required|string|in:operativo,mantenimiento,fuera_servicio',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $recurso->update($validated);

        return redirect()->route('recursos.index')
            ->with('success', 'Recurso actualizado satisfactoriamente.');
    }

    /**
     * Elimina el recurso del catálogo.
     */
    public function destroy(Recurso $recurso): RedirectResponse
    {
        if ($recurso->solicitudes()->exists()) {
            return redirect()->route('recursos.index')
                ->with('error', 'No se puede eliminar el recurso porque tiene solicitudes de servicio vinculadas.');
        }

        $recurso->delete();

        return redirect()->route('recursos.index')
            ->with('success', 'Recurso eliminado correctamente.');
    }
}
