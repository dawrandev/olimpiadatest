@extends('layouts.admin.main')
@section('title', 'Barcha natijalar')
@section('content')
<x-admin.breadcrumb title="Barcha natijalar">
    <a href="{{ route('admin.test-assignments.index') }}" class="btn btn-outline-primary btn-sm me-2">
        <i class="icofont icofont-arrow-left"></i>
        Orqaga
    </a>
    <a href="{{ route('admin.test-assignments.all-results.export-excel') }}" class="btn btn-success btn-sm">
        <i class="icofont icofont-file-excel"></i>
        Excel yuklash
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <!-- Results Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Barcha yakunlangan testlar ({{ $results->total() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Talaba ID</th>
                            <th>F.I.O</th>
                            <th>Guruh</th>
                            <th>Fakultet</th>
                            <th>Fan</th>
                            <th class="text-center">To'g'ri/Jami</th>
                            <th class="text-center">Ball</th>
                            <th class="text-center">Foiz</th>
                            <th class="text-center">Boshlangan</th>
                            <th class="text-center">Yakunlangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $index => $result)
                        @php
                            $points = $result->correct_answers * 2;
                        @endphp
                        <tr>
                            <td>{{ $results->firstItem() + $index }}</td>
                            <td>{{ $result->student->student_id ?? '-' }}</td>
                            <td>
                                <strong>{{ $result->student->full_name }}</strong>
                            </td>
                            <td>{{ $result->student->group->name ?? '-' }}</td>
                            <td>{{ $result->student->group->faculty->name ?? '-' }}</td>
                            <td>
                                {{ optional($result->testAssignment->subject->translations->firstWhere('language_id', currentLanguageId()))->name ?? '-' }}
                            </td>
                            <td class="text-center">
                                <strong>{{ $result->correct_answers }}</strong> / {{ $result->total_questions }}
                            </td>
                            <td class="text-center">
                                {{ $points }} ball
                            </td>
                            <td class="text-center">
                                <strong>{{ $result->score }}%</strong>
                            </td>
                            <td class="text-center">
                                <small>{{ $result->started_at ? $result->started_at->format('d.m.Y H:i') : '-' }}</small>
                            </td>
                            <td class="text-center">
                                <small>{{ $result->completed_at ? $result->completed_at->format('d.m.Y H:i') : '-' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="icofont icofont-ui-block icofont-3x text-muted mb-2"></i>
                                <p class="text-muted">Yakunlangan testlar topilmadi</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($results->hasPages())
        <div class="card-footer">
            {{ $results->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .icofont-3x {
        font-size: 3rem;
    }
</style>
@endpush
@endsection
