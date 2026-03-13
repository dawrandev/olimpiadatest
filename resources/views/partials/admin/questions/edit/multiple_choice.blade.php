<!-- Multiple Choice Answers Section -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-primary">
            <i class="ti ti-list me-2"></i>{{ __('Answer Options') }}
            <small class="text-muted">({{ __('Multiple correct answers allowed') }})</small>
        </h5>
        <button type="button" class="btn btn-success btn-sm" id="addAnswerBtn">
            <i class="icofont icofont-plus me-1"></i> {{ __('Add Answer') }}
        </button>
    </div>

    <div id="answersContainer">
        @foreach($question->answers as $i => $answer)
        <div class="answer-row mb-3" data-index="{{ $i }}">
            <div class="d-flex align-items-start gap-3">
                <!-- Correct Checkbox -->
                <div class="form-check mt-2">
                    <input class="form-check-input correct-checkbox"
                        type="checkbox"
                        name="correct_answers[]"
                        value="{{ $i }}"
                        id="correct_{{ $i }}"
                        {{ $answer->is_correct ? 'checked' : '' }}>
                    <label class="form-check-label" for="correct_{{ $i }}">
                        <span class="key-badge bg-success text-white">{{ chr(65 + $i) }}</span>
                    </label>
                </div>

                <!-- Answer Text with Math Support -->
                <div class="flex-grow-1">
                    <textarea name="answers[{{ $i }}][text]"
                        class="form-control answer-text-input"
                        rows="2"
                        placeholder="{{ __('Answer text (supports LaTeX)') }}"
                        required>{{ old("answers.$i.text", $answer->text) }}</textarea>
                    <input type="hidden" name="answers[{{ $i }}][id]" value="{{ $answer->id }}">
                </div>

                <!-- Remove Button -->
                <button type="button" class="btn btn-danger btn-sm remove-answer-btn mt-2">
                    <i class="icofont icofont-trash"></i>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    @error('answers')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error('correct_answers')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<!-- Answer Template -->
<template id="answer-template">
    <div class="answer-row mb-3" data-index="{index}">
        <div class="d-flex align-items-start gap-3">
            <div class="form-check mt-2">
                <input class="form-check-input correct-checkbox"
                    type="checkbox"
                    name="correct_answers[]"
                    value="{index}"
                    id="correct_{index}">
                <label class="form-check-label" for="correct_{index}">
                    <span class="key-badge bg-success text-white">{letter}</span>
                </label>
            </div>
            <div class="flex-grow-1">
                <textarea name="answers[{index}][text]"
                    class="form-control answer-text-input"
                    rows="2"
                    placeholder="{{ __('Answer text (supports LaTeX)') }}"
                    required></textarea>
                <input type="hidden" name="answers[{index}][id]" value="">
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-answer-btn mt-2">
                <i class="icofont icofont-trash"></i>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    (function() {
        let answersContainer = document.getElementById('answersContainer');
        let addBtn = document.getElementById('addAnswerBtn');
        let answerTemplate = document.getElementById('answer-template').innerHTML;
        let index = answersContainer.querySelectorAll('.answer-row').length;

        function reindex() {
            const rows = answersContainer.querySelectorAll('.answer-row');
            rows.forEach((row, i) => {
                row.dataset.index = i;

                // Update letter badge
                const badge = row.querySelector('.key-badge');
                if (badge) badge.textContent = String.fromCharCode(65 + i);

                // Update checkbox
                const checkbox = row.querySelector('.correct-checkbox');
                if (checkbox) {
                    checkbox.value = i;
                    checkbox.id = 'correct_' + i;
                }

                // Update label
                const label = row.querySelector('.form-check-label');
                if (label) label.setAttribute('for', 'correct_' + i);

                // Update textarea name
                const textInput = row.querySelector('textarea');
                if (textInput) {
                    textInput.name = `answers[${i}][text]`;
                }

                // Update hidden id
                const hiddenId = row.querySelector('input[type="hidden"]');
                if (hiddenId) {
                    hiddenId.name = `answers[${i}][id]`;
                }
            });
            index = rows.length;
        }

        function addAnswer() {
            const letter = String.fromCharCode(65 + index);
            const html = answerTemplate
                .replaceAll('{index}', index)
                .replaceAll('{letter}', letter);

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            const node = wrapper.firstElementChild;

            answersContainer.appendChild(node);

            // Add event listener for math preview
            const textarea = node.querySelector('.answer-text-input');
            const preview = node.querySelector('.answer-math-preview');
            if (textarea && preview) {
                textarea.addEventListener('input', function() {
                    updateMathPreview(textarea, preview);
                });
            }

            reindex();
        }

        // Remove handler
        answersContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-answer-btn')) {
                const row = e.target.closest('.answer-row');
                if (!row) return;

                const total = answersContainer.querySelectorAll('.answer-row').length;
                if (total <= 2) {
                    alert('{{ __("At least 2 answers are required") }}');
                    return;
                }

                row.remove();
                reindex();
            }
        });

        addBtn.addEventListener('click', function() {
            addAnswer();
        });

        // Form submission validation
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            const checkedBoxes = answersContainer.querySelectorAll('.correct-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('{{ __("Please select at least one correct answer") }}');
                return false;
            }
        });

        // Initial setup
        document.addEventListener('DOMContentLoaded', function() {
            const total = answersContainer.querySelectorAll('.answer-row').length;
            if (total === 0) {
                addAnswer();
                addAnswer();
            } else if (total === 1) {
                addAnswer();
            }

            // Add math preview listeners to existing answers
            const textareas = answersContainer.querySelectorAll('.answer-text-input');
            textareas.forEach((textarea) => {
                const preview = textarea.closest('.answer-row').querySelector('.answer-math-preview');
                if (preview) {
                    textarea.addEventListener('input', function() {
                        updateMathPreview(textarea, preview);
                    });

                    // Initial render
                    if (window.MathJax) {
                        MathJax.typesetPromise([preview]);
                    }
                }
            });
        });
    })();
</script>
@endpush