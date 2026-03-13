@foreach($subjects as $subject)
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="card subject-card h-100" onclick="selectSubject({{ $subject->id }})">
        <div class="card-body text-center d-flex flex-column justify-content-center">
            <div class="subject-icon mb-3">
                <i class="icofont icofont-book"></i>
            </div>
            <h5 class="card-title mb-2">
                {{ $subject->translations->first()->name ?? __('No name') }}
            </h5>
            <div class="questions-count">
                {{ $subject->questions_count ?? 0 }} {{__('questions')}}
            </div>
        </div>
    </div>
</div>
@endforeach

@if($subjects->isEmpty())
<div class="col-12">
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="icofont icofont-book text-muted" style="font-size: 4rem;"></i>
        </div>
        <h5 class="text-muted">{{__('No subjects found')}}</h5>
        <p class="text-muted">{{__('No subjects available for selected language')}}</p>
    </div>
</div>
@endif