@extends('layouts.admin.main')

@section('title', __('Dashboard'))

@push('css')
@vite(['resources/css/admin/dashboard.css'])
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ __('Dashboard') }}</h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Faculties Count -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Faculties') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $faculties_count ?? 0 }}
                            </div>
                            <div class="text-xs text-muted mt-1">{{ __('Total faculties') }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-primary text-white rounded-circle">
                                <i data-feather="briefcase" style="width: 20px; height: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Groups Count -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Groups') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $groups_count ?? 0 }}
                            </div>
                            <div class="text-xs text-muted mt-1">{{ __('Total groups') }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-success text-white rounded-circle">
                                <i data-feather="users" style="width: 20px; height: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Count -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('Students') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $students_count ?? 0 }}
                            </div>
                            <div class="text-xs text-muted mt-1">{{ __('Total students') }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-info text-white rounded-circle">
                                <i data-feather="user" style="width: 20px; height: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects Count -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Subjects') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $subjects_count ?? 0 }}
                            </div>
                            <div class="text-xs text-muted mt-1">{{ __('Total subjects') }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-warning text-white rounded-circle">
                                <i data-feather="book" style="width: 20px; height: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 pb-3">
                    <h5 class="mb-0 font-weight-bold">{{ __('Groups by Faculty') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Number of groups in each faculty') }}</p>
                </div>
                <div class="card-body">
                    <div id="facultyGroupsChart"></div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Faculties bo'yicha studentlar -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 pb-3">
                    <h5 class="mb-0 font-weight-bold">{{ __('Students by Faculty') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Number of students in each faculty') }}</p>
                </div>
                <div class="card-body">
                    <div id="facultyStudentsChart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chart 3: Guruhlar bo'yicha studentlar -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 pb-3">
                    <h5 class="mb-0 font-weight-bold">{{ __('Students by Group') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Number of students in each group') }}</p>
                </div>
                <div class="card-body">
                    <div id="groupStudentsChart"></div>
                </div>
            </div>
        </div>

        <!-- Chart 4: Fanlar bo'yicha savollar -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 pb-3">
                    <h5 class="mb-0 font-weight-bold">{{ __('Questions by Subject') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Top 10 subjects with most questions') }}</p>
                </div>
                <div class="card-body">
                    <div id="subjectQuestionsChart"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- latest jquery-->
<script src="{{asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
<script src="{{asset('assets/js/chart/apex-chart/stock-prices.js') }}"></script>
<script src="{{asset('assets/js/chart/apex-chart/chart-custom.js') }}"></script>

<!-- Pass data to JavaScript -->
<script>
    window.dashboardData = {
        facultiesWithGroups: @json($facultiesWithGroups ?? []),
        facultiesWithStudents: @json($facultiesWithStudents ?? []),
        subjectsWithTopics: @json($subjectsWithTopics ?? []),
        subjectsWithQuestions: @json($subjectsWithQuestions ?? []),
        groupsWithStudents: @json($groupsWithStudents ?? [])
    };

    // Translations for JavaScript
    window.translations = {
        groups: @json(__('groups')),
        students: @json(__('students')),
        questions: @json(__('questions')),
        numberOfGroups: @json(__('Number of Groups')),
        numberOfStudents: @json(__('Number of Students')),
        totalStudents: @json(__('Total Students')),
        numberOfQuestions: @json(__('Number of Questions'))
    };
</script>

@vite(['resources/js/admin/dashboard.js'])
@endpush