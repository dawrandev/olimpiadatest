{{-- resources/views/partials/admin/questions/question_list.blade.php --}}
{{-- Bu fayl faqat savollar ro'yxatini render qiladi, container oldindan mavjud --}}

@if($questions->count() > 0)
<div class="row">
    @foreach($questions as $index => $question)
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm question-card">
            @php
            $languages = getLanguages();
            $selectedLanguageId = request('language_id', $languages->first()->id);
            $questionNumber = ($questions->currentPage() - 1) * $questions->perPage() + $index + 1;
            @endphp

            <div class="position-relative">
                @if($question->image && $question->image !== 'medicaltest.png')
                <img src="{{ asset('storage/questions/' . $question->image) }}"
                    class="card-img-top"
                    style="height: 150px; object-fit: cover;"
                    alt="Question Image">
                @else
                <img src="{{ asset('storage/logo.png') }}"
                    class="card-img-top"
                    style="height: 150px; object-fit: contain; padding: 20px; background-color: #f8f9fa;"
                    alt="Logo">
                @endif
                <div class="position-absolute top-0 start-0 p-2">
                    <span class="badge bg-dark bg-opacity-75 fs-6">
                        #{{ $questionNumber }}
                    </span>
                </div>
            </div>

            <div class="card-body d-flex flex-column">
                <div class="mb-3 flex-grow-1">
                    <p class="card-text text-muted mb-0">
                        {!! $question->text ?? __('No text available') !!}
                    </p>
                </div>

                <div class="mt-auto">
                    <div class="d-flex gap-2">
                        <button type="button"
                            class="btn btn-outline-info btn-sm flex-fill view-question-btn"
                            data-question-id="{{ $question->id }}"
                            data-language-id="{{ $selectedLanguageId }}">
                            <i class="icofont icofont-eye me-1"></i>
                        </button>

                        <a href="{{ route('admin.questions.edit', $question->id) }}"
                            class="btn btn-outline-warning btn-sm flex-fill">
                            <i class="icofont icofont-edit me-1"></i>
                        </a>

                        <button type="button"
                            class="btn btn-outline-danger btn-sm flex-fill confirm-action"
                            data-action="delete"
                            data-question-id="{{ $question->id }}">
                            <i class="icofont icofont-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $questions->appends(request()->query())->links() }}
</div>
@else
<div class="text-center py-5">
    <div class="mb-3">
        <i class="icofont icofont-question-circle text-muted" style="font-size: 4rem;"></i>
    </div>
    <h5 class="text-muted">{{__('No questions found')}}</h5>
    <p class="text-muted">{{__('Try adjusting your filters or create new questions')}}</p>
    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
        <i class="icofont icofont-plus me-1"></i>
        {{__('Create Question')}}
    </a>
</div>
@endif