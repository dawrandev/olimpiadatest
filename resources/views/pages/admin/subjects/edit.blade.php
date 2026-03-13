@extends('layouts.admin.main')

@section('title', __('Edit Subject'))

@section('content')
<x-admin.breadcrumb :title="__('Edit Subject')">
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm">
                <div class="card-body">

                    <!-- Edit Subject Form -->
                    <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            @foreach(getLanguages() as $language)
                            <div class="col-md-4 mb-3">
                                <label for="name_{{ $language->code }}" class="form-label fw-bold">
                                    <i class="icofont icofont-book-alt me-2"></i>
                                    {{ __('Subject Name') }} ({{ $language->name }})
                                </label>
                                <input type="text"
                                    name="translations[{{ $language->id }}][name]"
                                    id="name_{{ $language->code }}"
                                    value="{{ old("translations.{$language->id}.name", optional($subject->translations->firstWhere('language_id', $language->id))->name) }}"
                                    class="form-control @error(" translations.{$language->id}.name") is-invalid @enderror"
                                placeholder="{{ __('Enter subject name in :lang', ['lang' => $language->name]) }}">
                                @error("translations.{$language->id}.name")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                <i class="icofont icofont-arrow-left"></i> {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icofont icofont-save"></i> {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                    <!-- End Form -->

                </div>
            </div>
        </div>
    </div>
</div>
@endsection