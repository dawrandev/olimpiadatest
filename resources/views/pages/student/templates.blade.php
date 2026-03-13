@extends('layouts.user.main')

@section('content')
<section class="section-space cuba-demo-section layout" id="layout">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 wow pulse">
                <div class="cuba-demo-content">
                    <div class="couting">
                        <div class="container-fluid">
                            <!-- Sarlavha -->
                            <div class="row mb-4">
                                <div class="col-12 text-center">
                                    <h2 class="mb-3">Test Shablonlari</h2>
                                    <p class="text-muted">O'zingizga mos test shablonini tanlang va bilimlaringizni sinab ko'ring</p>
                                </div>
                            </div>

                            <!-- Til tanlash card -->
                            <div class="row justify-content-center mb-4">
                                <div class="col-lg-6 col-md-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="language" id="kaa" checked>
                                                <label class="btn btn-outline-primary" for="kaa">
                                                    Qaraqalpaqsha
                                                </label>

                                                <input type="radio" class="btn-check" name="language" id="uz">
                                                <label class="btn btn-outline-primary" for="uz">
                                                    O'zbekcha
                                                </label>

                                                <input type="radio" class="btn-check" name="language" id="ru">
                                                <label class="btn btn-outline-primary" for="ru">
                                                    Русский
                                                </label>

                                                <input type="radio" class="btn-check" name="language" id="uzb">
                                                <label class="btn btn-outline-primary" for="uzb">
                                                    Ўзбекча
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Shablonlar qismi -->
                            <div class="row">
                                @forelse($templates as $template)
                                <div class="col-xl-4 col-sm-6">
                                    <div class="card">
                                        <div class="card-header pb-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">{{ $template->name ?? 'Shablon ' . $template->id }}</h5>
                                                <span class="badge badge-light-primary">
                                                    {{ strtoupper($template->language ?? 'KAA') }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center mt-2">
                                                <i data-feather="file-text" class="me-2"></i>
                                                <span class="text-muted">{{ $template->questions_count ?? 0 }} ta savol</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-6 text-start">
                                                    <span class="text-muted">Savol soni:</span>
                                                    <div class="fw-bold">{{ $template->questions_count ?? 0 }} ta</div>
                                                </div>
                                                <div class="col-6 text-start">
                                                    <span class="text-muted">Til:</span>
                                                    <div class="fw-bold">
                                                        @switch($template->language ?? 'kaa')
                                                        @case('kaa')
                                                        Qaraqalpaqsha
                                                        @break
                                                        @case('uz')
                                                        O'zbekcha
                                                        @break
                                                        @case('ru')
                                                        Русский
                                                        @break
                                                        @case('uzb')
                                                        Ўзбекча
                                                        @break
                                                        @default
                                                        Qaraqalpaqsha
                                                        @endswitch
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-grid">
                                                @if($template->questions_count > 0)
                                                <a href="{{ route('user.templates.start', $template->id) }}" class="btn btn-primary">
                                                    <i class="icofont icofont-ui-play me-2"></i>
                                                    Testni Boshlash
                                                </a>
                                                @else
                                                <button class="btn btn-secondary" disabled>
                                                    <i class="icofont icofont-ban me-2"></i>
                                                    Savollar mavjud emas
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body text-center py-5">
                                            <i data-feather="inbox" class="mb-3" style="width: 48px; height: 48px; color: #6c757d;"></i>
                                            <h5 class="text-muted">Hali hech qanday shablon mavjud emas</h5>
                                            <p class="text-muted">Tez orada test shablonlari qo'shiladi</p>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection