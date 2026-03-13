@extends('layouts.admin.main')
@section('title', __('Students'))
@section('content')
<x-admin.breadcrumb :title="__('All Students')">
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        <i class="icofont icofont-plus"></i>
        {{ __('Add Student') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.students.index') }}" class="row g-2 mb-3">
                        {{-- Search --}}
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control"
                                placeholder="{{ __('Search') }}"
                                value="{{ request('search') }}">
                        </div>

                        {{-- Faculty filter --}}
                        <div class="col-md-3">
                            <select name="faculty_id" class="form-select">
                                <option value="">{{ __('All Faculties') }}</option>
                                @foreach(getFaculties() as $faculty)
                                <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                    {{ optional($faculty->translations->firstWhere('language_id', currentLanguageId()))->name ?? $faculty->id }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Group filter --}}
                        <div class="col-md-3">
                            <select name="group_id" class="form-select">
                                <option value="">{{ __('All Groups') }}</option>
                                @foreach(getGroups() as $group)
                                <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Per page --}}
                        <div class="col-md-2">
                            <select name="per_page" class="form-select">
                                @foreach([10,25,50,100] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="icofont icofont-filter"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Students Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="studentsTable">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>{{ __('Full Name') }}</th>
                                    <th>{{ __('Group') }}</th>
                                    <th>{{ __('Faculty') }}</th>
                                    <th>{{ __('Registered') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td>{{ $students->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="f-w-500">{{ $student->full_name }}</span>
                                    </td>
                                    <td>
                                        <span class="info">
                                            {{ $student->group->name ?? __('Not specified') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="success">
                                            {{ optional($student->group->faculty->translations->firstWhere('language_id', currentLanguageId()))->name ?? __('Not specified') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span>{{ $student->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text align-middle">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-info px-2 py-1">
                                                <i class="icon-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-warning px-2 py-1">
                                                <i class="icon-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger px-2 py-1 confirm-action" data-action="delete">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="icofont icofont-search"></i> {{ __('No students found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <br>

                    <!-- Pagination -->
                    @if($students->hasPages())
                    <div class="row">
                        <div class="col-md-6">
                            <div class="dataTables_info">
                                {{ __('Showing') }} {{ $students->firstItem() }} {{ __('to') }} {{ $students->lastItem() }} {{ __('of') }} {{ $students->total() }} {{ __('entries') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="dataTables_paginate paging_simple_numbers float-end">
                                {{ $students->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@endpush

@endsection