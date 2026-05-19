<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index()
    {
        $studentId = Auth::user()->user_id;

        // Eager loading completo incluyendo .person para evitar queries ocultas en las vistas
        $enrollments = Enrollment::with([
            'training.course',
            'training.teacher.person'
        ])
            ->where('student_id', $studentId)
            ->get();

        $totalCourses = $enrollments->count();
        
        // Dinamizamos las métricas leyendo los estados reales ('C' de Completed, 'A' de Active)
        $completed = $enrollments->where('status', 'C')->count();
        $inProgress = $enrollments->where('status', 'A')->count();

        return view('student.dashboard', compact(
            'enrollments',
            'totalCourses',
            'completed',
            'inProgress'
        ));
    }

    public function courses()
    {
        $studentId = Auth::user()->user_id;

        // Cambiamos el enfoque: Partimos desde Enrollment para resolver todo en 1 sola query limpia
        $enrollments = Enrollment::with([
            'training.course',
            'training.teacher.person',
            'progress' // Cargamos la relación de progreso si existe
        ])
            ->where('student_id', $studentId)
            ->get()
            ->map(function ($enrollment) {
                // El progreso se calcula de forma segura basándose en el estado o en tu tabla de progreso
                // Si usas tu lógica de estado 'C' (Completado) a nivel de inscripción:
                $enrollment->progress_percentage = $enrollment->status === 'C' ? 100 : 0;

                // Nota: Si en el futuro calculas el progreso por tareas completadas, 
                // podrás hacerlo aquí usando la relación $enrollment->progress sin romper la vista.
                
                return $enrollment;
            });

        return view('student.courses.index', compact('enrollments'));
    }
}