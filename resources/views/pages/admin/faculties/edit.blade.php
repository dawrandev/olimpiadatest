@extends('layouts.admin.main')

@section('title', __('Edit Faculty'))

@section('content')
<x-admin.breadcrumb :title="__('Edit Faculty')">
    <a href="{{ route('admin.faculties.index') }}" class="btn btn-secondary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <!-- Edit Faculty Form -->
                    <form action="{{ route('admin.faculties.update', $faculty) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            @foreach(getLanguages() as $language)
                            <div class="col-md-4 mb-3">
                                <label for="name_{{ $language->code }}" class="form-label">
                                    {{ __('Name') }} ({{ $language->name }})
                                </label>
                                <input type="text"
                                    name="translations[{{ $language->id }}][name]"
                                    id="name_{{ $language->code }}"
                                    value="{{ old("translations.{$language->id}.name", optional($faculty->translations->firstWhere('language_id', $language->id))->name) }}"
                                    class="form-control @error(" translations.{$language->id}.name") is-invalid @enderror"
                                placeholder="{{ __('Enter faculty name') }}">
                                @error("translations.{$language->id}.name")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
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