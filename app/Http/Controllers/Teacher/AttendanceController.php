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
     * Store attendance records for a specific training and date.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate training_id, date and attendance array
        $request->validate([
            'training_id' => 'required|exists:trainings,training_id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,user_id',
            'attendances.*.status' => 'required|in:P,A,J',
        ]);

        // Get the training and verify ownership
        $training = \App\Models\Training::findOrFail($request->training_id);

        if ($training->teacher_id !== auth()->user()->user_id) {
            abort(403, 'No tienes permiso para registrar asistencias en esta capacitación.');
        }

        // Use transaction for atomic operations
        DB::transaction(function () use ($request, $training) {
            foreach ($request->attendances as $attendanceData) {
                // Update or create attendance record
                Attendance::updateOrCreate(
                    [
                        'training_id' => $training->training_id,
                        'student_id' => $attendanceData['student_id'],
                        'date' => $request->date,
                    ],
                    [
                        'status' => $attendanceData['status'],
                    ]
                );
            }
        });

        return redirect()
            ->route('teacher.courses')
            ->with('success', 'Asistencias guardadas correctamente para la fecha ' . $request->date);
    }
}
