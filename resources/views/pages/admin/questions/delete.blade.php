@extends('layouts.admin.main')

@section('title', __('Delete Questions'))

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
@endpush

@vite(['resources/css/admin/questions/delete.css'])

@section('content')
<x-admin.breadcrumb :title="__('Delete Questions')">
    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
        <i class="icofont icofont-arrow-left"></i>
        {{__('Back to Questions')}}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <!-- Warning Info Box -->
    <div class="info-box">
        <h5 class="mb-2">
            <i class="icofont icofont-warning-alt me-2"></i>
            {{__('Mass Delete Questions')}}
        </h5>
        <p class="mb-0">
            {{__('This tool allows you to delete all questions belonging to a specific subject or topic. The subject/topic itself will not be deleted, only the questions.')}}
        </p>
    </div>

    <div class="row">
        <!-- Filter Section -->
        <div class="col-lg-8">
            <div class="card delete-card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="icofont icofont-ui-delete me-2"></i>
                        {{__('Select Criteria')}}
                    </h5>
                </div>
                <div class="card-body">
                    <form id="deleteQuestionsForm" action="{{ route('admin.questions.mass-delete') }}" method="POST">
                        @csrf

                        <!-- Delete Type Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-listine-dots me-1"></i>
                                {{__('Delete By')}} <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline p-3 border rounded w-100">
                                        <input class="form-check-input" type="radio" name="delete_type" id="deleteBySubject" value="subject" checked>
                                        <label class="form-check-label w-100" for="deleteBySubject">
                                            <i class="icofont icofont-book-alt text-primary me-2"></i>
                                            <strong>{{__('By Subject')}}</strong>
                                            <small class="d-block text-muted">{{__('Delete all questions in a subject')}}</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline p-3 border rounded w-100">
                                        <input class="form-check-input" type="radio" name="delete_type" id="deleteByTopic" value="topic">
                                        <label class="form-check-label w-100" for="deleteByTopic">
                                            <i class="icofont icofont-list text-success me-2"></i>
                                            <strong>{{__('By Topic')}}</strong>
                                            <small class="d-block text-muted">{{__('Delete all questions in a topic')}}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Language Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-globe me-1"></i>
                                {{__('Language')}} <span class="text-danger">*</span>
                            </label>
                            <select id="languageSelect" name="language_id" class="form-select" required>
                                <option value="">{{__('Select Language')}}</option>
                                @foreach(getLanguages() as $lang)
                                <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subject Selection -->
                        <div class="mb-3" id="subjectSelectContainer">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-book-alt me-1"></i>
                                {{__('Subject')}} <span class="text-danger">*</span>
                            </label>
                            <select id="subjectSelect" name="subject_id" class="form-select js-select2" required>
                                <option value="">{{__('First select language')}}</option>
                            </select>
                        </div>

                        <!-- Topic Selection (Hidden by default) -->
                        <div class="mb-3 d-none" id="topicSelectContainer">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-list me-1"></i>
                                {{__('Topic')}} <span class="text-danger">*</span>
                            </label>
                            <select id="topicSelect" name="topic_id" class="form-select js-select2">
                                <option value="">{{__('First select subject')}}</option>
                            </select>
                        </div>

                        <div class="warning-box" id="warningBox" style="display: none;">
                            <div class="d-flex align-items-start">
                                <i class="icofont icofont-warning text-warning fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-2">{{__('Warning!')}}</h6>
                                    <p class="mb-2" id="warningText"></p>
                                    <p class="mb-0"><strong>{{__('This action cannot be undone!')}}</strong></p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" id="checkQuestionsBtn" class="btn btn-primary">
                                <i class="icofont icofont-search me-1"></i>
                                {{__('Check Questions Count')}}
                            </button>
                            <button type="submit" id="deleteBtn" class="btn delete-btn-danger text-white" disabled>
                                <i class="icofont icofont-ui-delete me-1"></i>
                                {{__('Delete Questions')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="icofont icofont-chart-bar-graph me-2"></i>
                        {{__('Statistics')}}
                    </h5>
                </div>
                <div class="card-body">
                    <div id="statsContainer">
                        <div class="text-center text-muted py-4">
                            <i class="icofont icofont-info-circle fs-3"></i>
                            <p class="mt-2">{{__('Select criteria to see statistics')}}</p>
                        </div>
                    </div>

                    <!-- Selected Criteria Display -->
                    <div id="selectedCriteria" class="mt-4" style="display: none;">
                        <h6 class="mb-3">
                            <i class="icofont icofont-check-circled text-success me-1"></i>
                            {{__('Selected:')}}
                        </h6>
                        <div id="criteriaDisplay"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="icofont icofont-warning me-2"></i>
                    {{__('Confirm Deletion')}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <i class="icofont icofont-trash fs-1 text-danger mb-3"></i>
                    <h5 class="mb-3">{{__('Are you absolutely sure?')}}</h5>
                    <p id="confirmDeleteText" class="text-muted"></p>
                    <div class="alert alert-danger mt-3">
                        <i class="icofont icofont-warning-alt me-2"></i>
                        {{__('This action is permanent and cannot be undone!')}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="icofont icofont-close-line me-1"></i>
                    {{__('Cancel')}}
                </button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="icofont icofont-ui-delete me-1"></i>
                    {{__('Yes, Delete All')}}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.js-select2').select2({
            placeholder: '{{__("Select an option")}}',
            allowClear: true
        });

        let questionsCount = 0;
        let selectedLanguageName = '';
        let selectedSubjectName = '';
        let selectedTopicName = '';

        $('input[name="delete_type"]').change(function() {
            const deleteType = $(this).val();

            if (deleteType === 'subject') {
                $('#topicSelectContainer').addClass('d-none');
                $('#topicSelect').prop('required', false);
            } else {
                $('#topicSelectContainer').removeClass('d-none');
                $('#topicSelect').prop('required', true);
            }

            resetForm();
        });

        $('#languageSelect').change(function() {
            const languageId = $(this).val();
            selectedLanguageName = $(this).find('option:selected').text();

            if (!languageId) {
                $('#subjectSelect').html('<option value="">{{__("First select language")}}</option>');
                resetForm();
                return;
            }

            const subjectsUrl = '{{ route("admin.ajax.subjects.byLanguage", ["language" => ":languageId"]) }}'.replace(':languageId', languageId);

            $.ajax({
                url: subjectsUrl,
                type: 'GET',
                success: function(response) {
                    let options = '<option value="">{{__("Select Subject")}}</option>';
                    response.forEach(function(subject) {
                        const subjectName = subject.translations && subject.translations.length > 0 ?
                            subject.translations[0].name :
                            'No name';
                        const questionsInfo = subject.questions_count !== undefined ?
                            ` (${subject.questions_count})` :
                            '';
                        options += `<option value="${subject.id}">${subjectName}${questionsInfo}</option>`;
                    });
                    $('#subjectSelect').html(options).trigger('change.select2');
                },
                error: function() {
                    toastr.error('{{__("Error loading subjects")}}');
                    $('#subjectSelect').html('<option value="">{{__("Error loading subjects")}}</option>');
                }
            });

            resetForm();
        });

        $('#subjectSelect').change(function() {
            const subjectId = $(this).val();
            selectedSubjectName = $(this).find('option:selected').text();

            if (!subjectId) {
                $('#topicSelect').html('<option value="">{{__("First select subject")}}</option>');
                resetForm();
                return;
            }

            if ($('#deleteByTopic').is(':checked')) {
                const languageId = $('#languageSelect').val();

                if (!languageId) {
                    toastr.error('{{__("Please select language first")}}');
                    return;
                }

                $.ajax({
                    url: '{{ route("admin.ajax.topics.bySubjectAndLanguage") }}',
                    type: 'GET',
                    data: {
                        subject: subjectId,
                        language: languageId
                    },
                    success: function(response) {
                        let options = '<option value="">{{__("Select Topic")}}</option>';
                        response.forEach(function(topic) {
                            const topicName = topic.translations && topic.translations.length > 0 ?
                                topic.translations[0].name :
                                'No name';
                            options += `<option value="${topic.id}">${topicName}</option>`;
                        });
                        $('#topicSelect').html(options).trigger('change.select2');
                    },
                    error: function() {
                        toastr.error('{{__("Error loading topics")}}');
                        $('#topicSelect').html('<option value="">{{__("Error loading topics")}}</option>');
                    }
                });
            }

            resetForm();
        });

        $('#topicSelect').change(function() {
            selectedTopicName = $(this).find('option:selected').text();
            resetForm();
        });

        $('#checkQuestionsBtn').click(function() {
            const deleteType = $('input[name="delete_type"]:checked').val();
            const languageId = $('#languageSelect').val();
            const subjectId = $('#subjectSelect').val();
            const topicId = $('#topicSelect').val();

            if (!languageId || !subjectId) {
                toastr.error('{{__("Please select language and subject")}}');
                return;
            }

            if (deleteType === 'topic' && !topicId) {
                toastr.error('{{__("Please select topic")}}');
                return;
            }

            $('#statsContainer').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

            $.ajax({
                url: '{{ route("admin.questions.count") }}',
                type: 'GET',
                data: {
                    delete_type: deleteType,
                    language_id: languageId,
                    subject_id: subjectId,
                    topic_id: topicId
                },
                success: function(response) {
                    questionsCount = response.count;

                    let statsHtml = `
                    <div class="stat-card">
                        <div class="stat-number">${questionsCount}</div>
                        <div class="stat-label">${deleteType === 'subject' ? '{{__("Questions in Subject")}}' : '{{__("Questions in Topic")}}'}</div>
                    </div>
                `;
                    $('#statsContainer').html(statsHtml);

                    let criteriaHtml = `
                    <div class="selection-badge">
                        <i class="icofont icofont-globe"></i> ${selectedLanguageName}
                    </div>
                    <div class="selection-badge">
                        <i class="icofont icofont-book-alt"></i> ${selectedSubjectName}
                    </div>
                `;

                    if (deleteType === 'topic') {
                        criteriaHtml += `
                        <div class="selection-badge">
                            <i class="icofont icofont-list"></i> ${selectedTopicName}
                        </div>
                    `;
                    }

                    $('#criteriaDisplay').html(criteriaHtml);
                    $('#selectedCriteria').show();

                    let warningText = deleteType === 'subject' ?
                        `{{__("You are about to delete")}} ${questionsCount} {{__("questions from the subject")}} "${selectedSubjectName}"` :
                        `{{__("You are about to delete")}} ${questionsCount} {{__("questions from the topic")}} "${selectedTopicName}"`;

                    $('#warningText').text(warningText);
                    $('#warningBox').show();

                    $('#deleteBtn').prop('disabled', questionsCount === 0);

                    if (questionsCount === 0) {
                        toastr.info('{{__("No questions found with selected criteria")}}');
                    }
                },
                error: function() {
                    toastr.error('{{__("Error loading statistics")}}');
                    resetForm();
                }
            });
        });

        $('#deleteQuestionsForm').submit(function(e) {
            e.preventDefault();

            if (questionsCount === 0) {
                toastr.error('{{__("No questions to delete")}}');
                return;
            }

            const deleteType = $('input[name="delete_type"]:checked').val();
            const confirmText = deleteType === 'subject' ?
                `{{__("Delete")}} ${questionsCount} {{__("questions from")}} "${selectedSubjectName}"?` :
                `{{__("Delete")}} ${questionsCount} {{__("questions from")}} "${selectedTopicName}"?`;

            $('#confirmDeleteText').text(confirmText);
            $('#confirmDeleteModal').modal('show');
        });

        $('#confirmDeleteBtn').click(function() {
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{__("Deleting...")}}');
            $('#deleteQuestionsForm').off('submit').submit();
        });

        function resetForm() {
            questionsCount = 0;
            $('#statsContainer').html(`
            <div class="text-center text-muted py-4">
                <i class="icofont icofont-info-circle fs-3"></i>
                <p class="mt-2">{{__("Select criteria to see statistics")}}</p>
            </div>
        `);
            $('#selectedCriteria').hide();
            $('#warningBox').hide();
            $('#deleteBtn').prop('disabled', true);
        }
    });
</script>
@endpush