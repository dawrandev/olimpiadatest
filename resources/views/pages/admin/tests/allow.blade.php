@extends('layouts.admin.main')
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
@endpush
@section('title', __('Assign Test to Students'))
@section('content')

<x-admin.breadcrumb :title="__('Assign Test to Students')">
    <a href="#" class="btn btn-outline-primary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Assignments') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ __('Create New Test Assignment') }}</h5>
                </div>
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Language -->
                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label fw-bold">
                                    {{ __('Language') }} <span class="text-danger">*</span>
                                </label>
                                <select name="language_id" id="language" class="form-select js-select2">
                                    <option value="">{{ __('Select Language') }}</option>
                                    @foreach(getLanguages() as $lang)
                                    <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Faculty -->
                            <div class="col-md-6 mb-3">
                                <label for="faculty" class="form-label fw-bold">
                                    {{ __('Faculty') }} <span class="text-danger">*</span>
                                </label>
                                <select name="faculty_id" id="faculty" class="form-select js-select2" disabled>
                                    <option value="">{{ __('Select Faculty') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Group -->
                            <div class="col-md-6 mb-3">
                                <label for="group" class="form-label fw-bold">
                                    {{ __('Group') }} <span class="text-danger">*</span>
                                </label>
                                <select name="group_id" id="group" class="form-select js-select2" disabled>
                                    <option value="">{{ __('Select Group') }}</option>
                                </select>
                            </div>

                            <!-- Subject -->
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label fw-bold">
                                    {{ __('Subject') }} <span class="text-danger">*</span>
                                </label>
                                <select name="subject_id" id="subject" class="form-select js-select2" disabled>
                                    <option value="">{{ __('Select Subject') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Question Count -->
                            <div class="col-md-6 mb-3">
                                <label for="question_count" class="form-label fw-bold">
                                    {{ __('Number of Questions') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="question_count" id="question_count"
                                    class="form-control" value="20" min="5" max="100" required>
                                @error('question_count')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Мавжуд саволлар: 150 та</small>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label fw-bold">
                                    {{ __('Duration (minutes)') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="duration" id="duration"
                                    class="form-control" value="30" min="5" max="180" required>
                                @error('duration')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Time -->
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label fw-bold">
                                    {{ __('Start Time') }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="start_time" id="start_time"
                                    class="form-control" value="2025-09-30T09:00" required>
                                @error('start_time')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Time -->
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label fw-bold">
                                    {{ __('End Time') }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="end_time" id="end_time"
                                    class="form-control" value="2025-09-30T17:00" required>
                                @error('end_time')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="icofont icofont-info-circle"></i>
                                    Студентлар бу вақт ораида тест топшириш имкониятига эга бўладилар
                                </small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">
                                {{ __('Description') }} <span class="text-muted">({{ __('Optional') }})</span>
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="3"
                                placeholder="{{ __('Enter additional instructions or notes...') }}">Диққат! Тест топшириш вақтида браузерни ёпманг ва бошқа саҳифаларга ўтманг. Тест фақат 1 марта топширилади.</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                    id="is_active" value="1" checked>
                                <label class="form-check-label fw-bold" for="is_active">
                                    {{ __('Activate immediately') }}
                                </label>
                            </div>
                            <small class="text-muted ms-4">
                                Фаоллаштирилса, студентлар дарҳол тестга кириш имкониятига эга бўладилар
                            </small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-end pt-3 border-top">
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="icofont icofont-refresh"></i>
                                {{ __('Reset') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icofont icofont-check"></i>
                                {{ __('Create Assignment') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const $language = $('#language');
        const $faculty = $('#faculty');
        const $group = $('#group');
        const $subject = $('#subject');

        if ($.fn.select2) {
            $('.js-select2').select2({
                width: '100%'
            });
        } else {
            console.warn('Select2 topilmadi — select2 skriptini yuklang.');
        }

        const urls = {
            faculties: "{{ route('admin.ajax.faculties.byLanguage', '__LANG__') }}",
            groups: "{{ route('admin.ajax.groups.byFaculty', '__FACULTY__') }}",
            subjects: "{{ route('admin.ajax.subjects.byLanguage', '__LANG__') }}"
        };

        function reset($el, placeholder, disable = true) {
            $el.empty().append($('<option>', {
                value: '',
                text: placeholder
            }));
            $el.prop('disabled', disable);
            if ($.fn.select2) $el.val('').trigger('change.select2');
        }

        reset($faculty, "{{ __('Select Faculty') }}", true);
        reset($group, "{{ __('Select Group') }}", true);
        reset($subject, "{{ __('Select Subject') }}", true);

        // LANGUAGE -> Faculties & Subjects
        $language.on('change', function() {
            const langId = $(this).val();

            reset($faculty, "{{ __('Loading...') }}", true);
            reset($group, "{{ __('Select Group') }}", true);
            reset($subject, "{{ __('Loading...') }}", true);

            if (!langId) return;

            // Faculties
            const facultiesUrl = urls.faculties.replace('__LANG__', langId);
            $.get(facultiesUrl)
                .done(function(data) {
                    reset($faculty, "{{ __('Select Faculty') }}", false);
                    if (Array.isArray(data) && data.length) {
                        data.forEach(f => {
                            $faculty.append($('<option>', {
                                value: f.id,
                                text: f.name || 'No name'
                            }));
                        });
                    }
                    if ($.fn.select2) $faculty.trigger('change.select2');
                })
                .fail(function(xhr) {
                    console.error('faculties load error', xhr);
                    reset($faculty, "{{ __('Select Faculty') }}", true);
                });

            // Subjects
            const subjectsUrl = urls.subjects.replace('__LANG__', langId);
            $.get(subjectsUrl)
                .done(function(data) {
                    reset($subject, "{{ __('Select Subject') }}", false);
                    if (Array.isArray(data) && data.length) {
                        data.forEach(s => {
                            let name = (s.translations && s.translations.length > 0) ?
                                s.translations[0].name :
                                '';
                            $subject.append($('<option>', {
                                value: s.id,
                                text: name
                            }));
                        });
                    }
                    if ($.fn.select2) $subject.trigger('change.select2');
                })
                .fail(function(xhr) {
                    console.error('subjects load error', xhr);
                    reset($subject, "{{ __('Select Subject') }}", true);
                });
        });

        // FACULTY -> Groups
        $faculty.on('change', function() {
            const facultyId = $(this).val();

            reset($group, "{{ __('Loading...') }}", true);

            if (!facultyId) return;

            const groupsUrl = urls.groups.replace('__FACULTY__', facultyId);
            $.get(groupsUrl)
                .done(function(data) {
                    reset($group, "{{ __('Select Group') }}", false);
                    if (Array.isArray(data) && data.length) {
                        data.forEach(g => {
                            $group.append($('<option>', {
                                value: g.id,
                                text: g.name
                            }));
                        });
                    }
                    if ($.fn.select2) $group.trigger('change.select2');
                })
                .fail(function(xhr) {
                    console.error('groups load error', xhr);
                    reset($group, "{{ __('Select Group') }}", true);
                });
        });
    });
</script>
@endpush

@endsection