@extends('backend.layouts.app')

@section('title', localize('Reset Password'))

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
                                <p class="mb-1 fs-16 fw-medium">Reset Password</p>
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
                <form action="{{ route('employee.reset_password.submit') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="current_password">{{ localize('Current Password') }}</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="new_password">{{ localize('New Password') }}</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="confirm_password">{{ localize('Confirm Password') }}</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">{{ localize('Reset Password') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('backend/assets/plugins/apexcharts/dist/apexcharts.js') }}"></script>
    <script src="{{ asset('public/backend/assets/dist/js/employee-dashboardchart.js?v=1') }}"></script>
@endpush
