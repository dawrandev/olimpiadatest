@extends('layouts.student.main')
@vite(['resources/css/student/test.css'])

@section('content')
<section class="section-space cuba-demo-section layout pt-5" id="layout">
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12 wow pulse">
                <div class="cuba-demo-content">
                    <!-- Result Header -->
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    <div class="result-icon mb-3">
                                        <i data-feather="{{ $level['icon'] }}" class="text-{{ $level['class'] }}" style="width: 48px; height: 48px;"></i>
                                    </div>
                                    <h3 class="text-{{ $level['class'] }} mb-2">{{ $level['name'] }}</h3>
                                    <h1 class="display-4 fw-bold mb-1">{{ $percentage }}%</h1>
                                    <p class="text-muted mb-0">{{ __('Test completed successfully') }}</p>
                                    <small class="text-muted">{{ $language->name }} - {{ $durationFormatted }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Overview -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="help-circle" class="text-primary" style="width: 32px; height: 32px;"></i>
                                    </div>
                                    <h4 class="mb-1">{{ $totalQuestions }}</h4>
                                    <small class="text-muted">{{ __('Total Questions') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="check-circle" class="text-success" style="width: 32px; height: 32px;"></i>
                                    </div>
                                    <h4 class="mb-1 text-success">{{ $correctAnswers }}</h4>
                                    <small class="text-muted">{{ __('Correct Answers') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="x-circle" class="text-danger" style="width: 32px; height: 32px;"></i>
                                    </div>
                                    <h4 class="mb-1 text-danger">{{ $incorrectAnswers }}</h4>
                                    <small class="text-muted">{{ __('Incorrect Answers') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon mb-2">
                                        <i data-feather="minus-circle" class="text-warning" style="width: 32px; height: 32px;"></i>
                                    </div>
                                    <h4 class="mb-1 text-warning">{{ $unansweredQuestions }}</h4>
                                    <small class="text-muted">{{ __('Unanswered') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Circle and Details -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Test Performance') }}</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="result-circle-container mb-3">
                                        <div class="result-circle">
                                            <svg class="result-progress" viewBox="0 0 120 120">
                                                <circle cx="60" cy="60" r="50" stroke="#e2e8f0" stroke-width="8" fill="none" />
                                                <circle cx="60" cy="60" r="50" stroke="currentColor" stroke-width="8" fill="none"
                                                    class="text-{{ $level['class'] }}"
                                                    stroke-dasharray="314.16"
                                                    stroke-dashoffset="{{ 314.16 - (314.16 * $percentage / 100) }}"
                                                    transform="rotate(-90 60 60)" />
                                            </svg>
                                            <div class="result-percentage">
                                                <span class="display-6 fw-bold text-{{ $level['class'] }}">{{ $percentage }}</span>
                                                <small class="text-muted d-block">%</small>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mb-0">{{ __('Overall Score') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Test Information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i data-feather="globe" class="me-2 text-primary"></i>
                                            <div>
                                                <small class="text-muted d-block">{{ __('Language') }}</small>
                                                <span class="fw-medium">{{ $language->name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i data-feather="clock" class="me-2 text-primary"></i>
                                            <div>
                                                <small class="text-muted d-block">{{ __('Duration') }}</small>
                                                <span class="fw-medium">{{ $durationFormatted }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i data-feather="calendar" class="me-2 text-primary"></i>
                                            <div>
                                                <small class="text-muted d-block">{{ __('Completed At') }}</small>
                                                <span class="fw-medium">{{ \Carbon\Carbon::parse($testSession->finished_at)->format('d.m.Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="d-flex align-items-center">
                                            <i data-feather="award" class="me-2 text-{{ $level['class'] }}"></i>
                                            <div>
                                                <small class="text-muted d-block">{{ __('Result Level') }}</small>
                                                <span class="fw-medium text-{{ $level['class'] }}">{{ $level['name'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Results -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ __('Detailed Results') }}</h6>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#detailedResults" aria-expanded="false" aria-controls="detailedResults">
                                        <i class="icofont icofont-eye me-1"></i>
                                        {{ __('View Details') }}
                                    </button>
                                </div>
                                <div class="collapse" id="detailedResults">
                                    <div class="card-body">
                                        @foreach($testSession->sessionAnswers as $index => $sessionAnswer)
                                        @php
                                        $question = $sessionAnswer->question;
                                        $questionTranslation = $question->translations->where('language_id', $language->id)->first();
                                        $selectedAnswer = $sessionAnswer->answer;
                                        $correctAnswer = $question->answers->where('is_correct', true)->first();
                                        $correctAnswerTranslation = $correctAnswer ? $correctAnswer->translations->where('language_id', $language->id)->first() : null;
                                        $selectedAnswerTranslation = $selectedAnswer ? $selectedAnswer->translations->where('language_id', $language->id)->first() : null;
                                        @endphp

                                        <div class="question-result mb-3 p-3 border rounded">
                                            <div class="d-flex align-items-start">
                                                <div class="question-number me-3">
                                                    <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-1">{{ $questionTranslation->text ?? __('Question not found') }}</h6>
                                                        @if($sessionAnswer->is_correct === true)
                                                        <span class="badge bg-success">
                                                            <i data-feather="check" style="width: 12px; height: 12px;"></i>
                                                            {{ __('Correct') }}
                                                        </span>
                                                        @elseif($sessionAnswer->is_correct === false)
                                                        <span class="badge bg-danger">
                                                            <i data-feather="x" style="width: 12px; height: 12px;"></i>
                                                            {{ __('Incorrect') }}
                                                        </span>
                                                        @else
                                                        <span class="badge bg-warning">
                                                            <i data-feather="minus" style="width: 12px; height: 12px;"></i>
                                                            {{ __('Not answered') }}
                                                        </span>
                                                        @endif
                                                    </div>

                                                    @if($selectedAnswer)
                                                    <div class="answer-info mb-2">
                                                        <small class="text-muted">{{ __('Your answer') }}:</small>
                                                        <div class="selected-answer p-2 rounded {{ $sessionAnswer->is_correct ? 'bg-light-success' : 'bg-light-danger' }}">
                                                            {{ $selectedAnswerTranslation->text ?? __('Answer not found') }}
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($sessionAnswer->is_correct === false && $correctAnswerTranslation)
                                                    <div class="correct-answer-info">
                                                        <small class="text-muted">{{ __('Correct answer') }}:</small>
                                                        <div class="correct-answer p-2 rounded bg-light-success">
                                                            {{ $correctAnswerTranslation->text }}
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                                        <a href="{{ route('student.home') }}" class="btn btn-primary">
                                            <i class="icofont icofont-home me-1"></i>
                                            {{ __('Back to Home') }}
                                        </a>
                                    </div>
                                    @if($percentage < 75)
                                        <div class="mt-3">
                                        <div class="alert alert-info">
                                            <i data-feather="info" class="me-2"></i>
                                            {{ __('Consider retaking the test to improve your score!') }}
                                        </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<!-- Custom CSS for result page -->
<style>
    .result-circle-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .result-circle {
        position: relative;
        width: 160px;
        height: 160px;
    }

    .result-progress {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .result-percentage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .stat-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px;
        height: 50px;
        margin: 0 auto;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.1);
    }

    .info-item {
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .question-result {
        transition: all 0.3s ease;
    }

    .question-result:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .bg-light-success {
        background-color: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.2);
    }

    .bg-light-danger {
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    @media print {

        .btn,
        .card-header button {
            display: none !important;
        }

        .collapse:not(.show) {
            display: block !important;
        }
    }

    @media (max-width: 768px) {
        .result-circle {
            width: 120px;
            height: 120px;
        }

        .display-4 {
            font-size: 2rem;
        }

        .d-flex.gap-3 {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<script>
    function shareResults() {
        if (navigator.share) {
            navigator.share({
                title: '{{ __("Test Results") }}',
                text: '{{ __("I scored") }} {{ $percentage }}% {{ __("on") }} {{ $language->name }} {{ __("test") }}!',
                url: window.location.href
            });
        } else {
            // Fallback for browsers that don't support Web Share API
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('{{ __("Link copied to clipboard!") }}');
            });
        }
    }

    // Initialize feather icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

@endsection