@extends('layouts.admin.main')
@section('title', __('Student Test Details'))
@vite(['resources/css/admin/test-assignments/student-detail.css'])

<!-- MathJax Configuration BEFORE loading library -->
<script>
    window.MathJax = {
        tex: {
            inlineMath: [
                ['\\(', '\\)']
            ],
            displayMath: [
                ['\\[', '\\]']
            ],
            processEscapes: true,
            processEnvironments: true
        },
        options: {
            skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre'],
            ignoreHtmlClass: 'tex2jax_ignore',
            processHtmlClass: 'tex2jax_process'
        },
        svg: {
            fontCache: 'global'
        },
        startup: {
            pageReady: () => {
                return MathJax.startup.defaultPageReady().then(() => {});
            }
        }
    };
</script>

@section('content')

<x-admin.breadcrumb :title="__('Student Test Details')">
    <a href="{{ route('admin.test-assignments.show', $testAssignment) }}" class="btn btn-outline-primary btn-sm">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Assignment') }}
    </a>
    <a href="{{ route('admin.test-assignments.student-pdf', [$testAssignment->id, $testResult->id]) }}"
        class="btn btn-outline-danger btn-sm ms-2"
        target="_blank">
        <i class="icofont icofont-file-pdf"></i>
        {{ __('Download PDF') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <!-- Retake Alert -->
    @if($testAssignment->is_retake && $testAssignment->parentAssignment)
    <div class="alert alert-warning mb-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="icofont icofont-refresh icofont-2x me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">
                    {{ __('Retake Assignment') }}
                </h6>
                <p class="mb-0 small">
                    {{ __('This is a retake test.') }}
                    <a href="{{ route('admin.test-assignments.show', $testAssignment->parent_assignment_id) }}"
                        class="alert-link"
                        style="color: #000000ff; font-weight: 600; text-decoration: underline;">
                        {{ __('View Original Test') }} #{{ $testAssignment->parent_assignment_id }}
                    </a>
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Student Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-{{ $testResult->score >= 60 ? 'success' : 'danger' }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="icofont icofont-user"></i>
                            {{ $testResult->student->full_name }}
                        </h5>
                        @if($testAssignment->is_retake)
                        <span class="badge bg-warning text-dark">
                            <i class="icofont icofont-refresh"></i> {{ __('RETAKE') }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="icofont icofont-user"></i>
                                {{ __('Student Information') }}
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('Full Name') }}:</th>
                                    <td><strong>{{ $testResult->student->full_name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('Login') }}:</th>
                                    <td>{{ $testResult->student->user->login }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Group') }}:</th>
                                    <td>{{ $testAssignment->group->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Test Type') }}:</th>
                                    <td>
                                        @if($testAssignment->is_retake)
                                        <span class="badge bg-warning text-dark">
                                            <i class="icofont icofont-refresh"></i> {{ __('Retake') }}
                                        </span>
                                        @else
                                        <span class="primary">
                                            <i class="icofont icofont-file-document"></i> {{ __('Original') }}
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="icofont icofont-chart-bar-graph"></i>
                                {{ __('Test Results') }}
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('Score') }}:</th>
                                    <td>
                                        <h6 class="mb-0" id="score-display">
                                            <span class="badge bg-{{ $testResult->score >= 60 ? 'success' : 'danger' }} px-2 py-1" style="font-size: 12px;">
                                                {{ $testResult->score }}%
                                            </span>
                                            @if($testResult->score >= 60)
                                            <span class="text-success ms-2" style="font-size: 13px;">
                                                <i class="icofont icofont-check-circled"></i> {{ __('Passed') }}
                                            </span>
                                            @else
                                            <span class="text-danger ms-2" style="font-size: 13px;">
                                                <i class="icofont icofont-close-circled"></i> {{ __('Failed') }}
                                            </span>
                                            @endif
                                        </h6>
                                    </td>

                                </tr>
                                <tr>
                                    <th>{{ __('Grade') }}:</th>
                                    <td id="grade-display">
                                        @if($testResult->grade)
                                        <span class="badge bg-{{ $testResult->grade_color }}" style="font-size: 1.2rem; padding: 0.5rem 1rem; color:black">
                                            {{ $testResult->grade }}
                                        </span>
                                        <small class="text-muted ms-2">
                                            @if($testResult->grade == 5)
                                            ({{ __('Excellent') }})
                                            @elseif($testResult->grade == 4)
                                            ({{ __('Good') }})
                                            @elseif($testResult->grade == 3)
                                            ({{ __('Satisfactory') }})
                                            @endif
                                        </small>
                                        @else
                                        <span class="badge bg-secondary">{{ __('N/A') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Correct Answers') }}:</th>
                                    <td>
                                        <strong>{{ $testResult->correct_answers }} / {{ $studentAnswers->count() }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Status') }}:</th>
                                    <td>
                                        <span class="badge bg-{{ $testResult->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ __($testResult->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions and Answers -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="icofont icofont-question-circle"></i>
                        {{ __('Questions and Answers') }}
                        <span class="badge bg-light text-dark ms-2">{{ $studentAnswers->count() }} {{ __('Questions') }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($studentAnswers as $index => $studentAnswer)
                    @php
                    $question = $studentAnswer->question;
                    $isCorrect = $studentAnswer->is_correct;
                    $partialScore = $studentAnswer->partial_score ?? 0;

                    // Determine if multiple choice based on correct answers count
                    $isMultiple = false;
                    if ($question->type === 'single_choice') {
                    $correctCount = $question->answers->where('is_correct', true)->count();
                    $isMultiple = $correctCount > 1;
                    }
                    @endphp

                    <div class="question-card mb-3 border rounded" style="border-color: #e5e7eb;">
                        <!-- Question Header - Ultra Minimal -->
                        <div class="question-header px-3 py-2 bg-white border-bottom" style="border-color: #f3f4f6;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-size: 0.75rem; color: #6b7280; font-weight: 600;">№{{ $index + 1 }}</span>
                                    <span style="font-size: 0.7rem; color: #9ca3af;">|</span>

                                    @if($question->type === 'single_choice')
                                    @if($isMultiple)
                                    <span style="font-size: 0.7rem; color: #6b7280;">{{__('Multiple Choice')}}</span>
                                    @else
                                    <span style="font-size: 0.7rem; color: #6b7280;">{{__('Single Choice')}}</span>
                                    @endif
                                    @elseif($question->type === 'matching')
                                    <span style="font-size: 0.7rem; color: #6b7280;">{{__('Matching')}}</span>
                                    @elseif($question->type === 'sequence')
                                    <span style="font-size: 0.7rem; color: #6b7280;">{{__('Sequence')}}</span>
                                    @endif
                                </div>

                                <div>
                                    @if($isCorrect)
                                    <span style="font-size: 0.7rem; color: #10b981; font-weight: 500;">
                                        ✓ {{__('Correct')}}
                                        @if($partialScore > 0 && $partialScore < 100)
                                            ({{ $partialScore }}%)
                                            @endif
                                            </span>
                                            @else
                                            <span style="font-size: 0.7rem; color: #ef4444; font-weight: 500;">
                                                ✗ {{__('Incorrect')}}
                                                @if($partialScore > 0)
                                                ({{ $partialScore }}%)
                                                @endif
                                            </span>
                                            @endif
                                </div>
                            </div>
                        </div>

                        <div class="px-3 py-2">
                            <!-- Question Text -->
                            <div class="question-text mb-2">
                                <div style="font-size: 0.875rem; line-height: 1.5; color: #1f2937;">
                                    @if (
                                    strpos($question->text, '<math') !==false ||
                                        strpos($question->text, '<sub') !==false ||
                                            strpos($question->text, '<sup') !==false
                                                )
                                                {!! $question->text !!}
                                                @else
                                                {{ $question->text }}
                                                @endif
                                </div>


                                <!-- Question Image -->
                                @if($question->image)
                                <div class="text-center mb-2">
                                    <img src="{{ asset('storage/questions/' . $question->image) }}"
                                        class="img-fluid rounded"
                                        style="max-height: 250px; border: 1px solid #e5e7eb;"
                                        alt="Question image">
                                </div>
                                @endif

                                <!-- SINGLE CHOICE OR MULTIPLE CHOICE TYPE -->
                                @if($question->type === 'single_choice')
                                <div class="mt-2">
                                    @php
                                    $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                                    $selectedIds = [];
                                    if ($isMultiple && $studentAnswer->answer_text) {
                                    $selectedIds = array_map('intval', explode(',', $studentAnswer->answer_text));
                                    }
                                    @endphp

                                    @foreach($question->answers as $answerIndex => $answer)
                                    @php
                                    $isSelected = $isMultiple
                                    ? in_array($answer->id, $selectedIds)
                                    : ($studentAnswer->answer_id == $answer->id);
                                    $isAnswerCorrect = $answer->is_correct;

                                    // Minimal styling
                                    $bgColor = '#ffffff';
                                    $borderColor = '#e5e7eb';
                                    $textColor = '#374151';
                                    $borderLeft = '';

                                    if ($isSelected && $isAnswerCorrect) {
                                    $bgColor = '#f0fdf4';
                                    $borderColor = '#d1fae5';
                                    $borderLeft = 'border-start border-success';
                                    } elseif ($isSelected && !$isAnswerCorrect) {
                                    $bgColor = '#fef2f2';
                                    $borderColor = '#fecaca';
                                    $borderLeft = 'border-start border-danger';
                                    } elseif (!$isSelected && $isAnswerCorrect) {
                                    $borderColor = '#d1fae5';
                                    }
                                    @endphp

                                    <div class="d-flex align-items-start gap-1">
                                        @if($isAnswerCorrect)
                                        <span style="color: #10b981; font-size: 0.85rem; font-weight: 600; min-width: 15px;">*</span>
                                        @else
                                        <span style="min-width: 15px;"></span>
                                        @endif

                                        <div class="answer-option px-2 py-1 mb-1 border rounded {{ $borderLeft }} flex-grow-1"
                                            style="background-color: {{ $bgColor }}; border-color: {{ $borderColor }}; font-size: 0.8rem;">
                                            <div class="d-flex align-items-start gap-2">
                                                <span style="color: #9ca3af; font-size: 0.75rem; min-width: 20px; font-weight: 500;">{{ $letters[$answerIndex] }}.</span>
                                                <span class="flex-grow-1" style="line-height: 1.4; color: {{ $textColor }};">
                                                    @if (
                                                    strpos($answer->text, '<math') !==false ||
                                                        strpos($answer->text, '<sub') !==false ||
                                                            strpos($answer->text, '<sup') !==false
                                                                )
                                                                {!! $answer->text !!}
                                                                @else
                                                                {{ $answer->text }}
                                                                @endif
                                                </span>

                                                <div class="d-flex align-items-center" style="min-width: 40px; justify-content: flex-end;">
                                                    @if($isSelected)
                                                    <span style="color: #9ca3af; font-size: 0.75rem;">→</span>
                                                    @endif
                                                    @if($isAnswerCorrect)
                                                    <span style="color: #10b981; font-size: 0.75rem; margin-left: 4px;">✓</span>
                                                    @endif
                                                    @if($isSelected && !$isAnswerCorrect)
                                                    <span style="color: #ef4444; font-size: 0.75rem; margin-left: 4px;">✗</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                @if($question->type === 'matching')
                                <div class="mt-2">
                                    @if($question->matchingPairs && $question->matchingPairs->count() > 0)
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.25rem; font-weight: 600;">
                                                    @if($question->left_items_title)
                                                    {{ $question->left_items_title }}
                                                    @else
                                                    {{__('Left Items')}}
                                                    @endif
                                                </div>
                                                @foreach($question->matchingPairs->where('side', 'left')->sortBy('order') as $leftItem)
                                                <div class="px-2 py-1 mb-1 border rounded" style="background-color: #f9fafb; border-color: #e5e7eb; font-size: 0.75rem;">
                                                    <span style="color: #9ca3af; font-weight: 600;">{{ $leftItem->key }}.</span>
                                                    <span style="color: #374151;">
                                                        @if (
                                                        strpos($leftItem->text, '<math') !==false ||
                                                            strpos($leftItem->text, '<sub') !==false ||
                                                                strpos($leftItem->text, '<sup') !==false
                                                                    )
                                                                    {!! $leftItem->text !!}
                                                                    @else
                                                                    {{ $leftItem->text }}
                                                                    @endif
                                                    </span>

                                                </div>
                                                @endforeach
                                            </div>

                                            <!-- Right Items -->
                                            <div class="col-md-6">
                                                <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.25rem; font-weight: 600;">
                                                    @if($question->right_items_title)
                                                    {{ $question->right_items_title }}
                                                    @else
                                                    {{__('Right Items')}}
                                                    @endif
                                                </div>
                                                @foreach($question->matchingPairs->where('side', 'right')->sortBy('order') as $rightItem)
                                                <div class="px-2 py-1 mb-1 border rounded" style="background-color: #f9fafb; border-color: #e5e7eb; font-size: 0.75rem;">
                                                    <span style="color: #9ca3af; font-weight: 600;">{{ $rightItem->key }}.</span>
                                                    <span style="color: #374151;">
                                                        @if (
                                                        strpos($rightItem->text, '<math') !==false ||
                                                            strpos($rightItem->text, '<sub') !==false ||
                                                                strpos($rightItem->text, '<sup') !==false
                                                                    )
                                                                    {!! $rightItem->text !!}
                                                                    @else
                                                                    {{ $rightItem->text }}
                                                                    @endif
                                                    </span>

                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Student javobi -->
                                    @if(isset($studentAnswer->parsed_matching) && count($studentAnswer->parsed_matching) > 0)
                                    <div class="mb-2">
                                        <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.5rem;">{{__('Student Answer')}}:</div>
                                        <div>
                                            @foreach($studentAnswer->parsed_matching as $left => $right)
                                            @php
                                            $isMatchCorrect = isset($studentAnswer->correct_matching[$left]) && $studentAnswer->correct_matching[$left] == $right;
                                            $bgColor = $isMatchCorrect ? '#f0fdf4' : '#fef2f2';
                                            $borderColor = $isMatchCorrect ? '#d1fae5' : '#fecaca';
                                            @endphp
                                            <div class="px-2 py-1 mb-1 border rounded" style="background-color: {{ $bgColor }}; border-color: {{ $borderColor }}; font-size: 0.75rem;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span style="color: #6b7280;">{{ $left }}</span>
                                                    <span style="color: #9ca3af;">→</span>
                                                    <span style="color: #6b7280;">{{ $right }}</span>
                                                    <span class="ms-auto" style="color: {{ $isMatchCorrect ? '#10b981' : '#ef4444' }};">
                                                        {{ $isMatchCorrect ? '✓' : '✗' }}
                                                    </span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- To'g'ri javob -->
                                    @if(isset($studentAnswer->correct_matching))
                                    <div class="px-2 py-1 rounded" style="background-color: #f0fdf4; border: 1px solid #d1fae5; font-size: 0.75rem;">
                                        <div style="color: #10b981; margin-bottom: 0.25rem; font-size: 0.7rem;">{{__('Correct Answer')}}:</div>
                                        <div style="color: #374151;">
                                            @foreach($studentAnswer->correct_matching as $left => $right)
                                            <span style="margin-right: 0.5rem;">{{ $left }}-{{ $right }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <!-- SEQUENCE TYPE -->
                                @if($question->type === 'sequence')
                                <div class="mt-2">
                                    <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.5rem;">{{__('Items to Order')}}:</div>
                                    <div class="row g-1 mb-2">
                                        @foreach($question->answers as $seqItem)
                                        <div class="col-md-6">
                                            <div class="px-2 py-1 border rounded" style="background-color: #f9fafb; border-color: #e5e7eb; font-size: 0.75rem;">
                                                <span style="color: #9ca3af;">{{ $loop->iteration }}.</span>
                                                <span style="color: #374151;">
                                                    @if (
                                                    strpos($seqItem->text, '<math') !==false ||
                                                        strpos($seqItem->text, '<sub') !==false ||
                                                            strpos($seqItem->text, '<sup') !==false
                                                                )
                                                                {!! $seqItem->text !!}
                                                                @else
                                                                {{ $seqItem->text }}
                                                                @endif
                                                </span>

                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    @if(isset($studentAnswer->parsed_sequence_orders))
                                    <div class="mb-2">
                                        <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.5rem;">{{__('Student Sequence')}}:</div>
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            @foreach($studentAnswer->parsed_sequence_orders as $displayNumber)
                                            <span style="background-color: {{ $isCorrect ? '#10b981' : '#ef4444' }}; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                                {{ $displayNumber }}
                                            </span>
                                            @if(!$loop->last)
                                            <span style="color: {{ $isCorrect ? '#10b981' : '#ef4444' }}; font-size: 0.7rem;">→</span>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    @if(isset($studentAnswer->correct_sequence))
                                    <div class="px-2 py-1 rounded" style="background-color: #f0fdf4; border: 1px solid #d1fae5; font-size: 0.75rem;">
                                        <div style="color: #10b981; margin-bottom: 0.25rem; font-size: 0.7rem;">{{__('Correct Sequence')}}:</div>
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            @foreach($studentAnswer->correct_sequence as $order)
                                            <span style="background-color: #10b981; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                                {{ $order }}
                                            </span>
                                            @if(!$loop->last)
                                            <span style="color: #10b981; font-size: 0.7rem;">→</span>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">
                            <i class="icofont icofont-clock-time"></i>
                            {{ __('Time Information') }}
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>{{ __('Started At') }}:</strong>
                                <p class="mb-0">{{ $testResult->started_at->format('d.m.Y H:i') }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>{{ __('Completed At') }}:</strong>
                                <p class="mb-0">{{ $testResult->completed_at ? $testResult->completed_at->format('d.m.Y H:i') : '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>{{ __('Duration') }}:</strong>
                                <p class="mb-0">
                                    @if($testResult->completed_at)
                                    {{ round($testResult->started_at->diffInSeconds($testResult->completed_at) / 60, 1) }} {{ __('minutes') }}
                                    @else
                                    -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Admin Modal - Ochilishi uchun: Ctrl + Shift + E -->
    <div class="modal fade" id="adminEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title mb-0">{{__('Edit Score')}}</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.test-assignments.update-score', $testResult->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <label class="form-label small mb-1">{{ __('Score (%)') }}</label>
                            <input type="number"
                                name="score"
                                class="form-control form-control-sm"
                                value="{{ $testResult->score }}"
                                min="0"
                                max="100"
                                required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small mb-1">{{ __('Grade') }}</label>
                            <select name="grade" class="form-select form-select-sm">
                                <option value="">{{ __('N/A') }}</option>
                                <option value="5" {{ $testResult->grade == 5 ? 'selected' : '' }}>
                                    {{ __('5') }}
                                </option>
                                <option value="4" {{ $testResult->grade == 4 ? 'selected' : '' }}>
                                    {{ __('4') }}
                                </option>
                                <option value="3" {{ $testResult->grade == 3 ? 'selected' : '' }}>
                                    {{ __('3') }}
                                </option>
                                <option value="2" {{ $testResult->grade == 2 ? 'selected' : '' }}>
                                    {{ __('2') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                        <button type="submit" class="btn btn-sm btn-primary">{{__('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.altKey && e.key === 'M') {
                e.preventDefault();
                const modal = new bootstrap.Modal(document.getElementById('adminEditModal'));
                modal.show();
            }
        });
    </script>

    <!-- MATHJAX CONFIGURATION -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [
                    ['\\(', '\\)']
                ],
                displayMath: [
                    ['\\[', '\\]']
                ],
                processEscapes: true
            },
            svg: {
                fontCache: 'global'
            }
        };
    </script>

    <!-- MathJax Rendering -->
    <script>
        window.renderMathContent = function(element = null) {
            if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                try {
                    if (element) {
                        MathJax.typesetPromise([element])

                    } else {
                        MathJax.typesetPromise()

                    }
                } catch (err) {
                    console.warn('MathJax error:', err);
                }
            } else {
                setTimeout(() => window.renderMathContent(element), 500);
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => window.renderMathContent(), 1000);
        });

        if (typeof MathJax !== 'undefined' && MathJax.startup && MathJax.startup.promise) {
            MathJax.startup.promise.then(() => {
                window.renderMathContent();
            }).catch(err => {
                console.warn('MathJax startup error:', err);
            });
        }
    </script>
    @endpush
    @endsection