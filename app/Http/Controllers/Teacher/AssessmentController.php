<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Training;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\Alternative; // Actualizado: Cambiado de Option a Alternative
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $trainings = Training::with('course')
            ->where('teacher_id', $user->user_id)
            ->get();

        return view('teacher.assessments.index', compact('trainings'));
    }

    public function show($id)
    {
        $user = auth()->user();

        // Actualizado: Cambiado 'assessments.questions.options' a 'assessments.questions.alternatives'
        $training = Training::with(['course', 'assessments.questions.alternatives'])
            ->where('training_id', $id)
            ->where('teacher_id', $user->user_id)
            ->firstOrFail();

        return view('teacher.assessments.manage', compact('training'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'training_id' => 'required|exists:trainings,training_id',
            'title' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'allowed_attempts' => 'required|integer|min:1',
            'time_limit' => 'nullable|integer|min:1',
            'active' => 'sometimes|boolean',
        ]);

        $user = auth()->user();

        $training = Training::where('training_id', $request->training_id)
            ->where('teacher_id', $user->user_id)
            ->firstOrFail();

        Assessment::create([
            'training_id' => $training->training_id,
            'title' => $request->title,
            'description' => $request->description ?? null,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'allowed_attempts' => $request->allowed_attempts,
            'time_limit' => ($request->time_limit && $request->time_limit > 0) ? $request->time_limit : 60,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('teacher.assessments.show', $training->training_id)
            ->with('success', 'Evaluación creada correctamente.');
    }

    public function addQuestion(Request $request, $assessment_id)
    {
        // Refactorizado: Validación limpia adaptada a alternatives
        $request->validate([
            'question_text' => 'required|string',
            'score' => 'required|integer|min:0',
            'alternatives' => 'required|array|min:2',
            'alternatives.*.text' => 'required|string',
            'correct_alternative' => 'required|integer|min:0',
        ]);

        $user = auth()->user();

        $assessment = Assessment::with('training')
            ->where('assessment_id', $assessment_id)
            ->firstOrFail();

        if ($assessment->training->teacher_id !== $user->user_id) {
            abort(403, 'No autorizado.');
        }

        // Ejecución segura mediante transacción
        DB::transaction(function () use ($request, $assessment) {
            $question = Question::create([
                'assessment_id' => $assessment->assessment_id,
                'question_text' => $request->question_text,
                'score' => $request->score,
                'order_index' => $assessment->questions()->count() + 1,
            ]);

            foreach ($request->alternatives as $index => $alternativeData) {
                Alternative::create([
                    'question_id' => $question->question_id,
                    'option_text'  => $alternativeData['text'],
                    'is_correct'   => $request->correct_alternative == $index,
                ]);
            }
        });

        return redirect()->route('teacher.assessments.show', $assessment->training->training_id)
            ->with('success', 'Pregunta agregada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'allowed_attempts' => 'required|integer|min:1',
            'time_limit' => 'nullable|integer|min:1',
            'active' => 'sometimes|boolean',
        ]);

        $user = auth()->user();

        $assessment = Assessment::with('training')
            ->where('assessment_id', $id)
            ->firstOrFail();

        if ($assessment->training->teacher_id !== $user->user_id) {
            abort(403, 'No autorizado.');
        }

        if ($assessment->attempts()->exists()) {
            return redirect()->back()->withErrors([
                'assessment' => 'No se puede modificar una evaluación que ya tiene intentos registrados'
            ]);
        }

        $assessment->update([
            'title' => $request->title,
            'description' => $request->description ?? null,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'time_limit' => ($request->time_limit && $request->time_limit > 0) ? $request->time_limit : ($assessment->time_limit > 0 ? $assessment->time_limit : 60),
            'allowed_attempts' => $request->allowed_attempts,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('teacher.assessments.show', $assessment->training->training_id)
            ->with('success', 'Evaluación actualizada correctamente.');
    }

    public function destroy($id)
    {
        $user = auth()->user();

        $assessment = Assessment::with('training')
            ->where('assessment_id', $id)
            ->firstOrFail();

        if ($assessment->training->teacher_id !== $user->user_id) {
            abort(403, 'No autorizado.');
        }

        if ($assessment->attempts()->exists()) {
            return redirect()->back()->withErrors([
                'assessment' => 'No se puede eliminar una evaluación que ya tiene intentos registrados.'
            ]);
        }

        $trainingId = $assessment->training->training_id;
        $assessment->delete();

        return redirect()->route('teacher.assessments.show', $trainingId)
            ->with('success', 'Evaluación eliminada correctamente.');
    }
}