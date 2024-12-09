@extends('backend.layouts.app')

@section('title', localize('Attendance Records'))

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

                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Table of attendance records -->
                    <div class="col-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ localize('Date') }}</th>
                                    <th>{{ localize('Status') }}</th>
                                    <th>{{ localize('Remarks') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendances as $record)
                                    <tr>
                                        <td>{{ $record->date }}</td>
                                        <td>{{ $record->status }}</td>
                                        <td>{{ $record->remarks }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('backend/assets/plugins/apexcharts/dist/apexcharts.js') }}"></script>
    <script src="{{ asset('public/backend/assets/dist/js/employee-dashboardchart.js?v=1') }}"></script>
@endpush
