@extends('layouts.admin.main')
@section('title', __('Test Assignment Details'))
@section('content')

<x-admin.breadcrumb :title="__('Test Assignment Details')">
    <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-outline-primary btn-sm me-2">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to List') }}
    </a>
    <a href="{{ route('admin.test-assignments.edit', $testAssignment) }}" class="btn btn-outline-warning btn-sm me-2">
        <i class="icofont icofont-edit"></i>
        {{ __('Edit') }}
    </a>
    <a href="{{ route('admin.test-assignments.results', $testAssignment) }}" class="btn btn-outline-success btn-sm">
        <i class="icofont icofont-chart-bar-graph"></i>
        {{ __('View Results') }}
    </a>
</x-admin.breadcrumb>


<div class="container-fluid">
    <!-- Retake Alert (agar bu retake bo'lsa) -->
    @if($testAssignment->is_retake && $testAssignment->parentAssignment)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="icofont icofont-refresh icofont-2x me-3"></i>
            <div class="text-dark">
                <h5 class="alert-heading mb-1">
                    <i class="icofont icofont-info-circle"></i>
                    {{ __('This is a Retake Assignment') }}
                </h5>
                <p class="mb-0">
                    {{ __('This retake was created for students who failed the original test.') }}
                    <a href="{{ route('admin.test-assignments.show', $testAssignment->parent_assignment_id) }}"
                        class="alert-link fw-bold text-dark">
                        {{ __('View Original Test') }} #{{ $testAssignment->parent_assignment_id }}
                    </a>
                </p>
            </div>

        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Retake Available Alert (agar yiqilgan studentlar bo'lsa) -->
    @if(!$testAssignment->is_retake)
    @php
    $failedCount = $testAssignment->testResults()
    ->where('status', 'completed')
    ->whereRaw('(correct_answers * 100 / total_questions) < 60')
        ->count();
        @endphp

        @if($failedCount > 0)
        <div class="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
            <div>
                <i class="icofont icofont-warning-alt"></i>
                <strong>{{ $failedCount }}</strong> {{ __('student(s) failed this test') }}
                <span class="text-muted">({{ __('Score < 60%') }})</span>
            </div>
            <a href="{{ route('admin.test-assignments.retake.create', $testAssignment->id) }}"
                class="btn btn-warning btn-sm">
                <i class="icofont icofont-refresh"></i>
                {{ __('Create Retake Assignment') }}
            </a>
        </div>
        @endif
        @endif

        <!-- Main Info Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="icofont icofont-file-document"></i>
                                {{ optional($testAssignment->subject->translations->firstWhere('language_id', currentLanguageId()))->name }}

                                @if($testAssignment->is_retake)
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="icofont icofont-refresh"></i> {{ __('RETAKE') }}
                                </span>
                                @endif
                            </h4>
                            @if($testAssignment->is_active)
                            <span class="badge bg-success fs-6">
                                <i class="icofont icofont-check-circled"></i>
                                {{ __('Active') }}
                            </span>
                            @else
                            <span class="badge bg-secondary fs-6">
                                <i class="icofont icofont-close-circled"></i>
                                {{ __('Inactive') }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%"><i class="icofont icofont-users text-primary"></i> {{ __('Group') }}:</th>
                                        <td><strong>{{ $testAssignment->group->name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th><i class="icofont icofont-building text-info"></i> {{ __('Faculty') }}:</th>
                                        <td>{{ optional($testAssignment->group->faculty->translations->firstWhere('language_id', currentLanguageId()))->name }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="icofont icofont-book text-success"></i> {{ __('Subject') }}:</th>
                                        <td>{{ optional($testAssignment->subject->translations->firstWhere('language_id', currentLanguageId()))->name }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="icofont icofont-world text-warning"></i> {{ __('Language') }}:</th>
                                        <td>{{ $testAssignment->language->name }}</td>
                                    </tr>
                                    @if($testAssignment->is_retake)
                                    <tr>
                                        <th><i class="icofont icofont-link text-primary"></i> {{ __('Assignment Type') }}:</th>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <i class="icofont icofont-refresh"></i> {{ __('Retake Assignment') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%"><i class="icofont icofont-question-circle text-primary"></i> {{ __('Questions') }}:</th>
                                        <td><strong>{{ $testAssignment->question_count }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th><i class="icofont icofont-clock-time text-success"></i> {{ __('Duration') }}:</th>
                                        <td><strong>{{ $testAssignment->duration }}</strong> {{ __('minutes') }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="icofont icofont-calendar text-info"></i> {{ __('Start Time') }}:</th>
                                        <td>{{ $testAssignment->start_time->format('d.m.Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="icofont icofont-calendar text-danger"></i> {{ __('End Time') }}:</th>
                                        <td>{{ $testAssignment->end_time->format('d.m.Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($testAssignment->description)
                        <div class="alert alert-info mt-2 text-dark" role="alert">
                            <h6 class="alert-heading">
                                <i class="icofont icofont-info-circle"></i>
                                {{ __('Description') }}
                            </h6>
                            <p class="mb-0">{{ $testAssignment->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-3">
            <!-- Total Students -->
            <div class="col-md-3">
                <div class="card border-left-primary shadow-sm h-100">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                    {{ __('Total Students') }}
                                </div>
                                <div class="fs-4 font-weight-bold text-gray-800">
                                    {{ $statistics['total_students'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="icofont icofont-users text-primary" style="font-size: 36px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="col-md-3">
                <div class="card border-left-success shadow-sm h-100">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                    {{ __('Completed') }}
                                </div>
                                <div class="fs-4 font-weight-bold text-gray-800">
                                    {{ $statistics['completed'] }}
                                </div>
                                <small class="text-muted">
                                    {{ $statistics['total_students'] > 0 ? round(($statistics['completed'] / $statistics['total_students']) * 100) : 0 }}%
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="icofont icofont-check-circled text-success" style="font-size: 36px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="col-md-3">
                <div class="card border-left-warning shadow-sm h-100">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">
                                    {{ __('In Progress') }}
                                </div>
                                <div class="fs-4 font-weight-bold text-gray-800">
                                    {{ $statistics['in_progress'] }}
                                </div>
                                <small class="text-muted">
                                    {{ $statistics['total_students'] > 0 ? round(($statistics['in_progress'] / $statistics['total_students']) * 100) : 0 }}%
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="icofont icofont-spinner text-warning" style="font-size: 36px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Not Started -->
            <div class="col-md-3">
                <div class="card border-left-info shadow-sm h-100">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-2">
                                    {{ __('Not Started') }}
                                </div>
                                <div class="fs-4 font-weight-bold text-gray-800">
                                    {{ $statistics['not_started'] }}
                                </div>
                                <small class="text-muted">
                                    {{ $statistics['total_students'] > 0 ? round(($statistics['not_started'] / $statistics['total_students']) * 100) : 0 }}%
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="icofont icofont-clock-time text-info" style="font-size: 36px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="icofont icofont-users"></i>
                                {{ __('Student Results') }}
                            </h5>
                            <span class="primary">
                                {{ __('Students Count') }}:{{ $testAssignment->testResults->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">{{ __('Student Name') }}</th>
                                        <th width="15%" class="text-center">{{ __('Status') }}</th>
                                        <th width="15%" class="text-center">{{ __('Started At') }}</th>
                                        <th width="15%" class="text-center">{{ __('Completed At') }}</th>
                                        <th width="10%" class="text-center">{{ __('Score') }}</th>
                                        <th width="10%" class="text-center">{{ __('Correct') }}</th>
                                        <th width="5%" class="text-center">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($testAssignment->testResults as $index => $result)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2">
                                                    {{ substr($result->student->first_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $result->student->full_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $result->student->user->login }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            @if($result->status == 'completed')
                                            <span class="badge bg-success">
                                                <i class="icofont icofont-check"></i>
                                                {{ __('Completed') }}
                                            </span>
                                            @elseif($result->status == 'in_progress')
                                            <span class="badge bg-warning">
                                                <i class="icofont icofont-spinner"></i>
                                                {{ __('In Progress') }}
                                            </span>
                                            @else
                                            <span class="badge bg-secondary">
                                                <i class="icofont icofont-minus"></i>
                                                {{ __('Not Started') }}
                                            </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($result->started_at)
                                            <small>{{ $result->started_at->format('d.m.Y H:i') }}</small>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($result->completed_at)
                                            <small>{{ $result->completed_at->format('d.m.Y H:i') }}</small>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($result->status == 'completed')
                                            <span class="badge bg-{{ $result->score >= 60 ? 'success' : 'danger' }} fs-10">
                                                {{ $result->score }}%
                                            </span>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($result->status == 'completed')
                                            <strong>{{ $result->correct_answers }}</strong> / {{ $result->total_questions }}
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($result->status == 'completed')
                                            <a href="{{ route('admin.test-assignments.student-detail', ['testAssignment' => $testAssignment, 'testResult' => $result]) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="{{ __('View Details') }}">
                                                <i class="icofont icofont-eye"></i>
                                            </a>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="icofont icofont-users icofont-3x text-muted mb-2"></i>
                                            <p class="text-muted">{{ __('No students found in this group') }}</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ __('Actions') }}</h6>
                            </div>
                            <div>
                                <form action="{{ route('admin.test-assignments.toggle-status', $testAssignment) }}"
                                    method="POST"
                                    style="display: inline;">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-{{ $testAssignment->is_active ? 'warning' : 'success' }}">
                                        <i class="icofont icofont-{{ $testAssignment->is_active ? 'close' : 'check' }}"></i>
                                        {{ $testAssignment->is_active ? __('Deactivate') : __('Activate') }}
                                    </button>
                                </form>

                                @if($statistics['completed'] == 0)
                                <form action="{{ route('admin.test-assignments.destroy', $testAssignment) }}"
                                    method="POST"
                                    style="display: inline;"
                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this assignment?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="icofont icofont-trash"></i>
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

@push('styles')
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

    .border-left-info {
        border-left: 4px solid #36b9cc;
    }

    .icofont-3x {
        font-size: 3rem;
    }

    .icofont-2x {
        font-size: 2rem;
    }

    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>
@endpush

@endsection