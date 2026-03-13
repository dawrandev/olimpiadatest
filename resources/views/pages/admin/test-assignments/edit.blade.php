@extends('layouts.admin.main')
@section('title', __('Edit Test Assignment'))
@section('content')

<x-admin.breadcrumb :title="__('Edit Test Assignment')">
    <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-outline-primary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to List') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="icofont icofont-edit"></i>
                        {{ __('Edit Test Assignment') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.test-assignments.update', $testAssignment) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Faculty Select -->
                            <div class="col-md-6 mb-3">
                                <label for="faculty" class="form-label fw-bold">
                                    {{ __('Faculty') }} <span class="text-danger">*</span>
                                </label>
                                <select name="faculty_id" id="faculty" class="form-select" required>
                                    <option value="">{{ __('Select Faculty') }}</option>
                                    @foreach(getFaculties() as $faculty)
                                    <option value="{{ $faculty->id }}"
                                        {{ old('faculty_id', $testAssignment->group->faculty_id) == $faculty->id ? 'selected' : '' }}>
                                        {{ optional($faculty->translations->firstWhere('language_id', currentLanguageId()))->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('faculty_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Group Select -->
                            <div class="col-md-6 mb-3">
                                <label for="group" class="form-label fw-bold">
                                    {{ __('Group') }} <span class="text-danger">*</span>
                                </label>
                                <select name="group_id" id="group" class="form-select" required>
                                    <option value="">{{ __('Select Group') }}</option>
                                    @foreach(getGroups() as $group)
                                    <option value="{{ $group->id }}"
                                        data-faculty-id="{{ $group->faculty_id }}"
                                        {{ old('group_id', $testAssignment->group_id) == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('group_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted" id="student-count">
                                    <i class="icofont icofont-info-circle"></i>
                                    {{(__('Number of Students'))}}: <span id="student-count-value">{{ $testAssignment->group->students->count() ?? 0 }}</span>
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Language Select -->
                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label fw-bold">
                                    {{ __('Language') }} <span class="text-danger">*</span>
                                </label>
                                <select name="language_id" id="language" class="form-select" required>
                                    <option value="">{{ __('Select Language') }}</option>
                                    @foreach(getLanguages() as $language)
                                    <option value="{{ $language->id }}"
                                        {{ old('language_id', $testAssignment->language_id) == $language->id ? 'selected' : '' }}>
                                        {{ $language->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('language_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Subject Select -->
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label fw-bold">
                                    {{ __('Subject') }} <span class="text-danger">*</span>
                                </label>
                                <select name="subject_id" id="subject" class="form-select" required>
                                    <option value="">{{ __('Select Subject') }}</option>
                                    @foreach(getSubjects() as $subject)
                                    <option value="{{ $subject->id }}"
                                        {{ old('subject_id', $testAssignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ optional($subject->translations->firstWhere('language_id', currentLanguageId()))->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Question Count -->
                            <div class="col-md-6 mb-3">
                                <label for="question_count" class="form-label fw-bold">
                                    {{ __('Number of Questions') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="question_count" id="question_count"
                                    class="form-control"
                                    value="{{ old('question_count', $testAssignment->question_count) }}"
                                    min="5" max="100" required>
                                @error('question_count')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted" id="available-questions">
                                    {{__('Existing questions')}}: <span id="questions-count">-</span> та
                                </small>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label fw-bold">
                                    {{ __('Duration (minutes)') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="duration" id="duration"
                                    class="form-control"
                                    value="{{ old('duration', $testAssignment->duration) }}"
                                    min="5" max="180" required>
                                @error('duration')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Time -->
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label fw-bold">
                                    {{ __('Start Time') }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="start_time" id="start_time"
                                    class="form-control"
                                    value="{{ old('start_time', $testAssignment->start_time->format('Y-m-d\TH:i')) }}"
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
                                <input type="datetime-local" name="end_time" id="end_time"
                                    class="form-control"
                                    value="{{ old('end_time', $testAssignment->end_time->format('Y-m-d\TH:i')) }}"
                                    required>
                                @error('end_time')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="icofont icofont-info-circle"></i>
                                    {{__('Students will have the opportunity to take the test during this period')}}
                                </small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">
                                {{ __('Description') }} <span class="text-muted">({{ __('Optional') }})</span>
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="3"
                                placeholder="{{ __('Enter additional instructions or notes...') }}">{{ old('description', $testAssignment->description) }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                    id="is_active" value="1"
                                    {{ old('is_active', $testAssignment->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    {{ __('Active Status') }}
                                </label>
                            </div>
                            <small class="text-muted ms-4">
                                {{ __('If activated, students will immediately have access to the test.') }}
                            </small>
                        </div>

                        <!-- Warning if test has started -->
                        @if($testAssignment->testResults()->where('status', '!=', 'not_started')->exists())
                        <div class="alert alert-warning">
                            <i class="icofont icofont-warning"></i>
                            <strong>{{ __('Warning') }}:</strong>
                            Баъзи студентлар аллақачон тестни бошлаган. Сиз фақат чекланган маълумотларни ўзгартириша оласиз.
                        </div>
                        @endif

                        <!-- Preview Summary -->
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading">
                                <i class="icofont icofont-info-circle"></i>
                                {{ __('Assignment Summary') }}
                            </h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="mb-0">
                                        <li><strong>{{ __('Group') }}:</strong> <span id="summary-group">{{ $testAssignment->group->name }}</span></li>
                                        <li><strong>{{ __('Subject') }}:</strong> <span id="summary-subject">{{ optional($testAssignment->subject->translations->firstWhere('language_id', currentLanguageId()))->name }}</span></li>
                                        <li><strong>{{ __('Language') }}:</strong> <span id="summary-language">{{ $testAssignment->language->name }}</span></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="mb-0">
                                        <li><strong>{{ __('Number of Questions') }}:</strong> <span id="summary-questions">{{ $testAssignment->question_count }}</span> </li>
                                        <li><strong>{{ __('Duration') }}:</strong> <span id="summary-duration">{{ $testAssignment->duration }}</span> {{ __('minutes') }}</li>
                                        <li><strong>{{ __('Status') }}:</strong>
                                            <span class="badge bg-{{ $testAssignment->is_active ? 'success' : 'secondary' }}">
                                                {{ $testAssignment->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-end pt-3 border-top">
                            <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="icofont icofont-close"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="icofont icofont-check"></i>
                                {{ __('Update Assignment') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const facultySelect = document.getElementById('faculty');
        const groupSelect = document.getElementById('group');
        const languageSelect = document.getElementById('language');
        const subjectSelect = document.getElementById('subject');
        const questionCountInput = document.getElementById('question_count');
        const durationInput = document.getElementById('duration');
        const startTime = document.getElementById('start_time');
        const endTime = document.getElementById('end_time');

        // Faculty change - filter groups
        facultySelect.addEventListener('change', function() {
            const facultyId = this.value;
            const allOptions = Array.from(groupSelect.options);

            allOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const optionFacultyId = option.getAttribute('data-faculty-id');
                if (facultyId === '' || optionFacultyId === facultyId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });

            // Reset group selection if current selection is hidden
            const currentOption = groupSelect.options[groupSelect.selectedIndex];
            if (currentOption && currentOption.style.display === 'none') {
                groupSelect.value = '';
            }
        });

        // Subject + Language change - check available questions
        function checkAvailableQuestions() {
            const subjectId = subjectSelect.value;
            const languageId = languageSelect.value;

            if (subjectId && languageId) {
                fetch(`{{ route('admin.ajax.questions.available') }}?subject_id=${subjectId}&language_id=${languageId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('questions-count').textContent = data.count;

                        // Update question count max value
                        questionCountInput.setAttribute('max', data.count);

                        if (parseInt(questionCountInput.value) > data.count) {
                            questionCountInput.value = data.count;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('questions-count').textContent = '-';
                    });
            }
        }

        subjectSelect.addEventListener('change', checkAvailableQuestions);
        languageSelect.addEventListener('change', checkAvailableQuestions);

        // Update summary in real-time
        groupSelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text;
            document.getElementById('summary-group').textContent = selectedText;
        });

        subjectSelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text;
            document.getElementById('summary-subject').textContent = selectedText;
        });

        languageSelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text;
            document.getElementById('summary-language').textContent = selectedText;
        });

        questionCountInput.addEventListener('input', function() {
            document.getElementById('summary-questions').textContent = this.value;
        });

        durationInput.addEventListener('input', function() {
            document.getElementById('summary-duration').textContent = this.value;
        });

        // Validate end time is after start time
        startTime.addEventListener('change', validateTimes);
        endTime.addEventListener('change', validateTimes);

        function validateTimes() {
            if (startTime.value && endTime.value) {
                if (new Date(endTime.value) <= new Date(startTime.value)) {
                    endTime.setCustomValidity('{{ __("End time must be after start time") }}');
                } else {
                    endTime.setCustomValidity('');
                }
            }
        }

        // Initialize
        facultySelect.dispatchEvent(new Event('change'));
        checkAvailableQuestions();
    });
</script>
@endpush

@push('styles')
<style>
    .icofont-3x {
        font-size: 3rem;
    }
</style>
@endpush

@endsection