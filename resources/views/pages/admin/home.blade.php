@extends('layouts.admin.main')

@section('title', __('Dashboard'))

@section('content')
<div class="container-fluid mt-1">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>{{ __('Dashboard') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="icofont icofont-chart-bar-graph" style="font-size: 80px; color: #7366ff; opacity: 0.3;"></i>
                        </div>
                        <h4 class="mb-3">{{ __('No Statistics Available') }}</h4>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection