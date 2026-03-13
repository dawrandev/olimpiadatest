@extends('layouts.admin.main')

@section('title', __('Questions Management'))

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
@vite(['resources/css/admin/questions/index.css'])
@endpush

@section('content')
<x-admin.breadcrumb :title="__('Questions Management')">
    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
        <i class="icofont icofont-plus"></i>
        {{__('Add New Question')}}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">
                                <i class="icofont icofont-question-circle me-2"></i>
                                @if($showSubjects)
                                {{__('Select Subject')}}
                                @else
                                {{__('Questions List')}}
                                <span class="badge bg-light text-primary ms-2 fs-6">
                                    {{ $questions->total() }}
                                </span>
                                @endif
                            </h4>
                        </div>
                        <div class="col-md-6">
                            @if(!$showSubjects)
                            <div class="input-group">
                                <input type="text"
                                    id="searchInput"
                                    class="form-control bg-white"
                                    placeholder="{{__('Search questions...')}}"
                                    value="{{ request('search') }}">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body pb-2">
                    @if($showSubjects)
                    <!-- Fanlar sahifasi -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-globe me-1"></i>
                                {{__('Language')}}
                            </label>
                            <select id="languageFilterMain" class="form-select">
                                @foreach(getLanguages() as $lang)
                                <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="subjectsContainer" class="row">
                        @include('partials.admin.questions.subjects_list', ['subjects' => $subjects])
                    </div>
                    @else
                    <!-- Savollar sahifasi -->
                    <div class="row g-3 align-items-end mb-4">
                        <!-- Language Filter -->
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-globe me-1"></i>
                                {{__('Language')}}
                            </label>
                            <select id="languageFilter" class="form-select">
                                @foreach(getLanguages() as $lang)
                                <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden Subject ID -->
                        <input type="hidden" id="subjectFilterHidden" value="{{ request('subject_id') }}">

                        <!-- Topic Filter -->
                        <div class="col-lg-3 col-md-4">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-list me-1"></i>
                                {{__('Topic')}}
                            </label>
                            <select id="topicFilter" class="form-select js-select2">
                                <option value="">{{__('All Topics')}}</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="col-lg-3 col-md-4 d-flex gap-2">
                            <button type="button" id="applyFilters" class="btn btn-primary">
                                <i class="icofont icofont-filter me-1"></i>
                            </button>
                            <button type="button" id="backToSubjects" class="btn btn-outline-secondary">
                                <i class="icofont icofont-arrow-left me-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Questions Container -->
                    <div id="questionsContainer">
                        @include('partials.admin.questions.question_list', ['questions' => $questions])
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Question Details Modal -->
<div class="modal fade" id="questionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="icofont icofont-question-circle me-2"></i>
                    {{__('Question Details')}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="questionModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{__('Loading...')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script>
    MathJax = {
        tex: {
            inlineMath: [
                ['\\(', '\\)']
            ],
            displayMath: [
                ['\\[', '\\]']
            ]
        }
    };

    window.translations = {
        attention: '{{ __("Attention!") }}',
        selectLanguage: '{{ __("Please select a language") }}',
        loading: '{{ __("Loading...") }}',
        errorLoading: '{{ __("Error loading questions") }}',
        errorLoadingDetails: '{{ __("Error loading question details") }}',
        question: '{{ __("Question") }}',
        noTranslation: '{{ __("No translation available") }}',
        answerOptions: '{{ __("Answer Options") }}',
        deleteQuestion: '{{ __("Delete Question") }}',
        deleteConfirmText: '{{ __("Are you sure you want to delete this question? This action cannot be undone!") }}',
        confirmDelete: '{{ __("Yes, delete it!") }}',
        cancel: '{{ __("Cancel") }}',
        deleted: '{{ __("Deleted!") }}',
        deleteSuccess: '{{ __("Question has been successfully deleted.") }}',
        error: '{{ __("Error!") }}',
        deleteError: '{{ __("An error occurred while deleting the question.") }}'
    };

    // Fanlarni tanlash funksiyasi
    function selectSubject(subjectId) {
        const langId = $('#languageFilterMain').val();
        window.location.href = "{{ route('admin.questions.index') }}?subject_id=" + subjectId + "&language_id=" + langId;
    }

    $(document).ready(function() {
        $('.js-select2').select2({
            width: '100%'
        });

        @if($showSubjects)
        // Fanlar sahifasi uchun
        $('#languageFilterMain').on('change', function() {
            const langId = $(this).val();
            loadSubjects(langId);
        });

        function loadSubjects(langId) {
            $('#subjectsContainer').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>');

            $.get("{{ route('admin.ajax.subjects.byLanguage', '') }}/" + langId)
                .done(function(subjects) {
                    let html = '';
                    subjects.forEach(subject => {
                        const name = subject.translations[0]?.name ?? '{{__("No name")}}';
                        const count = subject.questions_count ?? 0;
                        html += `
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="card subject-card h-100" onclick="selectSubject(${subject.id})">
                                    <div class="card-body text-center d-flex flex-column justify-content-center">
                                        <div class="subject-icon mb-3">
                                            <i class="icofont icofont-book"></i>
                                        </div>
                                        <h5 class="card-title mb-2">${name}</h5>
                                        <div class="questions-count">${count} {{__('questions')}}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    if (subjects.length === 0) {
                        html = `
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="icofont icofont-book text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                    <h5 class="text-muted">{{__('No subjects found')}}</h5>
                                </div>
                            </div>
                        `;
                    }

                    $('#subjectsContainer').html(html);
                })
                .fail(function() {
                    $('#subjectsContainer').html('<div class="col-12"><div class="alert alert-danger">{{__("Error loading subjects")}}</div></div>');
                });
        }
        @else
        // Savollar sahifasi uchun
        const subjectId = $('#subjectFilterHidden').val();
        const initialLangId = $('#languageFilter').val();

        if (subjectId) {
            loadTopics(subjectId, initialLangId);
        }

        $('#languageFilter').on('change', function() {
            const langId = $(this).val();
            loadTopics(subjectId, langId);
        });

        function loadTopics(subjectId, langId) {
            $('#topicFilter').prop('disabled', true).html('<option value="">{{__("Loading...")}}</option>');

            $.get("{{ route('admin.ajax.topics.bySubjectAndLanguage') }}", {
                    subject: subjectId,
                    language: langId
                })
                .done(function(data) {
                    $('#topicFilter').prop('disabled', false).html('<option value="">{{__("All Topics")}}</option>');
                    data.forEach(topic => {
                        const name = topic.translations[0]?.name ?? '{{__("No name")}}';
                        $('#topicFilter').append(`<option value="${topic.id}">${name}</option>`);
                    });
                })
                .fail(function() {
                    $('#topicFilter').prop('disabled', false).html('<option value="">{{__("All Topics")}}</option>');
                    alert('{{__("Error loading topics")}}');
                });
        }

        // Filtrlarni qo'llash
        $('#applyFilters').on('click', function() {
            const params = new URLSearchParams();
            const langId = $('#languageFilter').val();
            const topicId = $('#topicFilter').val();
            const search = $('#searchInput').val();

            if (langId) params.append('language_id', langId);
            if (subjectId) params.append('subject_id', subjectId);
            if (topicId) params.append('topic_id', topicId);
            if (search) params.append('search', search);

            window.location.href = "{{ route('admin.questions.index') }}?" + params.toString();
        });

        // Qidiruv
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#applyFilters').click();
            }
        });

        // Live search with AJAX
        let searchTimeout;
        $('#searchInput').on('keyup', function(e) {
            if (e.which === 13) {
                return;
            }

            clearTimeout(searchTimeout);
            const search = $(this).val();

            if (search.length >= 3 || search.length === 0) {
                searchTimeout = setTimeout(function() {
                    const params = {
                        search: search,
                        language_id: $('#languageFilter').val(),
                        subject_id: subjectId,
                        topic_id: $('#topicFilter').val()
                    };

                    $('#questionsContainer').html(`
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{__('Loading...')}}</span>
                            </div>
                            <p class="mt-2 text-muted">{{__('Searching...')}}</p>
                        </div>
                    `);

                    $.ajax({
                        url: "{{ route('admin.questions.index') }}",
                        type: "GET",
                        data: params,
                        dataType: 'html',
                        success: function(response) {
                            $('#questionsContainer').html(response);

                            if (window.MathJax) {
                                MathJax.typesetPromise();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            $('#questionsContainer').html(`
                                <div class="alert alert-danger">
                                    <i class="icofont icofont-warning me-2"></i>
                                    {{__('Error loading questions')}}
                                </div>
                            `);
                        }
                    });
                }, 500);
            }
        });
        @endif

        // Orqaga qaytish
        $('#backToSubjects').on('click', function() {
            window.location.href = "{{ route('admin.questions.index') }}";
        });

        // Savolni ko'rish
        $(document).on('click', '.view-question-btn', function() {
            const questionId = $(this).data('question-id');
            const languageId = $(this).data('language-id');

            $('#questionModal').modal('show');
            $('#questionModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">${window.translations.loading}</span>
                    </div>
                </div>
            `);

            $.get("{{ route('admin.questions.show', ':id') }}".replace(':id', questionId), {
                    language_id: languageId
                })
                .done(function(response) {
                    if (response.success) {
                        displayQuestionDetails(response.data);
                    } else {
                        $('#questionModalBody').html(`<div class="alert alert-danger">${response.message || window.translations.errorLoadingDetails}</div>`);
                    }
                })
                .fail(function() {
                    $('#questionModalBody').html(`<div class="alert alert-danger">${window.translations.errorLoadingDetails}</div>`);
                });
        });

        function displayQuestionDetails(question) {
            let html = '';

            // Question Header
            html += `
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="badge bg-primary mb-2">${getQuestionTypeLabel(question.type)}</span>
                    <h6 class="text-muted mb-0">
                        <i class="icofont icofont-book me-1"></i> ${question.subject}
                        <i class="icofont icofont-separator mx-2">•</i>
                        <i class="icofont icofont-list me-1"></i> ${question.topic}
                    </h6>
                </div>
            </div>
            
            <div class="p-3 bg-light rounded">
                <p class="mb-0 fs-6 text-dark fw-semibold question-text">
                    ${question.text || 'No text available'}
                </p>
            </div>
            
            ${question.image_url ? `
            <div class="mt-3 text-center">
                <div class="position-relative d-inline-block">
                    <img src="${question.image_url}" 
                         alt="Question Image" 
                         class="img-fluid rounded shadow-sm question-modal-image"
                         style="max-width: 100%; max-height: 400px; cursor: pointer;"
                         onclick="openImageModal('${question.image_url}')">
                    <div class="position-absolute top-0 end-0 m-2">
                        <button class="btn btn-sm btn-light" onclick="openImageModal('${question.image_url}')" title="Rasmni kattalashtirish">
                            <i class="icofont icofont-search-2"></i>
                        </button>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;

            // Type-specific content
            if (question.type === 'single_choice') {
                html += displaySingleChoice(question);
            } else if (question.type === 'matching') {
                html += displayMatching(question);
            } else if (question.type === 'sequence') {
                html += displaySequence(question);
            }

            $('#questionModalBody').html(html);

            // Render MathJax
            if (window.MathJax) {
                MathJax.typesetPromise();
            }
        }

        function getQuestionTypeLabel(type) {
            const labels = {
                'single_choice': 'Oddiy Test',
                'matching': 'Muvofiqlik',
                'sequence': 'Ketma-ketlik'
            };
            return labels[type] || type;
        }

        // Single Choice Questions
        function displaySingleChoice(question) {
            if (!question.answers || question.answers.length === 0) {
                return '<div class="alert alert-warning">Javoblar topilmadi</div>';
            }

            let html = `
        <div>
            <h6 class="text-success mb-3">
                <i class="icofont icofont-ui-check me-2"></i>
                Javob Variantlari
            </h6>
    `;

            question.answers.forEach((answer, index) => {
                const answerText = answer.text || 'No text available';
                const isCorrect = answer.is_correct;

                html += `
            <div class="answer-item mb-3 p-3 rounded ${isCorrect ? 'correct' : 'incorrect'}" 
                 style="border: 1px solid ${isCorrect ? '#198754' : '#6c757d'}; background-color: ${isCorrect ? 'rgba(25, 135, 84, 0.1)' : '#f8f9fa'}; border-left: 4px solid ${isCorrect ? '#198754' : '#6c757d'};">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge ${isCorrect ? 'bg-success' : 'bg-secondary'}">
                        ${String.fromCharCode(65 + index)}
                    </span>
                    ${isCorrect ? '<i class="icofont icofont-check-circled text-success fs-5"></i>' : ''}
                </div>
                <p class="mb-0 answer-text" style="color: #212529 !important;">${answerText}</p>
                ${answer.image_url ? `
                <div class="mt-2">
                    <img src="${answer.image_url}" 
                         alt="Answer Image" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-width: 300px; max-height: 200px; cursor: pointer;"
                         onclick="openImageModal('${answer.image_url}')">
                </div>
                ` : ''}
            </div>
        `;
            });

            html += '</div>';
            return html;
        }

        // Matching Questions
        function displayMatching(question) {
            let html = `
        <div class="matching-container">
            <h6 class="text-primary mb-3">
                <i class="icofont icofont-link me-2"></i>
                Muvofiqlik Elementlari
            </h6>
            
            <div class="row mb-4">
                <!-- Chap tomon -->
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <strong>${question.left_items_title || 'Chap tomon'}</strong>
                        </div>
                        <div class="card-body">
    `;

            if (question.left_items && question.left_items.length > 0) {
                question.left_items.forEach(item => {
                    html += `
                <div class="mb-3 p-2 bg-light rounded border-start border-primary border-3">
                    <strong class="text-primary">${item.key}.</strong>
                    <span class="ms-2 matching-text" style="color: #212529 !important;">${item.text}</span>
                    ${item.image_url ? `
                    <div class="mt-2">
                        <img src="${item.image_url}" 
                             alt="Left Item Image" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-width: 250px; max-height: 150px; cursor: pointer;"
                             onclick="openImageModal('${item.image_url}')">
                    </div>
                    ` : ''}
                </div>
            `;
                });
            } else {
                html += '<p class="text-muted">Elementlar topilmadi</p>';
            }

            html += `
                        </div>
                    </div>
                </div>
                
                <!-- O'ng tomon -->
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <strong>${question.right_items_title || "O'ng tomon"}</strong>
                        </div>
                        <div class="card-body">
    `;

            if (question.right_items && question.right_items.length > 0) {
                question.right_items.forEach(item => {
                    html += `
                <div class="mb-3 p-2 bg-light rounded border-start border-success border-3">
                    <strong class="text-success">${item.key})</strong>
                    <span class="ms-2 matching-text" style="color: #212529 !important;">${item.text}</span>
                    ${item.image_url ? `
                    <div class="mt-2">
                        <img src="${item.image_url}" 
                             alt="Right Item Image" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-width: 250px; max-height: 150px; cursor: pointer;"
                             onclick="openImageModal('${item.image_url}')">
                    </div>
                    ` : ''}
                </div>
            `;
                });
            } else {
                html += '<p class="text-muted">Elementlar topilmadi</p>';
            }

            html += `
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Javob Variantlari -->
            <div class="mt-4">
                <h6 class="text-success mb-3">
                    <i class="icofont icofont-ui-check me-2"></i>
                    Javob Variantlari
                </h6>
    `;

            if (question.answer_variants && question.answer_variants.length > 0) {
                question.answer_variants.forEach((variant, index) => {
                    const isCorrect = variant.is_correct;
                    html += `
                <div class="mb-3 p-3 rounded ${isCorrect ? 'bg-success bg-opacity-25 border-success' : 'bg-light border-secondary'}" 
                     style="border: 1px solid; border-left: 4px solid ${isCorrect ? '#198754' : '#6c757d'};">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge ${isCorrect ? 'bg-success' : 'bg-secondary'} me-2">
                                ${index + 1}
                            </span>
                            <span class="matching-answer-text" style="color: #212529 !important;">${variant.text}</span>
                        </div>
                        ${isCorrect ? '<i class="icofont icofont-check-circled text-success fs-4"></i>' : ''}
                    </div>
                </div>
            `;
                });
            }

            html += `
            </div>
        </div>
    `;

            return html;
        }

        // Sequence Questions
        function displaySequence(question) {
            let html = `
        <div class="sequence-container">
            <h6 class="text-primary mb-3">
                <i class="icofont icofont-numbered me-2"></i>
                Ketma-ketlik Elementlari
            </h6>
            
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <strong>Tartibsiz Variantlar</strong>
                        </div>
                        <div class="card-body">
    `;

            if (question.sequence_items && question.sequence_items.length > 0) {
                question.sequence_items.forEach(item => {
                    html += `
                <div class="mb-3 p-3 bg-light rounded border-start border-info border-3">
                    <span class="badge bg-info me-2">${item.display_number})</span>
                    <span class="sequence-text text-dark">${item.text}</span>
                    ${item.image_url ? `
                    <div class="mt-2">
                        <img src="${item.image_url}" 
                             alt="Sequence Item Image" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-width: 250px; max-height: 150px; cursor: pointer;"
                             onclick="openImageModal('${item.image_url}')">
                    </div>
                    ` : ''}
                </div>
            `;
                });
            } else {
                html += '<p class="text-muted">Elementlar topilmadi</p>';
            }

            html += `
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- To'g'ri Tartib -->
            <div class="mt-4">
                <h6 class="text-success mb-3">
                    <i class="icofont icofont-check-circled me-2"></i>
                    To'g'ri Ketma-ketlik
                </h6>
                <div class="p-4 bg-success bg-opacity-25 rounded border-success" style="border: 2px solid #198754;">
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-3">
    `;

            if (question.correct_sequence && question.correct_sequence.length > 0) {
                question.correct_sequence.forEach((order, index) => {
                    const isLast = index === question.correct_sequence.length - 1;
                    html += `
                <span class="badge bg-success fs-5 px-3 py-2">${order}</span>
                ${!isLast ? '<i class="icofont icofont-arrow-right text-success fs-4"></i>' : ''}
            `;
                });
            }

            html += `
                    </div>
                </div>
                
                <!-- Explanation -->
                <div class="alert alert-info mt-3 mb-0">
                    <i class="icofont icofont-info-circle me-2"></i>
                    <small>Raqamlar to'g'ri ketma-ketlikni bildiradi. Masalan: <strong>${question.correct_sequence ? question.correct_sequence.join(', ') : ''}</strong></small>
                </div>
            </div>
        </div>
    `;

            return html;
        }

        function decodeHtml(html) {
            const txt = document.createElement('textarea');
            txt.innerHTML = html;
            return txt.value;
        }

        $(document).on('click', '.confirm-action', function(e) {
            e.preventDefault();
            const questionId = $(this).data('question-id');
            const actionType = $(this).data('action') || 'delete';
            const customTitle = $(this).data('title');
            const customText = $(this).data('text');

            let title = customTitle || window.alertTranslations.areYouSure;
            let text = customText || window.alertTranslations.cannotUndo;
            let icon = 'warning';
            let confirmText = window.alertTranslations.yesConfirm;

            if (actionType === 'delete') {
                icon = 'error';
                confirmText = window.alertTranslations.yesDelete;
            } else if (actionType === 'update') {
                icon = 'question';
                confirmText = window.alertTranslations.yesUpdate;
            } else if (actionType === 'toggle') {
                icon = 'info';
            }

            Swal.fire({
                title: decodeHtml(title),
                text: decodeHtml(text),
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: decodeHtml(confirmText),
                cancelButtonText: decodeHtml(window.alertTranslations.cancel)
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.questions.destroy', '') }}/" + questionId,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            Swal.fire({
                                icon: 'success',
                                title: decodeHtml(window.translations.deleteSuccessTitle || '{{ __("Deleted!") }}'),
                                text: decodeHtml(window.translations.deleteSuccessText || '{{ __("Question has been successfully deleted.") }}'),
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: window.translations?.deleteErrorTitle || 'Error!',
                                text: window.translations?.deleteError || 'Failed to delete the question.'
                            });
                        }
                    });
                }
            });
        });

    });

    function openImageModal(imageUrl) {
        const modalHtml = `
            <div class="modal fade" id="imageViewModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-0">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <img src="${imageUrl}" class="img-fluid" style="max-height: 80vh;" alt="Full Image">
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Avvalgi modal oynani o'chirish
        $('#imageViewModal').remove();

        // Yangi modal qo'shish
        $('body').append(modalHtml);

        // Modal ochish
        const imageModal = new bootstrap.Modal(document.getElementById('imageViewModal'));
        imageModal.show();

        // Modal yopilganda o'chirish
        $('#imageViewModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }
</script>
@endpush