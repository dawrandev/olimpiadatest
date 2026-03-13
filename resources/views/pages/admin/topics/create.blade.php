@extends('layouts.admin.main')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
@endpush

@section('title', __('Create Topic'))

@section('content')
<x-admin.breadcrumb :title="__('Create Topic')">
    <a href="{{ route('admin.topics.index') }}" class="btn btn-primary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Topics') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="icofont icofont-plus"></i> {{ __('Create New Topic') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.topics.store') }}" method="POST">
                        @csrf

                        <!-- Subject select -->
                        <div class="mb-3">
                            <label for="subject_id" class="form-label fw-bold">
                                {{ __('Subject') }} <span class="text-danger">*</span>
                            </label>
                            <select name="subject_id" id="subject_id"
                                class="js-example-placeholder-multiple col-sm-12 @error('subject_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select Subject') }}">
                                <option value=""></option>
                                @foreach(getSubjects() as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->translations->first()->name ?? $subject->name }}
                                </option>
                                @endforeach
                            </select>

                            @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Translations -->
                        <div class="row">
                            @foreach(getLanguages() as $language)
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="icofont icofont-book-alt"></i>
                                    {{ __('Topic Name') }} ({{ $language->name }})
                                </label>
                                <input
                                    type="text"
                                    name="translations[{{ $language->id }}][name]"
                                    class="form-control @error('translations.'.$language->id.'.name') is-invalid @enderror"
                                    value="{{ old('translations.'.$language->id.'.name') }}"
                                    placeholder="{{ __('Enter topic name in :lang', ['lang' => $language->name]) }}"
                                    required>
                                @error('translations.'.$language->id.'.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endforeach
                        </div>

                        <!-- Buttons -->
                        <div class="text-end mt-4">
                            <a href="{{ route('admin.topics.index') }}" class="btn btn-light me-2">
                                <i class="icofont icofont-close"></i> {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icofont icofont-save"></i> {{ __('Save Topic') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
@endpush

@endsection