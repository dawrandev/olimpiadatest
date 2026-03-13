@extends('layouts.student.main')
@vite(['resources/css/student/test.css'])
@vite(['resources/css/student/results.css'])
@section('content')
<section class="section-space cuba-demo-section layout pt-5" id="layout">
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12 wow pulse">
                <div class="cuba-demo-content">
                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('student.home') }}" class="btn btn-light me-3">
                                                <i data-feather="arrow-left"></i>
                                            </a>
                                            <div>
                                                <h4 class="mb-1">{{ __('My Test Results') }}</h4>
                                                <p class="text-muted mb-0">{{ __('View all your completed tests') }}</p>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="stats-mini">
                                                <span class="badge bg-primary me-2">{{ $testSessions->count() }} {{ __('Tests') }}</span>
                                                @if($testSessions->count() > 0)
                                                <span class="badge bg-success">{{ __('Avg') }}: {{ number_format($averageScore, 1) }}%</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    @if($testSessions->count() > 0)
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body py-4">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="file-text" class="text-primary"></i>
                                    </div>
                                    <h4 class="mb-1">{{ $testSessions->count() }}</h4>
                                    <small class="text-muted">{{ __('Total Tests') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body py-4">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="trending-up" class="text-success"></i>
                                    </div>
                                    <h4 class="mb-1">{{ number_format($averageScore, 1) }}%</h4>
                                    <small class="text-muted">{{ __('Average Score') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body py-4">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="award" class="text-warning"></i>
                                    </div>
                                    <h4 class="mb-1">{{ number_format($bestScore, 1) }}%</h4>
                                    <small class="text-muted">{{ __('Best Score') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body py-4">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="globe" class="text-info"></i>
                                    </div>
                                    <h4 class="mb-1">{{ $languageCount }}</h4>
                                    <small class="text-muted">{{ __('Languages Tested') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Filters and Search -->
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body py-3">
                                    <form method="GET" action="{{ route('student.results') }}" class="row align-items-center">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small">{{ __('Language') }}</label>
                                            <select name="language_id" class="form-select form-select-sm">
                                                <option value="">{{ __('All Languages') }}</option>
                                                @foreach($languages as $language)
                                                <option value="{{ $language->id }}" {{ request('language_id') == $language->id ? 'selected' : '' }}>
                                                    {{ $language->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small">{{ __('Date From') }}</label>
                                            <input type="date" name="date_from" class="form-control form-control-sm"
                                                value="{{ request('date_from') }}">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small">{{ __('Date To') }}</label>
                                            <input type="date" name="date_to" class="form-control form-control-sm"
                                                value="{{ request('date_to') }}">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <label class="form-label small">&nbsp;</label>
                                            <div class="d-flex gap-1">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i data-feather="filter"></i>
                                                </button>
                                                <a href="{{ route('student.results') }}" class="btn btn-outline-secondary btn-sm">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results List -->
                    <div class="row">
                        <div class="col-lg-12">
                            @if($testSessions->count() > 0)
                            @foreach($testSessions as $session)
                            <div class="card mb-3 result-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <!-- Test Info -->
                                        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                                            <div class="d-flex align-items-center">
                                                <div class="result-score-mini me-3">
                                                    <div class="score-circle score-{{ $session->level['class'] }}">
                                                        {{ $session->percentage }}%
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i data-feather="globe" class="me-1" style="width: 16px; height: 16px;"></i>
                                                        {{ $session->language->name }}
                                                    </h6>
                                                    <div class="text-muted small">
                                                        <i data-feather="calendar" class="me-1" style="width: 14px; height: 14px;"></i>
                                                        {{ \Carbon\Carbon::parse($session->finished_at)->format('d.m.Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Statistics -->
                                        <div class="col-lg-5 col-md-6 mb-3 mb-lg-0">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="stat-mini">
                                                        <div class="stat-value text-primary">{{ $session->total_questions }}</div>
                                                        <div class="stat-label">{{ __('Questions') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-mini">
                                                        <div class="stat-value text-success">{{ $session->correct_answers }}</div>
                                                        <div class="stat-label">{{ __('Correct') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-mini">
                                                        <div class="stat-value text-info">{{ $session->duration_formatted }}</div>
                                                        <div class="stat-label">{{ __('Duration') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="col-lg-3 text-end">
                                            <div class="d-flex flex-column h-100">
                                                <!-- Badge tepada -->
                                                <div class="mb-2">
                                                    <span class="badge bg-{{ $session->level['class'] }}">
                                                        <i data-feather="{{ $session->level['icon'] }}" style="width: 16px; height: 12px;"></i>
                                                        {{ $session->level['name'] }}
                                                    </span>
                                                </div>

                                                <!-- Pastda button -->
                                                <div class="mt-auto">
                                                    <a href="{{ route('student.test.result', $session->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i data-feather="eye" class="me-1" style="width: 14px; height: 14px;"></i>
                                                        {{ __('View Details') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            <!-- Pagination -->
                            @if($testSessions->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $testSessions->links() }}
                            </div>
                            @endif
                            @else
                            <!-- Empty State -->
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <div class="empty-state">
                                        <i data-feather="file-text" class="text-muted mb-3" style="width: 64px; height: 64px;"></i>
                                        <h5 class="text-muted mb-2">{{ __('No test results found') }}</h5>
                                        <p class="text-muted mb-4">{{ __('You haven\'t completed any tests yet. Start your first test now!') }}</p>
                                        <a href="{{ route('student.home') }}" class="btn btn-primary">
                                            <i data-feather="plus" class="me-2"></i>
                                            {{ __('Start New Test') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    function printResult(sessionId) {
        window.open('{{ route("student.test.result", "") }}/' + sessionId + '?print=1', '_blank');
    }

    function shareResult(sessionId, languageName, percentage) {
        const text = `{{ __("I scored") }} ${percentage}% {{ __("on") }} ${languageName} {{ __("test") }}!`;
        const url = '{{ route("student.test.result", "") }}/' + sessionId;

        if (navigator.share) {
            navigator.share({
                title: '{{ __("Test Results") }}',
                text: text,
                url: url
            });
        } else {
            navigator.clipboard.writeText(url).then(function() {
                alert('{{ __("Link copied to clipboard!") }}');
            });
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

@endsection