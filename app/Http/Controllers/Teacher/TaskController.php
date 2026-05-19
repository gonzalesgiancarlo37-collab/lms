<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Training;
// Usamos el modelo que corresponda a tus tareas, usualmente Task
use App\Models\Task; 

class TaskController extends Controller
{
    /**
     * Almacena una nueva tarea asignada desde el modal.
     */
    public function store(Request $request)
    {
        // 1. Validar la petición entrante
        $request->validate([
            'training_id' => 'required|exists:trainings,training_id',
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string',
            'due_date'    => 'required|date|after_or_equal:today',
            // Agrega aquí más campos si tu formulario de tareas los requiere (ej. puntos, archivos)
        ]);

        $user = auth()->user();

        // 2. Verificar que el curso pertenece al profesor logueado
        $training = Training::where('training_id', $request->training_id)
            ->where('teacher_id', $user->user_id)
            ->firstOrFail();

        // 3. Crear el registro de la tarea
        Task::create([
            'training_id' => $training->training_id,
            'title'       => $request->title,
            'description' => $request->description ?? null,
            'due_date'    => $request->due_date,
        ]);

        // CORRECCIÓN: Se utiliza la clave 'id' para cumplir con {id} de la ruta teacher.courses.show
        return redirect()->route('teacher.courses.show', ['id' => $training->training_id, 'tab' => 'contenido'])
            ->with('success', 'Tarea asignada correctamente.');
    }
}