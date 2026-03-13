@extends('layouts.student.main')
@vite(['resources/css/student/home.css'])
@section('title', __('My Tests'))
@section('content')
<section class="section-space cuba-demo-section layout" id="layout">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 wow pulse">
                <div class="cuba-demo-content">
                    <div class="couting">
                        <div class="container-fluid">
                            @if($tests->isEmpty())
                            <div class="d-flex justify-content-center align-items-start" style="min-height: 100vh; padding-top: 120px;">
                                <div class="col-lg-6 col-md-8">
                                    <div class="card shadow-sm border-0 text-center p-5">
                                        <i data-feather="inbox" style="width: 64px; height: 64px;" class="text-muted mx-auto mb-3"></i>
                                        <h4 class="text-muted mb-3">{{ __('No tests available') }}</h4>
                                        <p class="text-muted">{{ __('Your teacher has not assigned any tests yet') }}</p>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="row justify-content-center">
                                @foreach($tests as $test)
                                <div class="col-lg-6 col-md-10 mb-4">
                                    <div class="card shadow-lg border-0 cke_tpl_item_main">
                                        <!-- Card Header -->
                                        <div class="card-header bg-primary text-white p-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4 class="mb-1 fw-bold">
                                                        {{ optional($test->subject->translations->firstWhere('language_id', currentLanguageId()))->name ?? 'N/A' }}
                                                    </h4>
                                                    <small>{{ $test->group->name }}</small>
                                                </div>
                                                <span class="badge bg-light text-primary fs-6 px-3 py-2">
                                                    @php
                                                    $language = getLanguages()->firstWhere('id', $test->language_id);
                                                    @endphp
                                                    {{ $language ? $language->name : 'Noma’lum til' }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Card Body -->
                                        <div class="card-body p-5">
                                            <!-- Test statistikasi -->
                                            <div class="row mb-4">
                                                <div class="col-4 text-center">
                                                    <div class="cke_tpl_stat">
                                                        <i data-feather="help-circle" style="width: 32px; height: 32px;" class="text-primary mb-2"></i>
                                                        <div class="fw-bold fs-4">{{ $test->question_count }}</div>
                                                        <div class="text-muted small">{{ __('Questions') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <div class="cke_tpl_stat">
                                                        <i data-feather="clock" style="width: 32px; height: 32px;" class="text-success mb-2"></i>
                                                        <div class="fw-bold fs-4">{{ $test->duration }}</div>
                                                        <div class="text-muted small">{{ __('Minutes') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <div class="cke_tpl_stat">
                                                        <i data-feather="calendar" style="width: 32px; height: 32px;" class="text-info mb-2"></i>
                                                        <div class="fw-bold fs-4">{{ $test->end_time->format('H:i') }}</div>
                                                        <div class="text-muted small">{{ __('Until') }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vaqt oralig'i -->
                                            <div class="alert alert-info mb-4">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <small>
                                                            <strong>{{ __('Available time') }}:</strong>
                                                            {{ $test->start_time->format('d.m.Y H:i') }} - {{ $test->end_time->format('d.m.Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($test->description)
                                            <!-- Tavsif -->
                                            <div class="alert alert-warning mb-4">
                                                <small><strong>{{ __('Note') }}:</strong> {{ $test->description }}</small>
                                            </div>
                                            @endif

                                            <!-- Status va tugma -->
                                            <div class="text-center">
                                                @if($test->student_status == 'completed')
                                                <div class="alert alert-success mb-3">
                                                    <strong>{{ __('Completed') }}</strong>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <span class="badge bg-success fs-5 px-4 py-2">
                                                            {{ __('Score') }}: {{ $test->test_result->score }}%
                                                        </span>
                                                        <span class="ms-3 text-muted">
                                                            {{ $test->test_result->correct_answers }}/{{ $test->test_result->total_questions }} {{ __('correct') }}
                                                        </span>
                                                    </div>
                                                </div>

                                                @elseif($test->student_status == 'in_progress')
                                                <div class="alert alert-warning mb-3">
                                                    <i data-feather="alert-circle" style="width: 20px; height: 20px;"></i>
                                                    <strong>{{ __('Test in progress') }}</strong>
                                                </div>
                                                <a href="{{ route('student.test.take', $test->id) }}" class="btn btn-warning btn-lg px-5 py-3">
                                                    <i data-feather="play" style="width: 20px; height: 20px;" class="me-2"></i>
                                                    {{ __('Continue Test') }}
                                                </a>
                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <i data-feather="clock" style="width: 14px; height: 14px;"></i>
                                                        {{ __('Started') }}: {{ $test->test_result->started_at->format('d.m.Y H:i') }}
                                                    </small>
                                                </div>

                                                @else
                                                <button class="btn btn-primary btn-lg px-5 py-3 cke_tpl_start_btn"
                                                    onclick="startTest({{ $test->id }})"
                                                    data-test-id="{{ $test->id }}">
                                                    <i class="icofont icofont-play-alt-1"></i>
                                                    {{ __('Start Test') }}
                                                </button>
                                                <div class="mt-3">
                                                    <small class="text-danger">
                                                        <i class="icofont icofont-warning-alt"></i>
                                                        {{ __('Once started, the test cannot be paused') }}
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="row justify-content-center mt-5">
                                <div class="col-lg-8 col-md-10">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-4 text-center">
                                            <h6 class="text-primary mb-3">
                                                <i class="icofont icofont-info-circle"></i>
                                                {{ __('Important Tips') }}
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-3 mb-2">
                                                    <small class="text-muted">
                                                        <i data-feather="lightbulb" class="text-warning me-1" style="width: 14px; height: 14px;"></i>
                                                        {{ __('Read questions carefully') }}
                                                    </small>
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <small class="text-muted">
                                                        <i data-feather="clock" class="text-info me-1" style="width: 14px; height: 14px;"></i>
                                                        {{ __('Manage your time') }}
                                                    </small>
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <small class="text-muted">
                                                        <i data-feather="x-circle" class="text-danger me-1" style="width: 14px; height: 14px;"></i>
                                                        {{ __('Do not close browser') }}
                                                    </small>
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <small class="text-muted">
                                                        <i data-feather="wifi-off" class="text-secondary me-1" style="width: 14px; height: 14px;"></i>
                                                        {{ __('Check internet connection') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function startTest(testId) {
        const button = document.querySelector(`[data-test-id="${testId}"]`);
        const url = "{{ route('student.test.start', ':testId') }}".replace(':testId', testId);

        Swal.fire({
                title: window.alertTranslations?.areYouSure || '{{ __("Are you sure?") }}',
                text: @json(__('Once started, the test cannot be paused or restarted')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __("Yes, start test") }}',
                cancelButtonText: window.alertTranslations?.cancel || '{{ __("Cancel") }}'
            })
            .then((result) => {
                if (result.isConfirmed) {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Starting...") }}';

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = data.redirect_url;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __("Error") }}',
                                    text: data.message || '{{ __("An error occurred") }}'
                                });
                                button.disabled = false;
                                button.innerHTML = '{{ __("Start Test") }}';
                            }
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __("Error") }}',
                                text: '{{ __("An error occurred") }}'
                            });
                            button.disabled = false;
                            button.innerHTML = '{{ __("Start Test") }}';
                        });
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
@endsection