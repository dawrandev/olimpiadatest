<table class="table table-hover">
    <thead>
        <tr>
            <th>№</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($subjects as $subject)
        <tr>
            <td>{{ $subjects->firstItem() + $loop->index }}</td>
            <td>
                <span class="f-w-500">
                    {{ optional($subject->translations->firstWhere('language_id', currentLanguageId()))->name }}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-outline-warning px-2 py-1">
                        <i class="icon-pencil"></i>
                    </a>
                    <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="d-inline">
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
            <td colspan="3" class="text-center py-5 text-muted">
                <i class="icofont icofont-search"></i> {{ __('No subjects found') }}
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($subjects->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $subjects->firstItem() }} {{ __('to') }} {{ $subjects->lastItem() }} {{ __('of') }} {{ $subjects->total() }} {{ __('entries') }}
    </div>
    <div>
        {{ $subjects->links() }}
    </div>
</div>
@endif