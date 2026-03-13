@extends('layouts.admin.main')
@section('title', __('Edit Student'))
@section('content')

<x-admin.breadcrumb :title="__('Edit Student')">
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary">
        <i class="icofont icofont-arrow-left"></i>
        {{ __('Back to Students') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ __('Edit Student') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.update', $student) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Faculty Select -->
                            <div class="col-md-6 mb-3">
                                <label for="faculty" class="form-label fw-bold">{{ __('Faculty') }} <span class="text-danger">*</span></label>
                                <select name="faculty_id" id="faculty" class="form-select" required>
                                    <option value="">{{ __('Select Faculty') }}</option>
                                    @foreach(getFaculties() as $faculty)
                                    <option value="{{ $faculty->id }}" {{ (old('faculty_id', $student->group->faculty_id ?? '') == $faculty->id) ? 'selected' : '' }}>
                                        {{ optional($faculty->translations->firstWhere('language_id', currentLanguageId()))->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('faculty_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Group Select -->
                            <div class="col-md-6 mb-3">
                                <label for="group" class="form-label fw-bold">{{ __('Group') }} <span class="text-danger">*</span></label>
                                <select name="group_id" id="group" class="form-select" required disabled>
                                    <option value="">{{ __('First select faculty') }}</option>
                                    @if(old('faculty_id', $student->group->faculty_id ?? ''))
                                    @foreach(getGroups() as $group)
                                    @if($group->faculty_id == old('faculty_id', $student->group->faculty_id ?? ''))
                                    <option value="{{ $group->id }}" {{ (old('group_id', $student->group_id) == $group->id) ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endif
                                    @endforeach
                                    @endif
                                </select>
                                @error('group_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="full_name" class="form-control" value="{{ old('full_name', $student->full_name) }}" required placeholder="{{ __('Enter full name') }}">
                            @error('full_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Login -->
                            <div class="col-md-6 mb-3">
                                <label for="login" class="form-label fw-bold">{{ __('Login') }} <span class="text-danger">*</span></label>
                                <input type="text" name="login" id="login" class="form-control" value="{{ old('login', $student->user->login) }}" placeholder="{{ __('Enter login') }}">
                                @error('login')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-bold">{{ __('Password') }}</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('Enter password') }}">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="icofont icofont-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password Confirmation -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label fw-bold">{{ __('Confirm Password') }}</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="{{ __('Confirm password') }}">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                        <i class="icofont icofont-eye" id="eyeIconConfirm"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="text-end pt-3 border-top">
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="icofont icofont-refresh"></i>
                                {{ __('Reset') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icofont icofont-check"></i>
                                {{ __('Update Student') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const facultySelect = document.getElementById('faculty');
        const groupSelect = document.getElementById('group');
        const togglePassword = document.getElementById('togglePassword');
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeIconConfirm = document.getElementById('eyeIconConfirm');

        facultySelect.addEventListener('change', function() {
            const facultyId = this.value;

            groupSelect.innerHTML = '<option value="">{{ __("Loading...") }}</option>';
            groupSelect.disabled = true;

            if (facultyId) {
                const allGroups = @json(getGroups());
                const filteredGroups = allGroups.filter(group => group.faculty_id == facultyId);

                groupSelect.innerHTML = '<option value="">{{ __("Select Group") }}</option>';

                if (filteredGroups.length > 0) {
                    filteredGroups.forEach(group => {
                        const option = document.createElement('option');
                        option.value = group.id;
                        option.textContent = group.name;

                        if ('{{ old("group_id", $student->group_id) }}' == group.id) {
                            option.selected = true;
                        }

                        groupSelect.appendChild(option);
                    });
                    groupSelect.disabled = false;
                } else {
                    groupSelect.innerHTML = '<option value="">{{ __("No groups available") }}</option>';
                }
            } else {
                groupSelect.innerHTML = '<option value="">{{ __("First select faculty") }}</option>';
                groupSelect.disabled = true;
            }
        });

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            if (type === 'text') {
                eyeIcon.className = 'icofont icofont-eye-blocked';
            } else {
                eyeIcon.className = 'icofont icofont-eye';
            }
        });

        togglePasswordConfirm.addEventListener('click', function() {
            const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirm.setAttribute('type', type);

            if (type === 'text') {
                eyeIconConfirm.className = 'icofont icofont-eye-blocked';
            } else {
                eyeIconConfirm.className = 'icofont icofont-eye';
            }
        });

        passwordConfirm.addEventListener('input', function() {
            if (password.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('{{ __("Passwords do not match") }}');
            } else {
                passwordConfirm.setCustomValidity('');
            }
        });

        password.addEventListener('input', function() {
            if (passwordConfirm.value && password.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('{{ __("Passwords do not match") }}');
            } else {
                passwordConfirm.setCustomValidity('');
            }
        });

        if (facultySelect.value) {
            facultySelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush

@endsection