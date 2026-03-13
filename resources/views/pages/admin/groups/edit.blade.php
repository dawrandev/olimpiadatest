@extends('layouts.admin.main')

@section('title', __('Edit Group'))

@section('content')
<x-admin.breadcrumb :title="__('Edit Group')">
    <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-lg-12 ">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.groups.update', $group->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="faculty_id" class="form-label">{{ __('Faculty') }}</label>
                            <select name="faculty_id" id="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror">
                                <option value="">{{ __('Select faculty') }}</option>
                                @foreach(getFaculties() as $faculty)
                                <option value="{{ $faculty->id }}"
                                    {{ old('faculty_id', $group->faculty_id) == $faculty->id ? 'selected' : '' }}>
                                    {{ optional($faculty->translations->firstWhere('language_id', currentLanguageId()))->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('faculty_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Group Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Group Name') }}</label>
                            <input type="text" name="name" id="name"
                                value="{{ old('name', $group->name) }}"
                                class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
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
                                    {{ __('Update Group') }}
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