@extends('backend.layouts.app')
@section('title', localize('employee_create'))
@section('content')
    @include('humanresource::employee_header')
    <div class="card mb-3 fixed-tab-body">
        @include('backend.layouts.common.validation')
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fs-17 fw-semi-bold mb-0">{{ localize('employee') }}</h6>
                </div>
                <div class="text-end">
                    <div class="actions">
                        <a href="{{ route('employees.index') }}" class="btn btn-success btn-sm"><i
                                class="fa fa-list"></i>&nbsp; {{ localize('employee_list') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-12 text-center">
                    <form action="{{ route('employees.store') }}" method="POST" class="f1" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="basic_setup_rule_id" value="{{ @$basic_setup_rule->id }}">
                        <input type="hidden" id="sub_departments" value="{{ json_encode($sub_departments) }}">
                        <fieldset>
                            <h5 class="mb-3 fw-semi-bold">{{ localize('basic_info') }}:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    @input(['input_name' => 'first_name', 'additional_class' => 'required-field'])
                                    @input(['input_name' => 'middle_name', 'required' => false])
                                    @input(['input_name' => 'last_name', 'additional_class' => 'required-field'])
                                    @input(['input_name' => 'email', 'type' => 'email', 'additional_class' => 'required-field', 'additional_id' => 'email', 'required' => true])
                                    @input(['input_name' => 'phone', 'additional_class' => 'required-field'])
                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="country"
                                            class="col-sm-3 col-form-label ps-0">{{ localize('country') }}</label>
                                        <div class="col-lg-9">
                                            <select name="state_id" class="form-select select-basic-single">
                                                <option value="">{{ localize('select_country') }}</option>
                                                @foreach ($countries as $key => $country)
                                                    <option value="{{ $country->id }}"
                                                        {{ old('state_id') == $country->id ? 'selected' : '' }}>
                                                        {{ $country->country_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('state_id'))
                                                <div class="error text-danger text-start">{{ $errors->first('state_id') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @input(['input_name' => 'city', 'required' => false])
                                    @input(['input_name' => 'basic_salary', 'type' => 'number', 'required' => true, 'additional_class' => 'required-field basic_salary calculate-salary'])

                                </div>
                                <div class="col-md-6">

                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="employee_type_id"
                                            class="col-sm-3 col-form-label ps-0">{{ localize('employee_type') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="employee_type_id" class="form-select required-field" required>
                                                <option value="">{{ localize('select_employee_type') }}</option>
                                                @foreach ($employee_types as $key => $employee_type)
                                                    <option value="{{ $employee_type->id }}"
                                                        {{ old('employee_type_id') == $employee_type->id ? 'selected' : '' }}>
                                                        {{ $employee_type->name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('employee_type_id'))
                                                <div class="error text-danger text-start">
                                                    {{ $errors->first('employee_type_id') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="attendance_time_id"
                                            class="col-sm-3 col-form-label ps-0">{{ localize('attendance_time') }}
                                            <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <select name="attendance_time_id" class="form-select required-field" required>
                                                <option value="">{{ localize('select_attendance_time') }}</option>
                                                @foreach ($times as $key => $time)
                                                    <option value="{{ $time->id }}"
                                                        {{ old('attendance_time_id') == $time->id ? 'selected' : '' }}>
                                                        {{ $time->name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('attendance_time_id'))
                                                <div class="error text-danger text-start">
                                                    {{ $errors->first('attendance_time_id') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="department_id"
                                            class="col-sm-3 col-form-label ps-0">{{ localize('department') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="department_id" class="form-select required-field"
                                                id="department">
                                                <option value="">{{ localize('select_department') }}</option>
                                                @foreach ($departments as $key => $department)
                                                    <option value="{{ $department->id }}"
                                                        {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                        {{ $department->department_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('department_id'))
                                                <div class="error text-danger text-start">
                                                    {{ $errors->first('department_id') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="sub_department_id"
                                            class="col-sm-3 col-form-label ps-0">{{ localize('sub_department') }}
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="sub_department_id" class="form-select" id="sub_department"
                                                @disabled(true)>
                                                <option value="">{{ localize('select_department') }}</option>
                                                @foreach ($sub_departments as $key => $sub_department)
                                                    <option value="{{ $sub_department->id }}"
                                                        {{ old('department_id') == $sub_department->id ? 'selected' : '' }}>
                                                        {{ $sub_department->department_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('sub_department_id'))
                                                <div class="error text-danger text-start">
                                                    {{ $errors->first('sub_department_id') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="position"
                                            class="col-sm-3 col-form-label ps-0">{{ localize('position') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="position_id" class="form-select required-field">
                                                <option value="">{{ localize('select_position') }}</option>
                                                @foreach ($positions as $key => $position)
                                                    <option value="{{ $position->id }}"
                                                        {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                        {{ $position->position_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('position_id'))
                                                <div class="error text-danger text-start">
                                                    {{ $errors->first('position_id') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    @input(['input_name' => 'joining_date', 'type' => 'date', 'additional_class' => 'required-field'])
                                    @input(['input_name' => 'password','type' => 'password', 'additional_class' => 'required-field'])
                                </div>




                            <div class="f1-buttons">
                                <button type="submit"
                                    class="btn btn-success btn-submit">{{ localize('submit') }}</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('backend/assets/plugins/bootstrap-wizard/form.scripts.js') }}"></script>
    <script src="{{ module_asset('HumanResource/js/salary.js') }}"></script>
    <script src="{{ module_asset('HumanResource/js/employee_form_wiz.js') }}"></script>
    <script src="{{ module_asset('HumanResource/js/employee-create.js') }}"></script>
@endpush
