@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-1">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">
                    <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>{{ $training->course->title }}
                </h1>
                <p class="text-muted small mb-0">Panel de estudio del alumno. Accede a tus clases, materiales, tareas y cuestionarios.</p>
            </div>
            <div class="text-end">
                <a href="{{ route('student.courses.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left-circle me-1"></i>Volver a mis cursos
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header bg-white border-bottom">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('student.courses.show', $training->training_id) }}?tab=inicio"
                            class="nav-link @if(request('tab', 'inicio') === 'inicio') active @endif" id="inicio-tab" role="tab">
                            <i class="bi bi-house-fill me-2"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('student.courses.show', $training->training_id) }}?tab=evaluaciones"
                            class="nav-link @if(request('tab') === 'evaluaciones') active @endif" id="evaluaciones-tab" role="tab">
                            <i class="bi bi-clipboard-check-fill me-2"></i>Evaluaciones
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('student.courses.show', $training->training_id) }}?tab=tareas"
                            class="nav-link @if(request('tab') === 'tareas') active @endif" id="tareas-tab" role="tab">
                            <i class="bi bi-book-fill me-2"></i>Contenido/Tareas
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">

                {{-- CONTENIDO: INICIO --}}
                @if(request('tab', 'inicio') === 'inicio')
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <a href="{{ route('student.courses.show', $training->training_id) }}?tab=evaluaciones" class="text-decoration-none">
                                <div class="card border-start border-primary border-3 shadow-sm h-100 transition-all" style="cursor: pointer;">
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-dark mb-2">
                                            <i class="bi bi-patch-question text-primary me-2"></i>Rendir Evaluaciones
                                        </h5>
                                        <p class="card-text text-muted small">Tienes {{ $training->assessments->count() }} cuestionarios listados en el sistema.</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="{{ route('student.courses.show', $training->training_id) }}?tab=tareas" class="text-decoration-none">
                                <div class="card border-start border-success border-3 shadow-sm h-100 transition-all" style="cursor: pointer;">
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-dark mb-2">
                                            <i class="bi bi-file-earmark-arrow-up text-success me-2"></i>Entregar Mis Archivos
                                        </h5>
                                        <p class="card-text text-muted small">Sube tus soluciones en formatos PDF, Word o planillas Excel.</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card border-start border-warning border-3 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-dark mb-3">
                                        <i class="bi bi-info-circle text-warning me-2"></i>Información del Aula
                                    </h5>
                                    <div class="small">
                                        <p class="mb-2"><strong>Instructor:</strong> {{ $training->teacher->person->first_names ?? $training->teacher->name ?? 'Por asignar' }}</p>
                                        <p class="mb-2"><strong>Modalidad:</strong> {{ ucfirst($training->modality ?? 'No definida') }}</p>
                                        <p class="mb-0"><strong>Estado académico:</strong> <span class="badge bg-success">Activo</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card border-start border-info border-3 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-dark mb-2">
                                        <i class="bi bi-card-text text-info me-2"></i>Resumen Teórico
                                    </h5>
                                    <p class="text-muted small mb-0" style="line-height: 1.5;">
                                        {{ $training->course->description ?? 'No hay una descripción detallada disponible para este curso todavía.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                {{-- CONTENIDO: EVALUACIONES --}}
                @elseif(request('tab') === 'evaluaciones')
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-1">Cuestionarios Disponibles</h5>
                        <p class="text-muted small">Respeta el tiempo configurado por el docente al abrir tu intento.</p>
                    </div>

                    @if($training->assessments->isEmpty())
                        <div class="alert alert-light border py-4 text-center text-muted small" role="alert">
                            <i class="bi bi-inbox fs-4 d-block mb-2 text-secondary"></i> No hay evaluaciones programadas para este curso.
                        </div>
                    @else
                        <div class="list-group gap-2 mb-4">
                            @foreach($training->assessments as $assessment)
                                <div class="list-group-item list-group-item-action rounded border p-3 bg-white">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">{{ $assessment->title }}</h6>
                                            <p class="text-muted small mb-2">{{ $assessment->description ?? 'Sin instrucciones adicionales.' }}</p>
                                            <div class="d-flex gap-3 flex-wrap text-muted" style="font-size: 0.75rem;">
                                                <span><i class="bi bi-calendar-play me-1"></i>Desde: {{ $assessment->start_date ? \Carbon\Carbon::parse($assessment->start_date)->format('d/m/Y') : 'Abierto' }}</span>
                                                <span><i class="bi bi-calendar-x me-1"></i>Hasta: {{ $assessment->end_date ? \Carbon\Carbon::parse($assessment->end_date)->format('d/m/Y') : 'Abierto' }}</span>
                                                <span><i class="bi bi-hourglass-split me-1"></i>Duración: <strong>{{ $assessment->time_limit }} min</strong></span>
                                            </div>
                                        </div>
                                        <div>
                                            @if($assessment->active)
                                                <a href="{{ route('student.assessment.take', $assessment->assessment_id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil-square me-1"></i>Tomar examen
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <h5 class="fw-bold text-dark mt-4 mb-2">Mi Historial de Rendimiento</h5>
                    @if(isset($attempts) && $attempts->isNotEmpty())
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Evaluación</th>
                                        <th>Fecha de Entrega</th>
                                        <th class="text-center">Calificación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td class="ps-3 fw-bold text-secondary">{{ $attempt->assessment->title ?? 'Cuestionario' }}</td>
                                            <td>{{ optional($attempt->created_at)->format('d/m/Y H:i') }}</td>
                                            <td class="text-center fw-bold text-dark">{{ $attempt->score }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border text-center text-muted py-3 small">
                            No registras ningún intento completado todavía.
                        </div>
                    @endif

                {{-- CONTENIDO: TAREAS (SOPORTE DE ARCHIVOS REALES MULTIPART) --}}
                @elseif(request('tab') === 'tareas')
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-1">Contenido y Tareas Asignadas</h5>
                        <p class="text-muted small">Carga tus entregables directamente en los recuadros correspondientes.</p>
                    </div>

                    @if(!$training->tasks || $training->tasks->isEmpty())
                        <div class="alert alert-light border py-4 text-center text-muted small" role="alert">
                            <i class="bi bi-folder-x fs-4 d-block mb-2 text-secondary"></i> No se han publicado asignaciones de tareas para este curso.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($training->tasks as $task)
                                @php
                                    $submission = $task->submissions()->where('student_id', auth()->id())->first();
                                @endphp
                                <div class="col-12">
                                    <div class="card border shadow-sm rounded">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center g-3">
                                                <div class="col-lg-7">
                                                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-text-fill text-success me-2"></i>{{ $task->title }}</h6>
                                                    <p class="text-muted small mb-3">{{ $task->description }}</p>
                                                    <div class="d-flex gap-3 text-muted flex-wrap" style="font-size: 0.75rem;">
                                                        <span><i class="bi bi-calendar-event text-danger me-1"></i>Límite: <strong>{{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y H:i') }}</strong></span>
                                                        <span><i class="bi bi-star me-1"></i>Puntaje Máximo: <strong>20 pts</strong></span>
                                                    </div>
                                                </div>

                                                <div class="col-lg-5">
                                                    <div class="p-3 bg-light rounded border small">
                                                        @if($submission)
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Archivo Recibido</span>
                                                                <span class="badge {{ isset($submission->grade) ? 'bg-primary' : 'bg-warning text-dark' }} px-2">
                                                                    {{ isset($submission->grade) ? 'Nota: '.$submission->grade.'/20' : 'Por revisar' }}
                                                                </span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="btn btn-sm btn-outline-dark w-100 text-start text-truncate">
                                                                    <i class="bi bi-download me-2 text-primary"></i>Descargar mi entrega
                                                                </a>
                                                            </div>
                                                            @if($submission->teacher_feedback)
                                                                <div class="p-2 bg-white rounded border border-dashed mt-2" style="font-size: 0.72rem;">
                                                                    <strong class="text-dark d-block">Retroalimentación:</strong>
                                                                    <span class="text-secondary">{{ $submission->teacher_feedback }}</span>
                                                                </div>
                                                            @endif
                                                        @else
                                                            {{-- FORMULARIO CON SOPORTE PARA ARCHIVOS --}}
                                                            <form action="{{ route('student.tasks.submit', $task->task_id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.75rem;">Subir Solución (PDF, Word, Excel, ZIP):</label>
                                                                <div class="input-group input-group-sm mb-1">
                                                                    <input type="file" name="task_file" class="form-control" required>
                                                                    <button class="btn btn-success fw-bold" type="submit"><i class="bi bi-cloud-upload-fill me-1"></i>Enviar</button>
                                                                </div>
                                                                <span class="text-muted d-block" style="font-size: 0.65rem;">Tamaño límite sugerido: 12MB por archivo.</span>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
@endsection