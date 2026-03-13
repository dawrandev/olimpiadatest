<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('Test Result') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #374151;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px;
        }

        .summary {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .question {
            margin-bottom: 25px;
            page-break-inside: avoid;
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 5px;
        }

        .question-header {
            background: #e3f2fd;
            padding: 10px;
            margin-bottom: 10px;
            font-weight: bold;
            border-radius: 3px;
        }

        .question-text {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 13px;
        }

        .answer {
            padding: 8px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .correct {
            background: #c8e6c9;
            border-color: #4caf50;
        }

        .wrong {
            background: #ffcdd2;
            border-color: #f44336;
        }

        .selected {
            font-weight: bold;
        }

        /* Matching styles */
        .matching-section {
            margin: 15px 0;
        }

        .matching-title {
            font-weight: bold;
            margin: 10px 0 5px 0;
            color: #1976d2;
        }

        .matching-items {
            margin-bottom: 15px;
        }

        .matching-item {
            padding: 6px;
            margin: 3px 0;
            background: #f5f5f5;
            border-left: 3px solid #1976d2;
        }

        .matching-pair {
            padding: 6px;
            margin: 3px 0;
            border: 1px solid #ddd;
        }

        .matching-pair.correct {
            background: #c8e6c9;
        }

        .matching-pair.incorrect {
            background: #ffcdd2;
        }

        /* Sequence styles */
        .sequence-section {
            margin: 15px 0;
        }

        .sequence-items {
            margin-bottom: 15px;
        }

        .sequence-item {
            padding: 6px;
            margin: 3px 0;
            background: #f5f5f5;
            border-left: 2px solid #1976d2;
            display: inline-block;
            min-width: 30px;
            text-align: center;
        }

        .sequence-answer {
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
        }

        .sequence-answer.correct {
            background: #c8e6c9;
        }

        .sequence-answer.incorrect {
            background: #ffcdd2;
        }

        .retake-badge {
            background: #fff3cd;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            color: #856404;
            display: inline-block;
        }

        .letters {
            color: #9ca3af;
            margin-right: 5px;
        }

        .arrow {
            color: #9ca3af;
            margin: 0 5px;
        }

        .checkmark {
            color: #10b981;
            margin-left: 5px;
        }

        .crossmark {
            color: #ef4444;
            margin-left: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ __('Test Result') }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>{{ __('Student') }}:</strong> {{ $testResult->student->full_name }}
                @if($testAssignment->is_retake)
                <span class="retake-badge">{{ __('RETAKE') }}</span>
                @endif
            </td>
            <td>
                <strong>{{ __('Login') }}:</strong> {{ $testResult->student->user->login }}
            </td>
        </tr>
        <tr>
            <td><strong>{{ __('Group') }}:</strong> {{ $testAssignment->group?->name ?? __('No group') }}</td>
            <td><strong>{{ __('Subject') }}:</strong> {{ $testAssignment->subject?->translations?->firstWhere('language_id', $testAssignment->language_id)?->name ?? __('No subject') }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>{{ __('Test Date') }}:</strong> {{ $testAssignment->start_time->format('d.m.Y H:i') }}</td>
        </tr>
    </table>

    <div class="summary">
        <strong>{{ __('Score') }}:</strong> {{ $testResult->score }}%
        ({{ $testResult->correct_answers }}/{{ $testResult->total_questions }}) |
        <strong>{{ __('Grade') }}:</strong> {{ $testResult->grade ?? __('N/A') }} |
        <strong>{{ __('Status') }}:</strong> {{ $testResult->score >= 60 ? __('Passed') : __('Failed') }}
    </div>

    @foreach($studentAnswers as $index => $sa)
    @php
    $question = $sa->question;
    $isCorrect = $sa->is_correct;
    $partialScore = $sa->partial_score ?? 0;

    // Determine if multiple choice
    $isMultiple = false;
    if ($question->type === 'single_choice') {
    $correctCount = $question->answers->where('is_correct', true)->count();
    $isMultiple = $correctCount > 1;
    }

    $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    @endphp

    <div class="question">
        <div class="question-header">
            <strong>{{ $index + 1 }}.</strong>
            @if($question->type === 'single_choice')
            {{ $isMultiple ? __('Multiple Choice') : __('Single Choice') }}
            @elseif($question->type === 'matching')
            {{ __('Matching') }}
            @elseif($question->type === 'sequence')
            {{ __('Sequence') }}
            @endif
            -
            @if($isCorrect)
            ✓ {{ __('Correct') }}
            @if($partialScore > 0 && $partialScore < 100)
                ({{ $partialScore }}%)
                @endif
                @else
                ✗ {{ __('Incorrect') }}
                @if($partialScore> 0)
                ({{ $partialScore }}%)
                @endif
                @endif
        </div>

        <div class="question-text">{!! $question->text !!}</div>

        @if($question->image)
        <div style="text-align: center; margin: 10px 0;">
            <img src="{{ public_path('storage/questions/' . $question->image) }}"
                style="max-width: 100%; max-height: 200px; border: 1px solid #ddd;"
                alt="{{ __('Question image') }}">
        </div>
        @endif

        {{-- SINGLE CHOICE OR MULTIPLE CHOICE --}}
        @if($question->type === 'single_choice')
        @php
        $selectedIds = [];
        if ($isMultiple && $sa->answer_text) {
        $selectedIds = array_map('intval', explode(',', $sa->answer_text));
        }
        @endphp

        @foreach($question->answers as $answerIndex => $answer)
        @php
        $isSelected = $isMultiple
        ? in_array($answer->id, $selectedIds)
        : ($sa->answer_id == $answer->id);
        $isAnswerCorrect = $answer->is_correct;

        $class = '';
        if ($isSelected && $isAnswerCorrect) {
        $class = 'correct selected';
        } elseif ($isSelected && !$isAnswerCorrect) {
        $class = 'wrong selected';
        } elseif (!$isSelected && $isAnswerCorrect) {
        $class = 'correct';
        }
        @endphp

        <div class="answer {{ $class }}">
            <span class="letters">{{ $letters[$answerIndex] }}.</span>
            {!! $answer->text !!}
            @if($isSelected)
            <span class="arrow">←</span> {{ __('Student answer') }}
            @endif
            @if($isAnswerCorrect)
            <span class="checkmark">✓</span> {{ __('Correct answer') }}
            @endif
        </div>
        @endforeach
        @endif

        {{-- MATCHING TYPE --}}
        @if($question->type === 'matching')
        <div class="matching-section">
            <div class="matching-items">
                <div class="matching-title">{{ __('Left Side') }}:</div>
                @foreach($question->matchingPairs->where('side', 'left')->sortBy('order') as $pair)
                <div class="matching-item">{{ $pair->key }}. {{ $pair->text }}</div>
                @endforeach

                <div class="matching-title" style="margin-top: 10px;">{{ __('Right Side') }}:</div>
                @foreach($question->matchingPairs->where('side', 'right')->sortBy('order') as $pair)
                <div class="matching-item">{{ $pair->key }}. {{ $pair->text }}</div>
                @endforeach
            </div>

            @if(isset($sa->student_matching) && count($sa->student_matching) > 0)
            <div>
                <strong>{{ __('Student Answer') }}:</strong>
                @foreach($sa->student_matching as $left => $right)
                @php
                $isMatchCorrect = isset($sa->correct_matching[$left]) && $sa->correct_matching[$left] == $right;
                @endphp
                <div class="matching-pair {{ $isMatchCorrect ? 'correct' : 'incorrect' }}">
                    {{ $left }} → {{ $right }}
                    {{ $isMatchCorrect ? '✓' : '✗' }}
                </div>
                @endforeach
            </div>
            @endif

            @if(isset($sa->correct_matching))
            <div style="margin-top: 10px;">
                <strong>{{ __('Correct Answer') }}:</strong>
                <div class="matching-pair correct">
                    @foreach($sa->correct_matching as $left => $right)
                    {{ $left }}-{{ $right }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- SEQUENCE TYPE --}}
        @if($question->type === 'sequence')
        <div class="sequence-section">
            <strong>{{ __('Items to Order') }}:</strong>
            <div class="sequence-items">
                @foreach($question->answers->sortBy('id') as $seqItem)
                <div class="matching-item">{{ $loop->iteration }}. {!! $seqItem->text !!}</div>
                @endforeach
            </div>

            @if(isset($sa->parsed_sequence_orders))
            <div style="margin-top: 10px;">
                <strong>{{ __('Student Sequence') }}:</strong>
                <div class="sequence-answer {{ $isCorrect ? 'correct' : 'incorrect' }}">
                    @foreach($sa->parsed_sequence_orders as $displayNumber)
                    <span class="sequence-item">{{ $displayNumber }}</span>
                    @if(!$loop->last) → @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($sa->correct_sequence))
            <div style="margin-top: 10px;">
                <strong>{{ __('Correct Sequence') }}:</strong>
                <div class="sequence-answer correct">
                    @foreach($sa->correct_sequence as $order)
                    <span class="sequence-item">{{ $order }}</span>
                    @if(!$loop->last) → @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
    @endforeach

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
        {{ __('Generated on') }}: {{ now()->format('d.m.Y H:i') }} | {{ config('app.name') }}
    </div>
</body>

</html>