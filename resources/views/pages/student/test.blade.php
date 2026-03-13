@extends('layouts.student.main')
@vite(['resources/css/student/test.css'])
@section('content')

<section class="section-space cuba-demo-section layout pt-5" id="layout">
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12 wow pulse">
                <div class="cuba-demo-content">
                    <div class="couting">
                        <!-- Test Header with Timer -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h5 class="mb-0">{{ $testAssignment->name }}</h5>
                                                        <small class="fw-bold text-primary d-block fs-5">
                                                            {{ Auth::user()->student->full_name ?? Auth::user()->full_name ?? 'Student' }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <small class="text-muted d-block">{{ __('Question') }}</small>
                                                    <span class="h6 mb-0">
                                                        <span id="currentQuestion">1</span> / {{ count($questions) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="timer-container">
                                                    <div class="timer-circle">
                                                        <svg class="timer-progress" viewBox="0 0 80 80">
                                                            <circle cx="40" cy="40" r="35" stroke="#e2e8f0" stroke-width="6" fill="none" />
                                                            <circle cx="40" cy="40" r="35" stroke="#3b82f6" stroke-width="6" fill="none"
                                                                id="progressCircle" stroke-dasharray="220" stroke-dashoffset="0" />
                                                        </svg>
                                                        <div class="timer-text">
                                                            <span id="timerDisplay">{{ floor($remainingSeconds / 60) }}:{{ str_pad($remainingSeconds % 60, 2, '0', STR_PAD_LEFT) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Questions Container -->
                        <div class="container-fluid">
                            @foreach($questions as $index => $question)
                            <div class="question-container {{ $index == 0 ? 'active' : '' }}"
                                id="question-{{ $question->id }}"
                                data-question-number="{{ $index + 1 }}"
                                data-question-type="{{ $question->type }}">

                                <div class="row">
                                    <!-- Question Card -->
                                    <div class="col-lg-8">
                                        <div class="card question-card">
                                            <div class="card-body">
                                                <!-- Question Header Badge -->
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge badge-primary">{{ __('Question') }} {{ $index + 1 }}</span>
                                                </div>

                                                <!-- Question Text -->
                                                <h6 class="mb-3 question-text math-content fw-bold">
                                                    @if(strpos($question->text, '<math') !==false)
                                                        {!! $question->text !!}
                                                        @else
                                                        {{ $question->text ?? __('Question text not found') }}
                                                        @endif
                                                </h6>

                                                <!-- Question Image -->
                                                @if($question->image)
                                                @php
                                                $showImage = false;
                                                $isLogoImage = ($question->image === 'medicaltest.png');

                                                if ($question->type === 'single_choice') {
                                                // single_choice bo'lsa har doim rasm ko'rsat
                                                $showImage = true;
                                                } elseif (in_array($question->type, ['matching', 'sequence'])) {
                                                // matching yoki sequence bo'lsa faqat medicaltest.png bo'lmaganda ko'rsat
                                                $showImage = !$isLogoImage;
                                                }
                                                @endphp

                                                @if($showImage)
                                                <div class="text-center mb-3">
                                                    @if($isLogoImage)
                                                    <img src="{{ asset('storage/logo.png') }}"
                                                        class="img-fluid rounded border question-image"
                                                        style="max-height: 600px; width: auto; object-fit: contain; max-width: 100%;"
                                                        alt="{{ __('Logo') }}">
                                                    @else
                                                    <img src="{{ asset('storage/questions/' . $question->image) }}"
                                                        class="img-fluid w-100 rounded border question-image"
                                                        alt="{{ __('Question image') }}">
                                                    @endif
                                                </div>
                                                @endif
                                                @endif


                                                <!-- MATCHING: Show left and right items in Question Card -->
                                                @if($question->type === 'matching')
                                                @php
                                                $leftItems = $question->matchingPairs()->where('side', 'left')->orderBy('order')->get();
                                                $rightItems = $question->matchingPairs()->where('side', 'right')->orderBy('order')->get();
                                                @endphp
                                                <div class="matching-container mt-4">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <div class="matching-column left">
                                                                <h6 class="text-primary mb-3">{{ $question->left_items_title ?? __('Left Side') }}</h6>
                                                                @foreach($leftItems as $item)
                                                                <div class="matching-item mb-2 p-2 bg-light rounded border-start border-primary border-3 text-dark">
                                                                    <span class="matching-key fw-bold text-primary">{{ $item->key }}.</span>
                                                                    <span class="math-content ms-2">
                                                                        @if(strpos($item->text, '<math') !==false || strpos($item->text, '<sub') !==false || strpos($item->text, '<sup') !==false)
                                                                                    {!! $item->text !!}
                                                                                    @else
                                                                                    {{ $item->text }}
                                                                                    @endif
                                                                    </span>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 mb-3">
                                                            <div class="matching-column right">
                                                                <h6 class="text-success mb-3">{{ $question->right_items_title ?? __('Right Side') }}</h6>
                                                                @foreach($rightItems as $item)
                                                                <div class="matching-item mb-2 p-2 bg-light rounded border-start border-success border-3 text-dark">
                                                                    <span class="matching-key fw-bold text-success">{{ $item->key }})</span>
                                                                    <span class="math-content ms-2">{!! $item->text !!}</span>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @endif


                                                <!-- SEQUENCE: Show sequence items -->
                                                @if($question->type === 'sequence')
                                                <div class="sequence-items-container">
                                                    @foreach($question->answers as $seqIndex => $seqAnswer)
                                                    <div class="sequence-item mb-2">
                                                        <span class="badge bg-light text-dark me-2">{{ $seqIndex + 1 }}</span>
                                                        <span class="math-content">
                                                            @if(
                                                            strpos($seqAnswer->text, '<math') !==false ||
                                                                strpos($seqAnswer->text, '<sub') !==false ||
                                                                    strpos($seqAnswer->text, '<sup') !==false
                                                                        )
                                                                        {!! $seqAnswer->text !!}
                                                                        @else
                                                                        {{ $seqAnswer->text }}
                                                                        @endif
                                                        </span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Answer Options (Right Side) -->
                                    <div class="col-lg-4">
                                        @if($question->type === 'single_choice')
                                        <div class="card">
                                            <div class="card-body">
                                                @php
                                                $studentAnswer = $studentAnswers->where('question_id', $question->id)->first();
                                                $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                                                $isMultiple = $question->is_multiple ?? false;
                                                @endphp
                                                <input type="hidden"
                                                    id="studentAnswerId{{ $question->id }}"
                                                    value="{{ $studentAnswer?->id ?? '' }}">

                                                <!-- Question Type Badge -->
                                                <div class="mb-3">
                                                    @if($isMultiple)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="icofont icofont-ui-check"></i> {{ __('Multiple Choice') }}
                                                    </span>
                                                    <small class="text-muted d-block mt-1">
                                                        {{ __('Select all correct answers') }}
                                                    </small>
                                                    @else
                                                    <span class="badge bg-info">
                                                        <i class="icofont icofont-check-alt"></i> {{ __('Single Choice') }}
                                                    </span>

                                                    @endif
                                                </div>

                                                <!-- Answer Variants -->
                                                <div class="answers-container">
                                                    @foreach($question->answers as $answerIndex => $answer)
                                                    <div class="variant-card mb-2"
                                                        data-answer-id="{{ $answer->id }}"
                                                        data-question-id="{{ $question->id }}"
                                                        data-is-multiple="{{ $isMultiple ? 'true' : 'false' }}">
                                                        <input class="form-check-input"
                                                            type="radio"
                                                            name="question_{{ $question->id }}"
                                                            value="{{ $answer->id }}"
                                                            id="answer_{{ $answer->id }}"
                                                            style="display: none;">
                                                        <label class="form-check-label w-100"
                                                            for="answer_{{ $answer->id }}"
                                                            style="cursor: pointer; margin: 0; display: flex; align-items: center; width: 100%;">
                                                            <span class="badge me-2">{{ $letters[$answerIndex] ?? ($answerIndex + 1) }}</span>
                                                            <span class="answer-text math-content">
                                                                @if(strpos($answer->text, '<math') !==false || strpos($answer->text, '<sub') !==false || strpos($answer->text, '<sup') !==false)
                                                                            {!! $answer->text !!}
                                                                            @else
                                                                            {{ $answer->text }}
                                                                            @endif
                                                            </span>
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>

                                                <!-- Submit Button Below Answer Options -->
                                                <button type="button"
                                                    class="btn btn-primary submit-btn w-100 mt-3"
                                                    id="submitBtn{{ $question->id }}"
                                                    data-question-id="{{ $question->id }}"
                                                    disabled>
                                                    <i class="icofont icofont-check me-1"></i>
                                                    <small>{{ __('Confirm Answer') }}</small>
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        @if($question->type === 'matching')
                                        <div class="card">
                                            <div class="card-body">
                                                @php
                                                $studentAnswer = $studentAnswers->where('question_id', $question->id)->first();
                                                $leftItems = $question->matchingPairs()->where('side', 'left')->orderBy('order')->get();
                                                $rightItems = $question->matchingPairs()->where('side', 'right')->orderBy('order')->get();
                                                @endphp

                                                <input type="hidden"
                                                    id="studentAnswerId{{ $question->id }}"
                                                    value="{{ $studentAnswer?->id ?? '' }}">

                                                <!-- Matching Answer Selection -->
                                                <div class="answers-container">
                                                    <h6 class="mb-3 text-success">
                                                        <i class="icofont icofont-ui-check me-2"></i>
                                                        {{ __('Connect the pairs') }}
                                                    </h6>

                                                    @foreach($leftItems as $leftItem)
                                                    <div class="mb-3">
                                                        <label class="form-label">
                                                            <strong class="text-primary">{{ $leftItem->key }}.</strong>
                                                            <span class="text-muted">{{ Str::limit($leftItem->text, 50) }}</span>
                                                        </label>
                                                        <select class="form-select matching-select"
                                                            data-left-key="{{ $leftItem->key }}"
                                                            data-question-id="{{ $question->id }}">
                                                            <option value="">{{ __('Select...') }}</option>
                                                            @foreach($rightItems as $rightItem)
                                                            <option value="{{ $rightItem->key }}">
                                                                {{ $rightItem->key }}) {{ Str::limit($rightItem->text, 40) }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @endforeach
                                                </div>

                                                <!-- Submit Button for Matching -->
                                                <button type="button"
                                                    class="btn btn-primary submit-btn w-100 mt-3"
                                                    id="submitBtn{{ $question->id }}"
                                                    data-question-id="{{ $question->id }}"
                                                    disabled>
                                                    <i class="icofont icofont-check me-1"></i>
                                                    <small>{{ __('Confirm Answer') }}</small>
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        @if($question->type === 'sequence')
                                        <div class="card">
                                            <div class="card-body">
                                                @php
                                                $sequenceAnswers = $question->answers;
                                                $sequenceCount = $sequenceAnswers->count();
                                                $studentAnswer = $studentAnswers->where('question_id', $question->id)->first();
                                                @endphp
                                                <input type="hidden"
                                                    id="studentAnswerId{{ $question->id }}"
                                                    value="{{ $studentAnswer?->id ?? '' }}">

                                                <!-- Sequence Answer -->
                                                <div class="answers-container">
                                                    <h6 class="mb-3">{{ __('Enter the sequence') }}</h6>
                                                    <p class="small text-muted mb-3">{{ __('Enter the numbers in order') }}</p>
                                                    <div class="sequence-input-group">
                                                        @foreach($sequenceAnswers as $seqIndex => $seqAnswer)
                                                        <div class="sequence-input-wrapper mb-2">
                                                            <select class="form-control sequence-input"
                                                                data-position="{{ $seqIndex }}"
                                                                data-question-id="{{ $question->id }}">
                                                                <option value="">{{ __('Select...') }}</option>
                                                                @for($i = 1; $i <= $sequenceCount; $i++)
                                                                    {{-- ✅ VALUE sifatida tartib raqami (1,2,3,4...) --}}
                                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                            </select><br>
                                                            @if($seqIndex + 1 < $sequenceCount)
                                                                <i class="icofont icofont-arrow-down text-primary mt-2 fs-5"></i>
                                                                @endif
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Submit Button for Sequence -->
                                                <button type="button"
                                                    class="btn btn-primary submit-btn w-100 mt-3"
                                                    id="submitBtn{{ $question->id }}"
                                                    data-question-id="{{ $question->id }}"
                                                    disabled>
                                                    <i class="icofont icofont-check me-1"></i>
                                                    <small>{{ __('Confirm Answer') }}</small>
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Navigation and Progress (Bottom navigation) -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h6 class="mb-1">{{ __('Progress') }}</h6>
                                                <small class="text-muted">
                                                    {{ __('Completed') }}: <span id="completedCount">0</span>/{{ count($questions) }}
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-danger" id="finishTestBtn">
                                                <i class="icofont icofont-racing-flag-alt"></i>
                                                {{ __('Finish Test') }}
                                            </button>
                                        </div>

                                        <div class="progress mb-3" style="height: 6px;">
                                            <div class="progress-bar bg-success"
                                                role="progressbar"
                                                id="progressBar"
                                                style="width: 0%"
                                                aria-valuenow="0"
                                                aria-valuemin="0"
                                                aria-valuemax="{{ count($questions) }}">
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($questions as $progIndex => $progQuestion)
                                            <button type="button"
                                                class="btn btn-sm nav-btn {{ $progIndex == 0 ? 'current' : '' }}"
                                                data-question-id="{{ $progQuestion->id }}"
                                                data-question-number="{{ $progIndex + 1 }}"
                                                id="navBtn{{ $progQuestion->id }}">
                                                {{ $progIndex + 1 }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
@push('scripts')
@php
$testData = [
'remainingSeconds' => $remainingSeconds,
'totalSeconds' => $totalSeconds,
'startedAt' => $startedAt,
'csrfToken' => csrf_token(),
'routes' => [
'submitAnswer' => route('student.test.submit-answer', $testAssignment->id),
'finish' => route('student.test.submit', $testAssignment->id),
'logout' => route('logout'),
],
];

$translations = [
'timeUpTitle' => __('Time is up!'),
'timeUpText' => __('Test is being completed automatically...'),
'warningTitle' => __('Warning!'),
'selectAnswer' => __('Please select an answer!'),
'enterSequence' => __('Please enter the correct sequence!'),
'enterMatching' => __('Please complete all matching pairs!'),
'invalidSequence' => __('Invalid sequence! Use numbers from 1 to N without repetition.'),
'errorTitle' => __('Error!'),
'errorOccurred' => __('An error occurred'),
'serverError' => __('Could not connect to server'),
'finishTitle' => __('Finish Test'),
'finishText' => __('Are you sure you want to finish the test?'),
'yesFinish' => __('Yes, finish'),
'cancel' => __('Cancel'),
'testFinished' => __('Test Finished!'),
'answeredQuestions' => __('Answered Questions'),
'correctAnswers' => __('Correct Answers'),
'score' => __('Score'),
'timeUsed' => __('Time Used'),
'logout' => __('Logout'),
];
@endphp

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

<!-- DATA INITIALIZATION -->
<script>
    window.testData = @json($testData);
    window.translations = @json($translations);
    window.questions = @json($questions);
    window.appLocale = '{{ app()->getLocale() }}';
</script>

@vite('resources/js/student/test.js')

<script>
    window.renderMathContent = function(element = null) {
        if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
            try {
                if (element) {
                    MathJax.typesetPromise([element])
                        .catch((err) => console.warn('MathJax render error:', err));
                } else {
                    MathJax.typesetPromise()
                        .catch((err) => console.warn('MathJax render error:', err));
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