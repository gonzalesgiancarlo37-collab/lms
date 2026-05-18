<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Show the form for recording attendance for a specific schedule.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Validate schedule_id exists
        $request->validate([
            'schedule_id' => 'required|exists:schedules,schedule_id',
        ]);

        // Get the schedule with its training
        $schedule = Schedule::with('training')
            ->findOrFail($request->schedule_id);

        // Verify the schedule belongs to the authenticated teacher
        if ($schedule->training->teacher_id !== auth()->user()->user_id) {
            abort(403, 'No tienes permiso para registrar asistencias en este horario.');
        }

        // Get all active enrollments for this training
        $enrollments = Enrollment::with(['student.person', 'training'])
            ->where('training_id', $schedule->training->training_id)
            ->where('status', 'A')
            ->orderBy('student_id', 'asc')
            ->get();

        // Get existing attendance records for this schedule (if any)
        $existingAttendances = Attendance::where('schedule_id', $schedule->schedule_id)
            ->pluck('attendance', 'enrollment_id')
            ->toArray();

        return view('teacher.attendance.create', compact(
            'schedule',
            'enrollments',
            'existingAttendances'
        ));
    }

    /**
     * Store attendance records for a specific schedule.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate schedule_id and attendance array
        $request->validate([
            'schedule_id' => 'required|exists:schedules,schedule_id',
            'attendances' => 'required|array',
            'attendances.*.enrollment_id' => 'required|exists:enrollments,enrollment_id',
            'attendances.*.attendance' => 'required|in:present,absent,late',
        ]);

        // Get the schedule and verify ownership
        $schedule = Schedule::with('training')
            ->findOrFail($request->schedule_id);

        if ($schedule->training->teacher_id !== auth()->user()->user_id) {
            abort(403, 'No tienes permiso para registrar asistencias en este horario.');
        }

        // Use transaction for atomic operations
        DB::transaction(function () use ($request, $schedule) {
            foreach ($request->attendances as $attendanceData) {
                // Verify the enrollment belongs to this training
                $enrollment = Enrollment::findOrFail($attendanceData['enrollment_id']);
                
                if ($enrollment->training_id !== $schedule->training->training_id) {
                    throw new \Exception('El estudiante no está inscrito en este entrenamiento.');
                }

                // Update or create attendance record
                Attendance::updateOrCreate(
                    [
                        'schedule_id' => $schedule->schedule_id,
                        'enrollment_id' => $attendanceData['enrollment_id'],
                    ],
                    [
                        'attendance' => $attendanceData['attendance'],
                    ]
                );
            }
        });

        return redirect()
            ->route('teacher.schedules.show', ['schedule' => $schedule->schedule_id])
            ->with('success', 'Asistencias registradas correctamente.');
    }
}
