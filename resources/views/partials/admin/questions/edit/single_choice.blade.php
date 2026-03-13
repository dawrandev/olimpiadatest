<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-primary">
            <i class="ti ti-list me-2"></i>{{ __('Answer Options') }}
        </h5>
        <div class="btn-group">
            <button type="button" class="btn btn-success btn-sm" id="addAnswerBtn">
                <i class="icofont icofont-plus me-1"></i> {{ __('Add Answer') }}
            </button>
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#fileEditModal">
                <i class="icofont icofont-file-document me-1"></i> {{ __('Edit via File') }}
            </button>
        </div>
    </div>

    <div id="answersContainer">
        @foreach($question->answers as $i => $answer)
        <div class="answer-row mb-3" data-index="{{ $i }}">
            <div class="d-flex align-items-start gap-3">
                <!-- Correct Radio -->
                <div class="form-check mt-2">
                    <input class="form-check-input correct-radio"
                        type="radio"
                        name="correct_answer"
                        value="{{ $i }}"
                        id="correct_{{ $i }}"
                        {{ $answer->is_correct ? 'checked' : '' }}>
                    <label class="form-check-label" for="correct_{{ $i }}">
                        <span class="key-badge bg-primary text-white">{{ chr(65 + $i) }}</span>
                    </label>
                </div>

                <!-- Answer Text -->
                <div class="flex-grow-1">
                    <textarea name="answers[{{ $i }}][text]"
                        id="answer_{{ $i }}"
                        class="form-control answer-text-input"
                        rows="2"
                        placeholder="{{ __('Answer text') }}"
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
    @error('correct_answer')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<!-- Answer Template -->
<template id="answer-template">
    <div class="answer-row mb-3" data-index="{index}">
        <div class="d-flex align-items-start gap-3">
            <div class="form-check mt-2">
                <input class="form-check-input correct-radio"
                    type="radio"
                    name="correct_answer"
                    value="{index}"
                    id="correct_{index}">
                <label class="form-check-label" for="correct_{index}">
                    <span class="key-badge bg-primary text-white">{letter}</span>
                </label>
            </div>
            <div class="flex-grow-1">
                <textarea name="answers[{index}][text]"
                    id="answer_{index}"
                    class="form-control answer-text-input"
                    rows="2"
                    placeholder="{{ __('Answer text') }}"
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

                const badge = row.querySelector('.key-badge');
                if (badge) badge.textContent = String.fromCharCode(65 + i);

                const radio = row.querySelector('.correct-radio');
                if (radio) {
                    radio.value = i;
                    radio.id = 'correct_' + i;
                    radio.name = 'correct_answer';
                }

                const label = row.querySelector('.form-check-label');
                if (label) label.setAttribute('for', 'correct_' + i);

                const textInput = row.querySelector('textarea');
                if (textInput) {
                    textInput.name = `answers[${i}][text]`;
                    textInput.id = 'answer_' + i;
                }

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
            reindex();
        }

        answersContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-answer-btn')) {
                const row = e.target.closest('.answer-row');
                if (!row) return;

                const total = answersContainer.querySelectorAll('.answer-row').length;
                if (total <= 2) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("Warning") }}',
                        text: '{{ __("At least 2 answers are required") }}',
                        confirmButtonText: '{{ __("OK") }}'
                    });
                    return;
                }

                row.remove();
                reindex();
            }
        });

        addBtn.addEventListener('click', function() {
            addAnswer();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const total = answersContainer.querySelectorAll('.answer-row').length;
            if (total === 0) {
                addAnswer();
                addAnswer();
            } else if (total === 1) {
                addAnswer();
            }
        });

        // File Edit Form Submit
        $('#fileEditForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let submitBtn = $('#fileEditSubmit');
            let progressContainer = $('#fileEditProgress');
            let progressBar = progressContainer.find('.progress-bar');
            let fileInput = $('#editFile');

            if (!fileInput[0].files.length) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("Warning") }}',
                    text: '{{ __("Please select a file") }}',
                    confirmButtonText: '{{ __("OK") }}'
                });
                return;
            }

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Uploading...") }}');
            progressContainer.removeClass('d-none');
            progressBar.css('width', '30%').text('30%');

            $.ajax({
                url: "{{ route('admin.questions.updateViaFile') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total * 100;
                            progressBar.css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    progressBar.css('width', '100%').text('100%').removeClass('progress-bar-animated');

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("Success") }}',
                            text: '{{ __("Question updated successfully!") }}',
                            confirmButtonText: '{{ __("OK") }}'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("Error") }}',
                            text: response.message || '{{ __("Unknown error") }}',
                            confirmButtonText: '{{ __("OK") }}'
                        });
                        progressBar.addClass('bg-danger');
                    }
                },
                error: function(xhr) {
                    progressBar.css('width', '100%').addClass('bg-danger').removeClass('progress-bar-animated');
                    let errorMsg = '{{ __("Error uploading file") }}';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMsg += '\n\n';
                            for (let field in errors) {
                                errorMsg += errors[field].join('\n') + '\n';
                            }
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Upload Failed") }}',
                        text: errorMsg,
                        confirmButtonText: '{{ __("OK") }}'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('<i class="icofont icofont-upload"></i> {{ __("Upload and Replace") }}');
                    setTimeout(function() {
                        progressContainer.addClass('d-none');
                        progressBar.css('width', '0%').text('0%').removeClass('bg-danger').addClass('progress-bar-animated');
                    }, 3000);
                }
            });
        });

        // File input change event - validate file type
        $('#editFile').on('change', function() {
            let file = this.files[0];
            if (file) {
                if (!file.name.endsWith('.docx')) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __("Invalid File") }}',
                        text: '{{ __("Only .docx files are allowed") }}',
                        confirmButtonText: '{{ __("OK") }}'
                    });
                    $(this).val('');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("File Too Large") }}',
                        text: '{{ __("File size must not exceed 5MB") }}',
                        confirmButtonText: '{{ __("OK") }}'
                    });
                    $(this).val('');
                    return;
                }
            }
        });
    })();
</script>
@endpush