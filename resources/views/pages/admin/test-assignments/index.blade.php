@extends('layouts.admin.main')
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df;
    }

    .border-left-success {
        border-left: 4px solid #1cc88a;
    }

    .border-left-warning {
        border-left: 4px solid #f6c23e;
    }

    .border-left-secondary {
        border-left: 4px solid #858796;
    }

    .icofont-3x {
        font-size: 3rem;
    }

    .icofont-5x {
        font-size: 5rem;
    }

    .progress {
        background-color: #e9ecef;
    }

    /* Retake badge uchun */
    .badge-retake {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    .retake-row {
        background-color: #fff8e1;
    }
</style>
@endpush
@section('title', __('Test Assignments'))
@section('content')

<x-admin.breadcrumb :title="__('Test Assignments')">
    <a href="{{ route('admin.test-assignments.create') }}" class="btn btn-primary">
        <i class="icofont icofont-plus"></i>
        {{ __('Create New Assignment') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="icofont icofont-check-circled"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="icofont icofont-warning"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="icofont icofont-info-circle"></i>
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Assignments') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $assignments->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="icofont icofont-file-document icofont-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Active') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $assignments->where('is_active', true)->where('end_time', '>', now())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="icofont icofont-check-circled icofont-3x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Scheduled') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $assignments->where('start_time', '>', now())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="icofont icofont-clock-time icofont-3x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-secondary shadow h-100">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                {{ __('Completed') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $assignments->where('end_time', '<', now())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="icofont icofont-flag icofont-3x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.test-assignments.index') }}" class="row g-3">
                <!-- Language -->
                <div class="col-md-2">
                    <label for="filter_language" class="form-label fw-bold">{{ __('Language') }}</label>
                    <select name="language_id" id="filter_language" class="form-select js-select2">
                        <option value="">{{ __('Select Language') }}</option>
                        @foreach(getLanguages() as $lang)
                        <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
                            {{ $lang->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Group -->
                <div class="col-md-2">
                    <label for="filter_group" class="form-label fw-bold">{{ __('Group') }}</label>
                    <select name="group_id" id="filter_group" class="form-select js-select2" disabled>
                        <option value="">{{ __('Select Group') }}</option>
                    </select>
                </div>

                <!-- Subject -->
                <div class="col-md-2">
                    <label for="filter_subject" class="form-label fw-bold">{{ __('Subject') }}</label>
                    <select name="subject_id" id="filter_subject" class="form-select js-select2" disabled>
                        <option value="">{{ __('Select Subject') }}</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="col-md-2">
                    <label for="date_from" class="form-label fw-bold">{{ __('Date From') }}</label>
                    <input type="date" name="date_from" id="date_from" class="form-control"
                        value="{{ request('date_from') }}">
                </div>

                <!-- Date To -->
                <div class="col-md-2">
                    <label for="date_to" class="form-label fw-bold">{{ __('Date To') }}</label>
                    <input type="date" name="date_to" id="date_to" class="form-control"
                        value="{{ request('date_to') }}">
                </div>

                <!-- Status -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="icofont icofont-filter"></i> {{ __('Filter') }}
                    </button>
                    <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-secondary">
                        <i class="icofont icofont-refresh"></i> {{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('All Assignments') }}</h5>
        </div>
        <div class="card-body p-0">
            @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">{{ __('Subject') }}</th>
                            <th width="10%">{{ __('Group') }}</th>
                            <th width="10%">{{ __('Language') }}</th>
                            <th width="10%" class="text-center">{{ __('Questions') }}</th>
                            <th width="10%" class="text-center">{{ __('Duration') }}</th>
                            <th width="15%">{{ __('Time Period') }}</th>
                            <th width="10%" class="text-center">{{ __('Status') }}</th>
                            <th width="15%" class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $index => $assignment)
                        <tr class="{{ $assignment->is_retake ? 'retake-row' : '' }}">
                            <td>{{ $assignments->firstItem() + $index }}</td>

                            <!-- Subject with Retake Badge -->
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">
                                        {{ optional($assignment->subject->translations->firstWhere('language_id', currentLanguageId()))->name ?? __('N/A') }}
                                    </span>

                                    @if($assignment->is_retake)
                                    <span class="badge bg-warning text-dark badge-retake mt-1">
                                        <i class="icofont icofont-refresh"></i> {{ __('Retake') }}
                                    </span>
                                    @if($assignment->parentAssignment)
                                    <small class="text-muted mt-1">
                                        <i class="icofont icofont-link"></i>
                                        <a href="{{ route('admin.test-assignments.show', $assignment->parent_assignment_id) }}"
                                            class="text-decoration-none"
                                            title="{{ __('View Original Test') }}">
                                            {{ __('Original Test') }} #{{ $assignment->parent_assignment_id }}
                                        </a>
                                    </small>
                                    @endif
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-info">{{ $assignment->group->name }}</span>
                            </td>

                            <td>
                                <span class="badge bg-secondary">{{ $assignment->language->name }}</span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-primary">{{ $assignment->question_count }}</span>
                            </td>

                            <td class="text-center">
                                <i class="icofont icofont-clock-time"></i>
                                {{ $assignment->duration }} {{ __('min') }}
                            </td>

                            <td>
                                <small class="d-block">
                                    <i class="icofont icofont-calendar"></i>
                                    {{ $assignment->start_time->format('d.m.Y H:i') }}
                                </small>
                                <small class="d-block">
                                    <i class="icofont icofont-flag"></i>
                                    {{ $assignment->end_time->format('d.m.Y H:i') }}
                                </small>
                            </td>

                            <td class="text-center">
                                @if($assignment->start_time > now())
                                <span class="badge bg-warning">
                                    <i class="icofont icofont-clock-time"></i> {{ __('Scheduled') }}
                                </span>
                                @elseif($assignment->end_time < now())
                                    <span class="badge bg-secondary">
                                    <i class="icofont icofont-flag"></i> {{ __('Completed') }}
                                    </span>
                                    @elseif($assignment->is_active)
                                    <span class="badge bg-success">
                                        <i class="icofont icofont-check-circled"></i> {{ __('Active') }}
                                    </span>
                                    @else
                                    <span class="badge bg-danger">
                                        <i class="icofont icofont-close"></i> {{ __('Inactive') }}
                                    </span>
                                    @endif
                            </td>

                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- View Details -->
                                    <a href="{{ route('admin.test-assignments.show', $assignment) }}"
                                        class="btn btn-outline-info"
                                        title="{{ __('View Details') }}">
                                        <i class="icofont icofont-eye"></i>
                                    </a>

                                    <!-- View Results -->
                                    <a href="{{ route('admin.test-assignments.results', $assignment) }}"
                                        class="btn btn-outline-success"
                                        title="{{ __('View Results') }}">
                                        <i class="icofont icofont-chart-bar-graph"></i>
                                    </a>

                                    <!-- Edit -->
                                    @if($assignment->completed_count == 0)
                                    <a href="{{ route('admin.test-assignments.edit', $assignment) }}"
                                        class="btn btn-outline-warning"
                                        title="{{ __('Edit') }}">
                                        <i class="icofont icofont-edit"></i>
                                    </a>
                                    @endif

                                    <!-- Retake (faqat original test lar uchun va yiqilgan studentlar bo'lsa) -->
                                    @if(!$assignment->is_retake && $assignment->failed_count > 0)
                                    <a href="{{ route('admin.test-assignments.retake.create', $assignment->id) }}"
                                        class="btn btn-outline-primary"
                                        title="{{ __('Create Retake') }} ({{ $assignment->failed_count }} {{ __('failed') }})">
                                        <i class="icofont icofont-refresh"></i>
                                    </a>
                                    @endif

                                    @if($assignment->end_time > now())
                                    <form action="{{ route('admin.test-assignments.toggle-status', $assignment) }}"
                                        method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="button"
                                            class="btn btn-outline-{{ $assignment->is_active ? 'secondary' : 'primary' }} confirm-action"
                                            data-action="toggle"
                                            data-title="{{ $assignment->is_active ? __('Deactivate assignment?') : __('Activate assignment?') }}"
                                            data-text="{{ $assignment->is_active ? __('This will deactivate the assignment') : __('This will activate the assignment') }}"
                                            title="{{ $assignment->is_active ? __('Deactivate') : __('Activate') }}">
                                            <i class="icofont icofont-{{ $assignment->is_active ? 'close' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    @endif

                                    @if($assignment->completed_count == 0)
                                    <form action="{{ route('admin.test-assignments.destroy', $assignment) }}"
                                        method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="btn btn-outline-danger confirm-action"
                                            data-action="delete"
                                            data-title="{{ __('Are you sure you want to delete this assignment?') }}"
                                            data-text="{{ __('This action cannot be undone!') }}"
                                            title="{{ __('Delete') }}">
                                            <i class="icofont icofont-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                {{ $assignments->links() }}
            </div>
            @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="icofont icofont-file-document icofont-5x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No test assignments found') }}</h5>
                <p class="text-muted">{{ __('Create your first test assignment to get started') }}</p>
                <a href="{{ route('admin.test-assignments.create') }}" class="btn btn-primary mt-3">
                    <i class="icofont icofont-plus"></i>
                    {{ __('Create Assignment') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const $language = $('#filter_language');
        const $group = $('#filter_group');
        const $subject = $('#filter_subject');

        // Select2 initialization
        if ($.fn.select2) {
            $('.js-select2').select2({
                width: '100%'
            });
        }

        const urls = {
            faculties: "{{ route('admin.ajax.faculties.byLanguage', '__LANG__') }}",
            groups: "{{ route('admin.ajax.groups.byFaculty', '__FACULTY__') }}",
            subjects: "{{ route('admin.ajax.subjects.byLanguage', '__LANG__') }}"
        };

        // Reset function
        function reset($el, placeholder, disable = true) {
            $el.empty().append($('<option>', {
                value: '',
                text: placeholder
            }));
            $el.prop('disabled', disable);
            if ($.fn.select2) {
                $el.trigger('change.select2');
            }
        }

        // Initial state
        reset($group, "{{ __('Select Group') }}", true);
        reset($subject, "{{ __('Select Subject') }}", true);

        // Language change handler
        $language.on('change', function() {
            const langId = $(this).val();

            if (!langId) {
                reset($group, "{{ __('Select Group') }}", true);
                reset($subject, "{{ __('Select Subject') }}", true);
                return;
            }

            reset($group, "{{ __('Loading...') }}", true);
            reset($subject, "{{ __('Loading...') }}", true);

            // Load Faculties and Groups
            const facultiesUrl = urls.faculties.replace('__LANG__', langId);

            $.ajax({
                url: facultiesUrl,
                method: 'GET',
                dataType: 'json',
                success: function(faculties) {
                    if (!Array.isArray(faculties) || faculties.length === 0) {
                        reset($group, "{{ __('Select Group') }}", true);
                        return;
                    }

                    const groupRequests = faculties.map(function(faculty) {
                        return $.ajax({
                            url: urls.groups.replace('__FACULTY__', faculty.id),
                            method: 'GET',
                            dataType: 'json'
                        });
                    });

                    $.when.apply($, groupRequests).done(function() {
                        let allGroups = [];

                        if (groupRequests.length === 1) {
                            allGroups = Array.isArray(arguments[0]) ? arguments[0] : [];
                        } else {
                            for (let i = 0; i < arguments.length; i++) {
                                if (Array.isArray(arguments[i][0])) {
                                    allGroups = allGroups.concat(arguments[i][0]);
                                }
                            }
                        }

                        $group.empty().append($('<option>', {
                            value: '',
                            text: "{{ __('Select Group') }}"
                        }));

                        allGroups.forEach(function(group) {
                            $group.append($('<option>', {
                                value: group.id,
                                text: group.name,
                                selected: "{{ request('group_id') }}" == group.id
                            }));
                        });

                        $group.prop('disabled', false);
                        if ($.fn.select2) $group.trigger('change.select2');
                    }).fail(function() {
                        reset($group, "{{ __('Select Group') }}", true);
                    });
                },
                error: function() {
                    reset($group, "{{ __('Select Group') }}", true);
                }
            });

            // Load Subjects
            $.ajax({
                url: urls.subjects.replace('__LANG__', langId),
                method: 'GET',
                dataType: 'json',
                success: function(subjects) {
                    $subject.empty().append($('<option>', {
                        value: '',
                        text: "{{ __('Select Subject') }}"
                    }));

                    if (Array.isArray(subjects)) {
                        subjects.forEach(function(subject) {
                            let name = (subject.translations && subject.translations.length > 0) ?
                                subject.translations[0].name :
                                subject.name || "{{ __('N/A') }}";

                            $subject.append($('<option>', {
                                value: subject.id,
                                text: name,
                                selected: "{{ request('subject_id') }}" == subject.id
                            }));
                        });
                    }

                    $subject.prop('disabled', false);
                    if ($.fn.select2) $subject.trigger('change.select2');
                },
                error: function() {
                    reset($subject, "{{ __('Select Subject') }}", true);
                }
            });
        });

        // Auto-trigger on page load
        const selectedLang = $language.val();
        if (selectedLang) {
            setTimeout(function() {
                $language.trigger('change');
            }, 300);
        }
    });
</script>
<script>
    window.alertTranslations = {
        areYouSure: "{{ __('Are you sure?') }}",
        cannotUndo: "{{ __('This action cannot be undone!') }}",
        yesDelete: "{{ __('Yes, delete it!') }}",
        yesUpdate: "{{ __('Yes, update!') }}",
        yesConfirm: "{{ __('Yes, confirm!') }}",
        cancel: "{{ __('Cancel') }}"
    };
</script>
@endpush
@endsection