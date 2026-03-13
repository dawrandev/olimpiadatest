@extends('layouts.admin.main')
@vite([
'resources/css/admin/questions/create.css', 'resources/js/admin/questions/edit.js', 'resources/css/admin/questions/edit.css'
])
@section('title', __('Edit Question and Answers'))

@section('content')
<x-admin.breadcrumb :title="__('Edit Question and Answers')">
    <a href="{{ route('admin.questions.index') }}" class="btn btn-primary">
        <i class="icofont icofont-arrow-left"></i>
        {{__('Back to Questions')}}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid questions-create">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="icofont icofont-edit me-2"></i>
                            {{__('Edit Question')}}
                        </h4>
                        @php
                        // Bir nechta to'g'ri javob borligini tekshirish
                        $correctAnswersCount = $question->answers->where('is_correct', true)->count();
                        $isMultipleChoice = $correctAnswersCount > 1;
                        $displayType = $isMultipleChoice ? 'Multiple Choice' : ucfirst(str_replace('_', ' ', $question->type));
                        @endphp
                        <span class="question-type-badge badge bg-light text-primary">
                            {{ __($displayType) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <form id="questionForm" action="{{ route('admin.questions.update', $question->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="type" value="{{ $question->type }}">

                        <!-- Image Upload Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">
                                    <i class="icofont icofont-image me-2"></i>{{__('Question Image')}}
                                </label>

                                @php
                                $currentImage = $question->image;
                                @endphp

                                <div class="image-upload-container @if($currentImage) has-image @endif">
                                    <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">

                                    <button type="button" class="btn btn-outline-primary btn-upload" onclick="document.getElementById('imageInput').click()">
                                        <i class="icofont icofont-upload me-2"></i>
                                        @if($currentImage) {{__('Change Image')}} @else {{__('Select Image')}} @endif
                                    </button>

                                    @if($currentImage)
                                    <div id="currentImage" class="image-preview-container mt-3">
                                        <div class="preview-card">
                                            <img id="currentImg" src="{{ asset('storage/questions/' . $currentImage) }}" alt="Current Image" class="preview-image" style="max-height:120px; object-fit:cover;">
                                            <div class="preview-info">
                                                <span class="file-name">{{__('Current image')}}</span>
                                                <span class="file-size text-muted">{{__('Existing')}}</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removeCurrentImage()">
                                                <i class="icofont icofont-close"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endif

                                    <div id="imagePreview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="preview-card">
                                            <img id="previewImg" src="" alt="Preview" class="preview-image" style="max-height:120px; object-fit:cover;">
                                            <div class="preview-info">
                                                <span id="fileName" class="file-name"></span>
                                                <span id="fileSize" class="file-size text-muted"></span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="removeImagePreview()">
                                                <i class="icofont icofont-close"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <input type="hidden" id="removeCurrentImage" name="remove_current_image" value="0">
                                </div>

                                @error('image')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                {{ __('Question Text') }}
                            </label>

                            <textarea name="text" id="questionText" class="form-control" rows="5" required>{{ old('text', $question->text) }}</textarea>

                            @error('text')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dynamic Content Based on Question Type -->
                        @switch($question->type)
                        @case('single_choice')
                        @php
                        // Bir nechta to'g'ri javob bormi tekshirish
                        $correctAnswersCount = $question->answers->where('is_correct', true)->count();
                        @endphp

                        @if($correctAnswersCount > 1)
                        {{-- Agar 1 dan ortiq to'g'ri javob bo'lsa - multiple choice --}}
                        @include('partials.admin.questions.edit.multiple_choice')
                        @else
                        {{-- Oddiy single choice --}}
                        @include('partials.admin.questions.edit.single_choice')
                        @endif
                        @break

                        @case('matching')
                        @include('partials.admin.questions.edit.matching')
                        @break

                        @case('sequence')
                        @include('partials.admin.questions.edit.sequence')
                        @break
                        @endswitch

                        <!-- Submit Buttons -->
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                <i class="icofont icofont-arrow-left"></i> {{__('Cancel')}}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icofont icofont-save"></i> {{__('Update Question')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILE EDIT MODAL -->
<div class="modal fade" id="fileEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="icofont icofont-file-document me-2"></i>
                    {{ __('Edit Question via File Upload') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="fileEditForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">

                    <div class="alert alert-info">
                        <i class="icofont icofont-info-circle me-2"></i>
                        <strong>{{ __('Instructions') }}:</strong>
                        <ul class="mb-0 mt-2">
                            <li>{{ __('Upload a .docx file containing the corrected version of this question') }}</li>
                            <li>{{ __('The file should contain only ONE question') }}</li>
                            <li>{{ __('The question and answers will be replaced with the file content') }}</li>
                            <li>{{ __('Question ID will remain the same') }}</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="icofont icofont-upload me-1"></i>
                            {{ __('Select File') }} <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                            name="file"
                            id="editFile"
                            class="form-control"
                            accept=".docx"
                            required>
                        <small class="text-muted">{{ __('Only .docx files (Max: 5MB)') }}</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Current Question Preview -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Current Question') }}:</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-2 fw-bold text-dark"><strong>{{ __('Text') }}:</strong> {{ $question->text }}</p>
                                <p class="mb-0 fw-bold text-dark"><strong>{{ __('Answers') }}:</strong></p>
                                <ul class="mb-0 fw-bold text-dark">
                                    @foreach($question->answers as $i => $answer)
                                    <li class="{{ $answer->is_correct ? 'text-success fw-bold' : '' }}">
                                        {{ chr(65 + $i) }}) {{ $answer->text }}
                                        @if($answer->is_correct)
                                        <i class="icofont icofont-check-circled text-success"></i>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-3 d-none" id="fileEditProgress">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar"
                                style="width: 0%">0%</div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="icofont icofont-close"></i> {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-warning" id="fileEditSubmit">
                            <i class="icofont icofont-upload"></i> {{ __('Upload and Replace') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Image Preview Handlers
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const currentImage = document.getElementById('currentImage');
    const removeCurrentImageInput = document.getElementById('removeCurrentImage');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewImg.src = ev.target.result;
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                imagePreview.style.display = 'block';
                if (currentImage) currentImage.style.display = 'none';
                removeCurrentImageInput.value = '0';
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeImagePreview = function() {
        if (imageInput) imageInput.value = '';
        if (imagePreview) imagePreview.style.display = 'none';
        if (currentImage) currentImage.style.display = 'block';
        removeCurrentImageInput.value = '0';
    };

    window.removeCurrentImage = function() {
        if (currentImage) currentImage.style.display = 'none';
        removeCurrentImageInput.value = '1';
    };

    // FILE EDIT FORM HANDLER
    $(document).ready(function() {
        $(document).on('submit', '#fileEditForm', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let submitBtn = $('#fileEditSubmit');
            let progressContainer = $('#fileEditProgress');
            let progressBar = progressContainer.find('.progress-bar');
            let fileInput = $('#editFile');

            if (!fileInput[0].files.length) {
                alert('{{ __("Please select a file") }}');
                return;
            }

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Uploading...") }}');
            progressContainer.removeClass('d-none');
            progressBar.css('width', '30%').text('30%');
        });

        $(document).on('change', '#editFile', function() {
            let file = this.files[0];
            if (file) {
                if (!file.name.endsWith('.docx')) {
                    alert('{{ __("Only .docx files are allowed") }}');
                    $(this).val('');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('{{ __("File size must not exceed 5MB") }}');
                    $(this).val('');
                    return;
                }
            }
        });
    });
</script>
@endpush

@endsection