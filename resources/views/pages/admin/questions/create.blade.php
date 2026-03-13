@extends('layouts.admin.main')
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<style>
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 30px;
        height: 30px;
        background-color: rgba(0, 0, 0, 0.5);
        background-size: 60% 60%;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .carousel-control-prev-icon:hover,
    .carousel-control-next-icon:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 5%;
    }

    .sequence-text ul {
        color: #000;
    }

    .ps-3.mt-2 {
        color: #000;
    }

    ul.mb-0,
    ul.mb-0 li,
    ul.mb-0 code,
    ul.mb-0 del,
    .mt-2,
    .mt-2 code,
    .mt-2 span {
        color: #000 !important;
    }
</style>
@endpush

@section('title', __('Add Question'))

@section('content')
<x-admin.breadcrumb :title="__('Add Question')">
    <a href="#" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#formatGuideModal">
        <i class="icofont icofont-question-circle"></i>
        {{ __('Format Guide') }}
    </a>
    <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
        <i class="icofont icofont-list"></i>
        {{ __('All Questions') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row d-flex justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm rounded-3">
                <div class="card-body">
                    <form id="questionForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Language Select -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Language') }} <span class="text-danger">*</span></label>
                            <select name="language_id" id="language" class="form-select">
                                <option value="">{{ __('Select Language') }}</option>
                                @foreach(getLanguages() as $lang)
                                <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Subject Select2 -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subject" class="form-select js-select2" disabled>
                                <option value="">{{ __('Select Subject') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Topic Select2 -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Topic') }} <span class="text-danger">*</span></label>
                            <select name="topic_id" id="topic" class="form-select js-select2" disabled>
                                <option value="">{{ __('Select Topic') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Upload File') }} <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control" accept=".docx,.html,.htm">
                            <small class="text-muted d-block mt-1">{{ __('Supported: .docx, .doc (Max: 3MB)') }}</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-3 d-none" id="progressContainer">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width: 0%">0%</div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="icofont icofont-save"></i> {{ __('Upload Questions') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Result') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resultContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-primary">{{ __('View Questions') }}</a>
            </div>
        </div>
    </div>
</div>

<!-- Format Guide Modal -->
<div class="modal fade" id="formatGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="icofont icofont-book-alt"></i>
                    Savollar Format Ko'rsatmasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Carousel -->
                <div id="formatCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#formatCarousel" data-bs-slide-to="0" class="active"></button>
                        <button type="button" data-bs-target="#formatCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#formatCarousel" data-bs-slide-to="2"></button>
                    </div>

                    <div class="carousel-inner">
                        <!-- Slide 1: Single Choice -->
                        <div class="carousel-item active">
                            <div class="card border-0">
                                <div class="card-header bg-success text-white">
                                    <h4 class="mb-0">
                                        <i class="icofont icofont-check-circled"></i>
                                        1. Single Choice (Bir variantli savol)
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="icofont icofont-info-circle"></i>
                                        <strong>Tavsif:</strong> Faqat bitta to'g'ri javobli savol. Marker ishlatilmaydi.
                                    </div>

                                    <!-- Namuna -->
                                    <div class="bg-light p-4 rounded mb-4" style="font-family: 'Courier New', monospace;">
                                        <div class="mb-3">
                                            <span class="badge bg-primary">Savol raqami</span>
                                            <code class="ms-2">1.</code>
                                            <span class="text-muted"> Какой из следующих препаратов является бета-блокатором?</span>
                                        </div>
                                        <div class="ps-4">
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">Noto'g'ri</span>
                                                <code class="ms-2 text-dark">a)</code> <span class="text-dark">Амлодипин</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="badge bg-success">To'g'ri</span>
                                                <code class="ms-2 text-dark">*b)</code> <span class="text-dark">Метопролол</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">Noto'g'ri</span>
                                                <code class="ms-2 text-dark">c)</code> <span class="text-dark">Эналаприл</span>
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary">Noto'g'ri</span>
                                                <code class="ms-2 text-dark">d)</code> <span class="text-dark">Фуросемид</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Qoidalar -->
                                    <h5 class="text-primary mb-3">
                                        <i class="icofont icofont-listing-box"></i>
                                        Asosiy Qoidalar:
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class="text-success">
                                                        <i class="icofont icofont-check"></i>
                                                        To'g'ri format:
                                                    </h6>
                                                    <ul class="mb-0" style="color: #000;">
                                                        <li>Savol raqami: <code>1.</code> yoki <code>2.</code></li>
                                                        <li>Variantlar: <code>a)</code>, <code>b)</code>, <code>c)</code>, <code>d)</code></li>
                                                        <li>To'g'ri javob: <code>*</code> belgisi bilan</li>
                                                        <li>Marker: <strong>ishlatilmaydi</strong></li>
                                                    </ul>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class="text-danger">
                                                        <i class="icofont icofont-close"></i>
                                                        Xato format:
                                                    </h6>
                                                    <ul class="mb-0" style="color: #000;">
                                                        <li><del>1-savol</del> → <code>1.</code> bo'lishi kerak</li>
                                                        <li><del>a.</del> → <code>a)</code> bo'lishi kerak</li>
                                                        <li><del>+ javob</del> → <code>*</code> belgisi kerak</li>
                                                        <li><del>[SINGLE]</del> → marker kerak emas</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2: Matching -->
                        <div class="carousel-item">
                            <div class="card border-0">
                                <div class="card-header bg-warning text-dark">
                                    <h4 class="mb-0">
                                        <i class="icofont icofont-link"></i>
                                        2. Matching (Moslashtirish savoli)
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="icofont icofont-info-circle"></i>
                                        <strong>Tavsif:</strong> Chap va o'ng tomonlarni moslashtiradigan savol.
                                        <code>[MATCHING]</code> marker bilan boshlanadi.
                                    </div>

                                    <!-- Namuna -->
                                    <div class="bg-light p-4 rounded mb-4" style="font-family: 'Courier New', monospace;">
                                        <div class="mb-3">
                                            <span class="badge bg-danger">Marker</span>
                                            <code class="ms-2">[MATCHING]</code>
                                        </div>
                                        <div class="mb-3">
                                            <span class="badge bg-primary">Savol</span>
                                            <code class="ms-2">2.</code>
                                            <span class="text-muted"> Установите соответствие между типом боли и заболеванием.</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <span class="badge bg-info">Chap tomon</span>
                                                    <div class="ps-3 mt-2">
                                                        <strong>Тип боли:</strong><br>
                                                        <code>А.</code> острая колющая;<br>
                                                        <code>Б.</code> тупая ноющая;<br>
                                                        <code>В.</code> жгучая загрудинная;<br>
                                                        <code>Г.</code> схваткообразная.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <span class="badge bg-info">O'ng tomon</span>
                                                    <div class="ps-3 mt-2">
                                                        <strong>Заболевание:</strong><br>
                                                        <code>1)</code> стенокардия;<br>
                                                        <code>2)</code> пневмоторакс;<br>
                                                        <code>3)</code> холецистит;<br>
                                                        <code>4)</code> остеохондроз.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ps-4 mt-3">
                                            <span class="badge bg-secondary">Javob variantlari</span>
                                            <div class="mt-2">
                                                <code>1)</code> А-1, Б-2, В-3, Г-4;<br>
                                                <span class="badge bg-success">To'g'ri</span>
                                                <code>*2)</code> А-2, Б-4, В-1, Г-3;<br>
                                                <code>3)</code> А-3, Б-1, В-2, Г-4;<br>
                                                <code>4)</code> А-4, Б-3, В-2, Г-1;
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Qoidalar -->
                                    <h5 class="text-primary mb-3">
                                        <i class="icofont icofont-listing-box"></i>
                                        Asosiy Qoidalar:
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class="text-success">
                                                        <i class="icofont icofont-check"></i>
                                                        To'g'ri format:
                                                    </h6>
                                                    <ul class="mb-0">
                                                        <li>Marker: <code>[MATCHING]</code> (birinchi qatorda)</li>
                                                        <li>Chap: <code>А.</code>, <code>Б.</code> (nuqta bilan)</li>
                                                        <li>O'ng: <code>1)</code>, <code>2)</code> (qavs bilan)</li>
                                                        <li>Javob: <code>А-1, Б-2</code> (vergul bilan)</li>
                                                        <li>To'g'ri: <code>*2)</code> yulduzcha bilan</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class="text-danger">
                                                        <i class="icofont icofont-close"></i>
                                                        Xato format:
                                                    </h6>
                                                    <ul class="mb-0">
                                                        <li><del>А)</del> → <code>А.</code> (nuqta kerak)</li>
                                                        <li><del>1.</del> → <code>1)</code> (qavs kerak)</li>
                                                        <li><del>А-1 Б-2</del> → vergul yo'q</li>
                                                        <li><del>Г антиаритмики</del> → nuqta yo'q</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3: Sequence -->
                        <div class="carousel-item">
                            <div class="card border-0">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0">
                                        <i class="icofont icofont-numbered"></i>
                                        3. Sequence (Ketma-ketlik savoli)
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-primary">
                                        <i class="icofont icofont-info-circle"></i>
                                        <strong>Tavsif:</strong> Elementlarni to'g'ri tartibda joylashtirish kerak.
                                        <code style="color: white; background-color: #333; padding: 2px 6px; border-radius: 4px;">[SEQUENCE]</code>
                                        marker bilan boshlanadi.
                                    </div>

                                    <!-- Namuna -->
                                    <div class="bg-light p-4 rounded mb-4" style="font-family: 'Courier New', monospace;">
                                        <div class="mb-3">
                                            <span class="badge bg-danger">Marker</span>
                                            <code class="ms-2">[SEQUENCE]</code>
                                        </div>
                                        <div class="mb-3">
                                            <span class="badge bg-primary">Savol</span>
                                            <code class="ms-2">3.</code>
                                            <span class="text-muted"> Последовательность этапов сестринского процесса:</span>
                                        </div>

                                        <div class="ps-4 mb-3">
                                            <span class="badge bg-info">Aralash elementlar</span>
                                            <div class="mt-2">
                                                <code>1)</code> оценка<br>
                                                <code>2)</code> сестринская диагностика<br>
                                                <code>3)</code> выполнение<br>
                                                <code>4)</code> сбор данных<br>
                                                <code>5)</code> планирование
                                            </div>
                                        </div>

                                        <div class="ps-4">
                                            <span class="badge bg-success">To'g'ri ketma-ketlik</span>
                                            <div class="mt-2">
                                                <code>Ответ: [4, 2, 5, 3, 1]</code>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                Bu degani: 4→2→5→3→1 tartibda bo'lishi kerak
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Qoidalar -->
                                    <h5 class="text-primary mb-3">
                                        <i class="icofont icofont-listing-box"></i>
                                        Asosiy Qoidalar:
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class="text-success">
                                                        <i class="icofont icofont-check"></i>
                                                        To'g'ri format:
                                                    </h6>
                                                    <ul class="mb-0">
                                                        <li>Marker: <code>[SEQUENCE]</code> (birinchi qatorda)</li>
                                                        <li>Elementlar: <code>1)</code>, <code>2)</code>, <code>3)</code></li>
                                                        <li>Javob: <code>Ответ: [4, 2, 5, 3, 1]</code></li>
                                                        <li>Format: Kvadrat qavs va vergul bilan</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class="text-danger">
                                                        <i class="icofont icofont-close"></i>
                                                        Xato format:
                                                    </h6>
                                                    <ul class="mb-0">
                                                        <li><del>Javob: 4, 2, 5</del> → qavs yo'q</li>
                                                        <li><del>[4-2-5]</del> → vergul kerak</li>
                                                        <li><del>Ответ [4,2,5]</del> → ikki nuqta yo'q</li>
                                                        <li>Kamida 2 ta element bo'lishi kerak</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carousel Controls -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#formatCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Oldingi</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#formatCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Keyingi</span>
                    </button>
                </div>

                <!-- Bottom Tips -->
                <div class="alert alert-info mt-4 mb-0">
                    <h6 class="alert-heading">
                        <i class="icofont icofont-light-bulb"></i>
                        Muhim Eslatmalar:
                    </h6>
                    <ul class="mb-0">
                        <li>Har bir savol yangi qatordan boshlanishi kerak</li>
                        <li>To'g'ri javob <code>*</code> yulduzcha bilan belgilanadi</li>
                        <li>Marker faqat matching va sequence savollarga qo'yiladi</li>
                        <li>Fayl formati: <strong>.docx</strong> (maksimal 10MB)</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="icofont icofont-close"></i>
                    Yopish
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // Init select2
        $('.js-select2').select2();

        // ---------------- LANGUAGE -> FACULTY ----------------
        $('#language').on('change', function() {
            let langId = $(this).val();

            // reset subject, topic, faculty, group
            $('#subject').empty().append('<option value="">{{ __("Select Subject") }}</option>').prop('disabled', true);
            $('#topic').empty().append('<option value="">{{ __("Select Topic") }}</option>').prop('disabled', true);
            $('#faculty').empty().append('<option value="">{{ __("Select Faculty") }}</option>').prop('disabled', true);
            $('#group').empty().append('<option value="">{{ __("Select Group") }}</option>').prop('disabled', true);

            if (!langId) return;

            // Faculty yuklash
            $('#faculty').prop('disabled', false).append('<option value="">{{ __("Loading...") }}</option>');

            $.get("{{ url('admin/ajax/faculties/by-language') }}/" + langId, function(data) {
                $('#faculty').empty().append('<option value="">{{ __("Select Faculty") }}</option>');
                data.forEach(faculty => {
                    let name = faculty.translations[0]?.name ?? '{{ __("No name") }}';
                    $('#faculty').append(`<option value="${faculty.id}">${name}</option>`);
                });
            });

            // Subject yuklash
            $('#subject').prop('disabled', false).append('<option value="">{{ __("Loading...") }}</option>');
            $.get("{{ route('admin.ajax.subjects.byLanguage', '') }}/" + langId, function(data) {
                $('#subject').empty().append('<option value="">{{ __("Select Subject") }}</option>');
                data.forEach(subject => {
                    let name = subject.translations[0]?.name ?? '{{ __("No name") }}';
                    $('#subject').append(`<option value="${subject.id}">${name}</option>`);
                });
            });
        });

        // ---------------- FACULTY -> GROUP ----------------
        $('#faculty').on('change', function() {
            let facultyId = $(this).val();

            $('#group').empty().append('<option value="">{{ __("Select Group") }}</option>').prop('disabled', true);

            if (!facultyId) return;

            $('#group').prop('disabled', false).append('<option value="">{{ __("Loading...") }}</option>');

            $.get("{{ url('admin/ajax/groups/by-faculty') }}/" + facultyId, function(data) {
                $('#group').empty().append('<option value="">{{ __("Select Group") }}</option>');
                data.forEach(group => {
                    $('#group').append(`<option value="${group.id}">${group.name}</option>`);
                });
            });
        });

        // ---------------- SUBJECT -> TOPIC ----------------
        $('#subject').on('change', function() {
            let subjectId = $(this).val();
            let langId = $('#language').val();

            $('#topic').empty().append('<option value="">{{ __("Select Topic") }}</option>').prop('disabled', true);

            if (!subjectId || !langId) return;

            $('#topic').prop('disabled', false).append('<option value="">{{ __("Loading...") }}</option>');

            $.get("{{ route('admin.ajax.topics.bySubjectAndLanguage') }}", {
                subject: subjectId,
                language: langId
            }, function(data) {
                $('#topic').empty().append('<option value="">{{ __("Select Topic") }}</option>');
                data.forEach(topic => {
                    let name = topic.translations[0]?.name ?? '{{ __("No name") }}';
                    $('#topic').append(`<option value="${topic.id}">${name}</option>`);
                });
            }).fail(function(xhr) {
                console.error("Error loading topics:", xhr.responseText);
                $('#topic').empty().append('<option value="">{{ __("Error loading topics") }}</option>');
            });
        });

        // Form submit
        $('#questionForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            let formData = new FormData(this);
            let submitBtn = $('#submitBtn');
            let progressContainer = $('#progressContainer');
            let progressBar = progressContainer.find('.progress-bar');

            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Uploading...") }}');
            progressContainer.removeClass('d-none');
            progressBar.css('width', '50%').text('50%');

            $.ajax({
                url: "{{ route('admin.questions.store') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    progressBar.css('width', '100%').text('100%').removeClass('progress-bar-animated');

                    setTimeout(function() {
                        showResultModal(response);
                        if (response.success && response.error_count === 0) {
                            $('#questionForm')[0].reset();
                            $('.js-select2').val(null).trigger('change');
                        }
                    }, 500);
                },
                error: function(xhr) {
                    progressBar.css('width', '100%').addClass('bg-danger').removeClass('progress-bar-animated');

                    // Log full error to console for debugging
                    console.error('Full error details:', xhr);
                    console.error('Response text:', xhr.responseText);
                    console.error('Status:', xhr.status);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        // Show validation errors
                        $.each(errors, function(field, messages) {
                            let input = $('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        });

                        // If there are question errors, show modal
                        if (xhr.responseJSON.error_count > 0) {
                            showResultModal(xhr.responseJSON);
                        }
                    } else {
                        // Show more detailed error
                        let errorMessage = '{{ __("Server error occurred") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage += ': ' + xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            errorMessage += '\n\nCheck browser console (F12) for details';
                        }
                        alert(errorMessage);
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('<i class="icofont icofont-save"></i> {{ __("Upload Questions") }}');
                    setTimeout(function() {
                        progressContainer.addClass('d-none');
                        progressBar.css('width', '0%').text('0%').removeClass('bg-danger').addClass('progress-bar-animated');
                    }, 2000);
                }
            });
        });

        // Show result modal
        function showResultModal(response) {
            let html = '';

            if (response.uploaded_count > 0) {
                html += `<div class="alert alert-success">
                <i class="icofont icofont-check-circled"></i> 
                <strong>${response.uploaded_count}</strong> {{ __('questions uploaded successfully') }}
            </div>`;
            }

            if (response.error_count > 0) {
                html += `<div class="alert alert-danger">
                <i class="icofont icofont-close-circled"></i> 
                <strong>${response.error_count}</strong> {{ __('questions with errors') }}
            </div>`;

                html += `<div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th width="100">{{ __('Question #') }}</th>
                            <th>{{ __('Error') }}</th>
                        </tr>
                    </thead>
                    <tbody>`;

                response.errors.forEach(function(error) {
                    html += `<tr>
                    <td class="text-center"><strong>${error.line}</strong></td>
                    <td>${error.message}</td>
                </tr>`;
                });

                html += `</tbody></table></div>`;
                html += `<p class="text-muted mt-3"><i class="icofont icofont-info-circle"></i> {{ __('Please fix the file and upload again.') }}</p>`;
            }

            $('#resultContent').html(html);
            $('#resultModal').modal('show');
        }
    });
</script>
@endpush

@endsection