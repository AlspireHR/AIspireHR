@extends('backend.layouts.app')

@section('title', localize('Employee Profile'))

@push('css')
    <link href="{{ asset('backend/assets/custom.css') }}" rel="stylesheet">
@endpush

@section('employeeside')
    @include('employee.emp-side')
@endsection

@section('content')
    @include('backend.layouts.common.message')

    <div class="tab-content" id="pills-tabContent">
        <div id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <div class="row g-4">
                <div class="col-xl-5 col-xxl-6">
                    <div class="card welcome-card justify-content-center h-100 p-4 rounded-15">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <p class="mb-1 fs-16 fw-medium">Employee Profile</p>
                                <p class="mb-0 fs-18 fw-medium">
                                    <span class="fw-bold">{{ ucwords(localize('hrm_dashboard')) }}</span>,
                                    {{ $user_info->full_name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('employee.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name">{{ localize('Full Name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $user_info->full_name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="email">{{ localize('Email') }}</label>
                                <input type="email" name="email" class="form-control" value="{{ $user_info->email }}">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">{{ localize('Update Profile') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('backend/assets/plugins/apexcharts/dist/apexcharts.js') }}"></script>
    <script src="{{ asset('public/backend/assets/dist/js/employee-dashboardchart.js?v=1') }}"></script>
@endpush
