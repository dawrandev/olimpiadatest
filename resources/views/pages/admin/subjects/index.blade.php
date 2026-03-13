@extends('layouts.admin.main')
@section('title', __('Subjects'))
@section('content')
<x-admin.breadcrumb :title="__('All Subjects')">
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
        <i class="icofont icofont-plus"></i>
        {{ __('Add Subject') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <!-- Search & Per Page -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="{{ __('Search...') }}">
                        </div>
                        <div class="col-md-3">
                            <select id="perPage" class="form-select">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Subjects Table -->
                    <div class="table-responsive" id="subjectsTableWrapper">
                        @include('partials.admin.subjects.subjects_table', ['subjects' => $subjects])
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const searchInput = document.getElementById('searchInput');
    const perPageSelect = document.getElementById('perPage');
    const wrapper = document.getElementById('subjectsTableWrapper');
    let page = 1;

    function fetchSubjects() {
        const query = searchInput.value;
        const perPage = perPageSelect.value;

        fetch(`{{ route('admin.subjects.index') }}?page=${page}&per_page=${perPage}&search=${query}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                wrapper.innerHTML = html;
                bindDeleteEvents(); // qayta delete btn ishlashi uchun
                bindPaginationLinks(); // pagination linklarini qayta bog‘lash
            });
    }

    searchInput.addEventListener('keyup', function() {
        page = 1;
        fetchSubjects();
    });

    perPageSelect.addEventListener('change', function() {
        page = 1;
        fetchSubjects();
    });

    function bindPaginationLinks() {
        wrapper.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                page = this.getAttribute('href').split('page=')[1];
                fetchSubjects();
            });
        });
    }

    function bindDeleteEvents() {
        wrapper.querySelectorAll(".delete-btn").forEach((button) => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                let form = this.closest("form");

                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('This action cannot be undone!') }}",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    }

    // Initial run
    bindPaginationLinks();
    bindDeleteEvents();
</script>
@endpush
@endsection