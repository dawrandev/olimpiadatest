@extends('layouts.admin.main')
@section('title', __('Student Details'))
@section('content')

<x-admin.breadcrumb :title="__('Student Details')">
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary btn-sm me-2">
        <i class="icofont icofont-arrow-left"></i>
        {{__('Back to Students')}}
    </a>
    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-warning btn-sm me-2">
        <i class="icofont icofont-edit"></i>
        {{__('Edit Student')}}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <!-- Activity Summary Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="icofont icofont-chart-histogram"></i>
                        {{ __('Activity Summary') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <div class="border rounded p-4 h-100 bg-light">
                                <div class="display-4 text-primary mb-2">
                                    <i class="icofont icofont-file-document"></i>
                                </div>
                                <div class="fw-bold fs-2 text-dark">{{ $allResults->count() }}</div>
                                <small class="text-muted text-uppercase fw-semibold">{{ __('Total Tests') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <div class="border rounded p-4 h-100 bg-light">
                                <div class="display-4 text-success mb-2">
                                    <i class="icofont icofont-check"></i>
                                </div>
                                <div class="fw-bold fs-2 text-dark">{{ $allResults->where('status', 'completed')->count() }}</div>
                                <small class="text-muted text-uppercase fw-semibold">{{ __('Completed Tests') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <div class="border rounded p-4 h-100 bg-light">
                                <div class="display-4 text-warning mb-2">
                                    <i class="icofont icofont-star"></i>
                                </div>
                                <div class="fw-bold fs-2 text-dark">
                                    @if($allResults->where('status', 'completed')->count() > 0)
                                    {{ round($allResults->where('status', 'completed')->avg('score'), 1) }}%
                                    @else
                                    0%
                                    @endif
                                </div>
                                <small class="text-muted text-uppercase fw-semibold">{{ __('Average Score') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-4 h-100 bg-light">
                                <div class="display-4 text-info mb-2">
                                    <i class="icofont icofont-trophy"></i>
                                </div>
                                <div class="fw-bold fs-2 text-dark">{{ $allResults->max('score') ?? 0 }}%</div>
                                <small class="text-muted text-uppercase fw-semibold">{{ __('Best Score') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Information Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="icofont icofont-student"></i>
                        {{ __('Student Information') }}
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">{{ __('Full Name') }}</label>
                                <div class="fw-bold fs-4 text-dark">
                                    <i class="icofont icofont-user text-primary me-2"></i>
                                    {{ $student->full_name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">{{ __('Login') }}</label>
                                <div class="fw-semibold fs-6">
                                    <i class="icofont icofont-key text-info me-2"></i>
                                    {{ $student->user->login }}
                                </div>
                            </div>

                            <div class="mb-4 mb-md-0">
                                <label class="form-label text-muted small fw-semibold text-uppercase">{{ __('Student ID') }}</label>
                                <div class="fw-semibold fs-6">
                                    <i class="icofont icofont-id-card text-secondary me-2"></i>
                                    #{{ str_pad($student->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">{{ __('Group') }}</label>
                                <div class="fw-semibold fs-6">
                                    <i class="icofont icofont-users text-primary me-2"></i>
                                    <span>{{ $student->group->name }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">{{ __('Faculty') }}</label>
                                <div class="fw-semibold fs-6">
                                    <i class="icofont icofont-building text-warning me-2"></i>
                                    <span>
                                        {{ optional($student->group->faculty->translations->firstWhere('language_id', currentLanguageId()))->name ?? __('Not specified') }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">{{ __('Status') }}</label>
                                <div>
                                    <span class="badge bg-success fs-10 px-3 py-2">
                                        <i class="icofont icofont-check-circle me-1"></i>
                                        {{ __('Active') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            @if($student->updated_at != $student->created_at)
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">{{ __('Last Updated') }}</label>
                                <div class="fw-semibold fs-6">
                                    <i class="icofont icofont-calendar text-warning me-2"></i>
                                    {{ $student->updated_at->format('d.m.Y') }}
                                    <small class="text-muted ms-2">{{ $student->updated_at->format('H:i') }}</small>
                                </div>
                            </div>
                            @endif
                            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-warning btn-sm py-1 px-2 flex-fill" style="min-width: 120px;">
                                    <i class="icofont icofont-edit"></i> {{ __('Edit') }}
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 flex-fill" data-bs-toggle="modal" data-bs-target="#deleteModal" style="min-width: 120px;">
                                    <i class="icofont icofont-delete"></i> {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Results Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="icofont icofont-chart-bar-graph"></i>
                            {{ __('Test Results') }}
                        </h5>
                        <span class="badge bg-light text-success">
                            {{ $testResults->count() }} {{ __('Tests') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        @if($testResults->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th>{{ __('Subject') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ __('Type') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ __('Language') }}</th>
                                        <th class="text-center" style="width: 130px;">{{ __('Date') }}</th>
                                        <th class="text-center" style="width: 90px;">{{ __('Score') }}</th>
                                        <th class="text-center" style="width: 120px;">{{ __('Questions') }}</th>
                                        <th class="text-center" style="width: 120px;">{{ __('Status') }}</th>
                                        <th class="text-center" style="width: 140px;">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($testResults as $index => $result)
                                    <tr class="{{ $result->testAssignment->is_retake ? 'table-warning' : '' }}">
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="icofont icofont-book-alt text-primary me-2 fs-5"></i>
                                                <strong>{{ optional($result->testAssignment->subject->translations->where('language_id', $result->testAssignment->language_id)->first())->name ?? __('No subject') }}</strong>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($result->testAssignment->is_retake)
                                            <span class="badge bg-warning text-dark" title="{{ __('Retake Test') }}">
                                                <i class="icofont icofont-refresh"></i>
                                            </span>
                                            @else
                                            <span class="badge bg-primary" title="{{ __('Original Test') }}">
                                                <i class="icofont icofont-file-document"></i>
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                {{ $result->testAssignment->language->name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="small">
                                                <i class="icofont icofont-calendar text-muted"></i>
                                                {{ $result->completed_at ? $result->completed_at->format('d.m.Y') : __('Not completed') }}
                                            </div>
                                            @if($result->completed_at)
                                            <div class="small text-muted">
                                                <i class="icofont icofont-clock-time"></i>
                                                {{ $result->completed_at->format('H:i') }}
                                            </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                            $scoreClass = 'text-danger';
                                            $scoreBg = 'bg-danger';

                                            if ($result->score >= 90) {
                                            $scoreClass = 'text-success';
                                            $scoreBg = 'bg-success';
                                            } elseif ($result->score >= 70) {
                                            $scoreClass = 'text-primary';
                                            $scoreBg = 'bg-primary';
                                            } elseif ($result->score >= 60) {
                                            $scoreClass = 'text-warning';
                                            $scoreBg = 'bg-warning';
                                            } else {
                                            $scoreClass = 'text-danger';
                                            $scoreBg = 'bg-danger';
                                            }
                                            @endphp

                                            <span class="badge {{ $scoreBg }} fs-10">{{ $result->score }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">
                                                <i class="icofont icofont-check text-success"></i>
                                                <strong>{{ $result->correct_answers }}</strong>
                                                <span class="text-muted">/</span>
                                                {{ $result->total_questions }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($result->status == 'completed')
                                            <span class="badge bg-success">
                                                <i class="icofont icofont-check-circle"></i>
                                                {{ __('Completed') }}
                                            </span>
                                            @elseif($result->status == 'in_progress')
                                            <span class="badge bg-warning">
                                                <i class="icofont icofont-clock-time"></i>
                                                {{ __('In Progress') }}
                                            </span>
                                            @else
                                            <span class="badge bg-secondary">
                                                <i class="icofont icofont-ui-timer"></i>
                                                {{ __('Not Started') }}
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($result->status == 'completed')
                                            <a href="{{ route('admin.test-assignments.student-detail', ['testAssignment' => $result->test_assignment_id, 'testResult' => $result->id]) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="icofont icofont-eye"></i>
                                            </a>
                                            @else
                                            <span class="text-muted small">{{ __('Not available') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="icofont icofont-file-document text-muted" style="font-size: 80px; opacity: 0.3;"></i>
                            <h5 class="text-muted mt-3 mb-2">{{ __('No test results found') }}</h5>
                            <p class="text-muted">{{ __('This student has not taken any tests yet.') }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="d-flex justify-content-end">
                        {{ $testResults->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="icofont icofont-warning"></i>
                        {{ __('Delete Student') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger border-danger">
                        <i class="icofont icofont-warning-alt me-2"></i>
                        <strong>{{ __('This action cannot be undone!') }}</strong>
                    </div>
                    <p class="mb-3">{{ __('Are you sure you want to permanently delete') }} <strong>{{ $student->full_name }}</strong>?</p>
                    <ul class="text-muted mb-0">
                        <li>{{ __('All student data will be permanently deleted') }}</li>
                        <li>{{ __('Test results will be removed') }}</li>
                        <li>{{ __('Progress and scores will be lost') }}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="icofont icofont-close"></i>
                        {{ __('Cancel') }}
                    </button>
                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you absolutely sure?') }}')">
                            <i class="icofont icofont-delete"></i>
                            {{ __('Delete Forever') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            background-color: #f8f9fa;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 0.4em 0.75em;
            font-weight: 500;
        }

        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }

        .card-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        /* Retake row styling */
        .table-warning {
            background-color: rgba(255, 193, 7, 0.15) !important;
        }

        .table-warning:hover {
            background-color: rgba(255, 193, 7, 0.25) !important;
        }

        @media print {

            .btn,
            .breadcrumb,
            .card-header {
                display: none !important;
            }

            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>
    @endpush

    @endsection