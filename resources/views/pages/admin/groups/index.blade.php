@extends('layouts.admin.main')
@section('title', __('Groups'))
@section('content')
<x-admin.breadcrumb :title="__('All Groups')">
    <a href="{{ route('admin.groups.create') }}" class="btn btn-primary">
        <i class="icofont icofont-plus"></i>
        {{ __('Add Group') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="GET" action="{{ route('admin.groups.index') }}">
                    <div class="input-group">
                        <select name="faculty_id" class="form-select" onchange="this.form.submit()">
                            <option value="">{{ __('All Faculties') }}</option>
                            @foreach(getFaculties() as $faculty)
                            <option value="{{ $faculty->id }}"
                                {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                {{ optional($faculty->translations->first())->name }}
                            </option>
                            @endforeach
                        </select>
                        @if(request('faculty_id'))
                        <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary">
                            {{ __('Reset') }}
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <!-- Groups Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="groupsTable">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Faculty') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $group)
                                <tr>
                                    <td>{{ $groups->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="f-w-500">{{ $group->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ optional($group->faculty->translations->firstWhere('language_id', currentLanguageId()))->name }}
                                        </span>
                                    </td>
                                    <td class="text align-middle">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-outline-warning px-2 py-1">
                                                <i class="icon-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.groups.destroy', $group) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger px-2 py-1 confirm-action" data-action="delete" title="{{ __('Delete') }}">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="icofont icofont-search"></i> {{ __('No groups found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <br>

                    @if($groups->hasPages())
                    <div class="row">
                        <div class="col-md-6">
                            <div class="dataTables_info">
                                {{ __('Showing') }} {{ $groups->firstItem() }} {{ __('to') }} {{ $groups->lastItem() }} {{ __('of') }} {{ $groups->total() }} {{ __('entries') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="dataTables_paginate paging_simple_numbers float-end">
                                {{ $groups->links() }}
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