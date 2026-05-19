<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Training;
// Alternativa: Asegúrate de importar tu modelo de Tareas si se llama Task o Homework
// use App\Models\Task; 

class TaskController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validamos los datos que vienen desde tu modal de tareas
        $request->validate([
            'training_id'   => 'required|exists:trainings,training_id',
            'title'         => 'required|string|max:150',
            'description'   => 'nullable|string',
            'delivery_date' => 'required|date|after_or_equal:today', 
        ]);

        $user = auth()->user();

        // 2. Seguridad: Verificamos que el curso pertenezca al profesor logueado
        $training = Training::where('training_id', $request->training_id)
            ->where('teacher_id', $user->user_id)
            ->firstOrFail();

        // 3. Guardar en la Base de Datos
        // Descomenta y ajusta esto cuando verifiques cómo se llama tu modelo/tabla de tareas
        /*
        Task::create([
            'training_id'   => $training->training_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'delivery_date' => $request->delivery_date,
        ]);
        */

        // 4. Redirección limpia a la pestaña de contenidos del curso
        return redirect()->route('teacher.courses.show', ['course' => $training->training_id, 'tab' => 'contenido'])
            ->with('success', 'Tarea publicada correctamente.');
    }
}