@extends('layouts.admin.main')
@section('title', __('Add Group'))
@section('content')
<x-admin.breadcrumb :title="__('Add Group')">
    <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Groups') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-dark">{{ __('Group Information') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.groups.store') }}" method="POST" id="groupForm">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="faculty_id" class="form-label">{{ __('Faculty') }} <span class="text-danger">*</span></label>
                            <select name="faculty_id" id="faculty_id" class="form-control @error('faculty_id') is-invalid @enderror">
                                <option value="">{{ __('Select Faculty') }}</option>
                                @foreach(getFaculties() as $faculty)
                                <option value="{{ $faculty->id }}" {{ old('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                    {{ optional($faculty->translations->firstWhere('language_id', currentLanguageId()))->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('faculty_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="name" class="form-label">{{ __('Group Name') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="{{ __('Enter group name') }}">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('admin.groups.index') }}" class="btn btn-light me-2">
                                    <i class="icofont icofont-close"></i>
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="icofont icofont-save"></i>
                                    {{ __('Save Group') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection