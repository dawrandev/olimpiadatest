@extends('layouts.admin.main')
@section('title', __('Test Results'))
@section('content')
<x-admin.breadcrumb :title="__('Test Results')">
    <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-outline-primary btn-sm me-2">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Assignments') }}
    </a>
    <a href="{{ route('admin.test-assignments.show', $testAssignment) }}" class="btn btn-outline-info btn-sm">
        <i class="icofont icofont-eye"></i>
        {{ __('View Details') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <!-- Test Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <span class="badge bg-primary me-2">{{ $testAssignment->group->name }}</span>
                                {{ optional($testAssignment->subject->translations->firstWhere('language_id', currentLanguageId()))->name }}
                            </h4>
                            <div class="text-muted">
                                <i class="icofont icofont-calendar"></i>
                                {{ $testAssignment->start_time->format('d.m.Y H:i') }} - {{ $testAssignment->end_time->format('d.m.Y H:i') }}
                                <span class="mx-2">|</span>
                                <i class="icofont icofont-clock-time"></i>
                                {{ $testAssignment->duration }} {{ __('minutes') }}
                                <span class="mx-2">|</span>
                                <i class="icofont icofont-question-circle"></i>
                                {{ $testAssignment->question_count }} {{ __('questions') }}
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-success" onclick="window.location.href='{{ route('admin.test-assignments.export-excel', $testAssignment) }}'">
                                <i class="icofont icofont-file-excel"></i>
                                {{ __('Export to Excel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="icofont icofont-search"></i>
                </span>
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('Search by student name...') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select id="statusFilter" class="form-select">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="completed">{{ __('Completed') }}</option>
                <option value="in_progress">{{ __('In Progress') }}</option>
                <option value="not_started">{{ __('Not Started') }}</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="scoreFilter" class="form-select">
                <option value="">{{ __('All Scores') }}</option>
                <option value="90-100">90-100% (5)</option>
                <option value="80-89">70-8z9% (4)</option>
                <option value="60-69">60-69% (3)</option>
                <option value="0-59">0-59%</option>
            </select>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-dark">{{ __('Student Results') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="resultsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>{{ __('Student Name') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Correct Answers') }}</th>
                            <th class="text-center">{{ __('Started At') }}</th>
                            <th class="text-center">{{ __('Completed At') }}</th>
                            <th class="text-center">{{ __('Duration') }}</th>
                            <th class="text-center">{{ __('Score') }}</th>
                            <th width="100" class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $index => $result)
                        <tr data-status="{{ $result->status }}" data-score="{{ $result->score ?? 0 }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong>{{ $result->student->full_name }}</strong><br>
                                        <small class="text-muted">{{ $result->student->login }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($result->status == 'completed')
                                <span class="badge bg-success">
                                    <i class="icofont icofont-check"></i> {{ __('Completed') }}
                                </span>
                                @elseif($result->status == 'in_progress')
                                <span class="badge bg-warning">
                                    <i class="icofont icofont-spinner"></i> {{ __('In Progress') }}
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="icofont icofont-minus"></i> {{ __('Not Started') }}
                                </span>
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
                                @if($result->started_at && $result->completed_at)
                                @php
                                $duration = ceil($result->started_at->diffInSeconds($result->completed_at) / 60);
                                @endphp
                                <span class="badge bg-light text-dark">
                                    {{ $duration }} {{ __('min') }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($result->status == 'completed')
                                <strong class="fs-5">{{ $result->score }}%</strong>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($result->status == 'completed')
                                <a href="{{ route('admin.test-assignments.student-detail', ['testAssignment' => $testAssignment, 'testResult' => $result]) }}"
                                    class="btn btn-sm btn-outline-info"
                                    title="{{ __('View Detailed Answers') }}">
                                    <i class="icofont icofont-eye"></i>
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="icofont icofont-users icofont-3x text-muted mb-2"></i>
                                <p class="text-muted">{{ __('No students found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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

    .border-left-info {
        border-left: 4px solid #36b9cc;
    }

    .border-left-warning {
        border-left: 4px solid #f6c23e;
    }

    .icofont-3x {
        font-size: 3rem;
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

    @media print {

        .btn,
        .card-header {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const scoreFilter = document.getElementById('scoreFilter');
        const tableRows = document.querySelectorAll('#resultsTable tbody tr[data-status]');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const scoreValue = scoreFilter.value;

            tableRows.forEach(row => {
                const studentName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                const score = parseFloat(row.getAttribute('data-score'));
                let showRow = true;

                if (searchTerm && !studentName.includes(searchTerm)) showRow = false;
                if (statusValue && status !== statusValue) showRow = false;
                if (scoreValue) {
                    const [min, max] = scoreValue.split('-').map(Number);
                    if (score < min || score > max) showRow = false;
                }
                row.style.display = showRow ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);
        scoreFilter.addEventListener('change', filterTable);
    });
</script>
@endpush
@endsection