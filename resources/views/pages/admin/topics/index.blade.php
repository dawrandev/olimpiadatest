@extends('layouts.admin.main')
@section('title', __('Topics'))
@section('content')
<x-admin.breadcrumb :title="__('All Topics')">
    <a href="{{ route('admin.topics.create') }}" class="btn btn-primary">
        <i class="icofont icofont-plus"></i>
        {{ __('Add Topic') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" action="{{ route('admin.topics.index') }}" class="d-flex gap-2">
                    <!-- Subject select (chap tarafda) -->
                    <select name="subject_id" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">{{ __('All Subjects') }}</option>
                        @foreach(getSubjects() as $subject)
                        <option value="{{ $subject->id }}"
                            {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ optional($subject->translations->firstWhere('language_id', currentLanguageId()))->name }}
                        </option>
                        @endforeach
                    </select>

                    @if(request('subject_id'))
                    <a href="{{ route('admin.topics.index') }}" class="btn btn-outline-secondary">
                        {{ __('Reset') }}
                    </a>
                    @endif
                </form>
            </div>

            <div class="col-md-6 d-flex justify-content-end">
                <input type="text" id="search" class="form-control w-50"
                    placeholder="{{ __('Search topics...') }}">
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <!-- Topics Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="topicsTable">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="topicsTableBody">
                                @include('partials.admin.topics.topics_table')
                            </tbody>

                        </table>
                    </div>
                    <br>

                    <!-- Pagination -->
                    @if($topics->hasPages())
                    <div class="row">
                        <div class="col-md-6">
                            <div class="dataTables_info">
                                {{ __('Showing') }} {{ $topics->firstItem() }} {{ __('to') }} {{ $topics->lastItem() }} {{ __('of') }} {{ $topics->total() }} {{ __('entries') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="dataTables_paginate paging_simple_numbers float-end">
                                {{ $topics->links() }}
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
<script>
    function bindDeleteEvents() {
        // Rebind confirm-action buttons for dynamically loaded content
        const topicsTableBody = document.getElementById('topicsTableBody');
        if (topicsTableBody) {
            topicsTableBody.querySelectorAll(".confirm-action").forEach((button) => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    const form = this.closest("form");
                    const actionType = this.dataset.action || "delete";
                    const customTitle = this.dataset.title;
                    const customText = this.dataset.text;

                    let title = customTitle || window.alertTranslations.areYouSure;
                    let text = customText || window.alertTranslations.cannotUndo;
                    let icon = "warning";
                    let confirmText = window.alertTranslations.yesConfirm;

                    if (actionType === "delete") {
                        icon = "error";
                        confirmText = window.alertTranslations.yesDelete;
                    } else if (actionType === "update") {
                        icon = "question";
                        confirmText = window.alertTranslations.yesUpdate;
                    } else if (actionType === "toggle") {
                        icon = "info";
                    }

                    Swal.fire({
                        title,
                        text,
                        icon,
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: confirmText,
                        cancelButtonText: window.alertTranslations.cancel,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        }
    }

    // Initial binding
    bindDeleteEvents();

    document.getElementById('search').addEventListener('keyup', function() {
        let search = this.value;
        let subjectId = document.querySelector('[name="subject_id"]').value;

        fetch("{{ route('admin.topics.index') }}?search=" + search + "&subject_id=" + subjectId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('topicsTableBody').innerHTML = data.html;
                bindDeleteEvents(); // Rebind after AJAX load
            });
    });
</script>
@endpush
@endsection