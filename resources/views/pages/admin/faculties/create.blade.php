@extends('layouts.admin.main')

@section('title', __('Create Faculty'))

@section('content')
<x-admin.breadcrumb :title="__('Create Faculty')">
    <a href="{{ route('admin.faculties.index') }}" class="btn btn-primary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Faculties') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="icofont icofont-plus me-2"></i>
                        {{ __('Create New Faculty') }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.faculties.store') }}" method="POST">
                        @csrf
                        @foreach(getLanguages() as $language)
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="icofont icofont-book-alt me-2"></i>
                                {{ __('Faculty Name') }} ({{ $language->name }})
                            </label>
                            <input
                                type="text"
                                name="name[{{ $language->id }}]"
                                class="form-control @error('name.'.$language->id) is-invalid @enderror"
                                placeholder="{{ __('Enter faculty name in :lang', ['lang' => $language->name]) }}"
                                required>
                            @error('name.'.$language->id)
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endforeach

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                <i class="icofont icofont-arrow-left"></i> {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icofont icofont-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection