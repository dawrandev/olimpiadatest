@extends('layouts.admin.main')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<style>
    .info-card {
        background: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .student-checkbox-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
    }

    .student-item {
        padding: 0.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .student-item:last-child {
        border-bottom: none;
    }

    .student-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('title', __('Create Retake Test'))

@section('content')
<x-admin.breadcrumb :title="__('Create Retake Test')">
    <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-outline-primary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Assignments') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Original Assignment Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="icofont icofont-info-circle"></i>
                        {{ __('Original Test Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Language') }}:</strong>
                            <p class="mb-0">{{ $assignment->language->name }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Faculty') }}:</strong>
                            <p class="mb-0">{{ $assignment->faculty->translations->first()->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Group') }}:</strong>
                            <p class="mb-0">{{ $assignment->group->name }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Subject') }}:</strong>
                            <p class="mb-0">{{ $assignment->subject->translations->first()->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Original Questions') }}:</strong>
                            <p class="mb-0">{{ $assignment->question_count }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Original Duration') }}:</strong>
                            <p class="mb-0">{{ $assignment->duration }} {{ __('minutes') }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Retake Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="icofont icofont-refresh"></i>
                        {{ __('Create a retake test') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.test-assignments.retake.store', $assignment->id) }}" method="POST" id="retakeForm">
                        @csrf

                        <input type="hidden" name="parent_assignment_id" value="{{ $assignment->id }}">

                        <!-- Failed Students Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                {{ __('Select students who failed the assignment.') }} <span class="text-danger">*</span>
                                <span class="text-muted">({{ __('Score') }}
                                    < 60%)</span>
                            </label>

                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                    <i class="icofont icofont-ui-check"></i> {{ __('Select All') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                    <i class="icofont icofont-ui-close"></i> {{ __('Deselect All') }}
                                </button>
                                <span class="ms-3 text-muted">
                                    <strong id="selectedCount">0</strong> / {{ $failedStudents->count() }} {{ __('selected') }}
                                </span>
                            </div>

                            <div class="student-checkbox-list">
                                @forelse($failedStudents as $student)
                                <div class="student-item">
                                    <div class="form-check">
                                        <input class="form-check-input student-checkbox"
                                            type="checkbox"
                                            name="student_ids[]"
                                            value="{{ $student->id }}"
                                            id="student_{{ $student->id }}">
                                        <label class="form-check-label" for="student_{{ $student->id }}">
                                            <strong>{{ $student->full_name }}</strong>
                                            @php
                                            $result = $assignment->testResults()
                                            ->where('student_id', $student->id)
                                            ->first();
                                            $percentage = $result ? round(($result->correct_answers / $result->total_questions) * 100, 1) : 0;
                                            @endphp
                                            <span class="badge bg-danger ms-2">{{ $percentage }}%</span>
                                            <small class="text-muted ms-2">
                                                ({{ $result->correct_answers ?? 0 }}/{{ $result->total_questions ?? 0 }} {{ __('correct') }})
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                @empty
                                <div class="alert alert-info mb-0">
                                    <i class="icofont icofont-info-circle"></i>
                                    {{ __('No failed students found for this assignment') }}
                                </div>
                                @endforelse
                            </div>

                            @error('student_ids')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @if($failedStudents->isEmpty())
                            <div class="alert alert-warning mt-2">
                                <i class="icofont icofont-warning"></i>
                                {{ __('All students have passed this test. No retake needed.') }}
                            </div>
                            @endif
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Question Count -->
                            <div class="col-md-6 mb-3">
                                <label for="question_count" class="form-label fw-bold">
                                    {{ __('Number of Questions') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                    name="question_count"
                                    id="question_count"
                                    class="form-control"
                                    value="{{ old('question_count', $assignment->question_count) }}"
                                    min="5"
                                    max="100"
                                    required>
                                @error('question_count')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    {{ __('Original test had') }}: {{ $assignment->question_count }} {{ __('questions') }}
                                </small>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label fw-bold">
                                    {{ __('Duration (minutes)') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                    name="duration"
                                    id="duration"
                                    class="form-control"
                                    value="{{ old('duration', $assignment->duration) }}"
                                    min="5"
                                    max="180"
                                    required>
                                @error('duration')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    {{ __('Original test had') }}: {{ $assignment->duration }} {{ __('minutes') }}
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Time -->
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label fw-bold">
                                    {{ __('Start Time') }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local"
                                    name="start_time"
                                    id="start_time"
                                    class="form-control"
                                    value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}"
                                    required>
                                @error('start_time')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Time -->
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label fw-bold">
                                    {{ __('End Time') }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local"
                                    name="end_time"
                                    id="end_time"
                                    class="form-control"
                                    value="{{ old('end_time', now()->addDays(7)->format('Y-m-d\TH:i')) }}"
                                    required>
                                @error('end_time')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="icofont icofont-info-circle"></i>
                                    {{ __('Students will be able to take the retake test within this time range') }}
                                </small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">
                                {{ __('Description') }} <span class="text-muted">({{ __('Optional') }})</span>
                            </label>
                            <textarea name="description"
                                id="description"
                                class="form-control"
                                rows="3"
                                placeholder="{{ __('Enter additional instructions or notes...') }}">{{ old('description', $assignment->description ?? __('RETAKE: This is your second chance. Do not close the browser or navigate to another page during the test. You can only submit the test once.')) }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="is_active"
                                    id="is_active"
                                    value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    {{ __('Activate immediately') }}
                                </label>
                            </div>
                            <small class="text-muted ms-4">
                                {{ __('If activated, selected students will immediately have access to the retake test') }}
                            </small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-end pt-3 border-top">
                            <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="icofont icofont-close"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="button"
                                class="btn btn-warning confirm-action"
                                data-action="update"
                                data-title="{{ __('Are you sure you want to create a retake test?') }}"
                                data-text="{{ __('You are about to create a retake test for selected students.') }}"
                                title="{{ __('Create Retake Test') }}"
                                @if($failedStudents->isEmpty()) disabled @endif>
                                <i class="icofont icofont-refresh"></i>
                                {{ __('Create Retake Test') }}
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        const selectAllBtn = document.getElementById('selectAll');
        const deselectAllBtn = document.getElementById('deselectAll');
        const selectedCountSpan = document.getElementById('selectedCount');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('retakeForm');

        function updateSelectedCount() {
            const selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
            selectedCountSpan.textContent = selectedCount;

            if (selectedCount > 0) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        selectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateSelectedCount();
        });

        deselectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectedCount();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        form.addEventListener('submit', function(e) {
            const selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
            if (selectedCount === 0) {
                e.preventDefault();
                alert('{{ __("Please select at least one student") }}');
                return false;
            }

            if (!confirm('{{ __("Are you sure you want to create a retake assignment for") }} ' + selectedCount + ' {{ __("student(s)?") }}')) {
                e.preventDefault();
                return false;
            }
        });

        updateSelectedCount();
    });
</script>
@endpush
@endsection