@extends('backend.layouts.app')

@section('title', localize('Leave Summary'))

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
                                <p class="mb-1 fs-16 fw-medium">Leave Summary</p>
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
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ localize('Leave Type') }}</th>
                            <th>{{ localize('From Date') }}</th>
                            <th>{{ localize('To Date') }}</th>
                            <th>{{ localize('Status') }}</th>
                            <th>{{ localize('Reason') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaveSummaries as $leave)
                            <tr>
                                <td>{{ $leave->leaveType->leave_type }}</td>
                                <td>{{ $leave->from_date }}</td>
                                <td>{{ $leave->to_date }}</td>
                                <td>{{ $leave->is_approved ? 'Approved' : 'Pending' }}</td>
                                <td>{{ $leave->reason }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('backend/assets/plugins/apexcharts/dist/apexcharts.js') }}"></script>
    <script src="{{ asset('public/backend/assets/dist/js/employee-dashboardchart.js?v=1') }}"></script>
@endpush
