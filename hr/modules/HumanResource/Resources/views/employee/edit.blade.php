@extends('backend.layouts.app')
@section('title', localize('edit_employee'))
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">
    <link rel="stylesheet" href="{{ asset('backend/assets/plugins/tagsinput/bootstrap-tagsinput.css') }}">
    <link rel="stylesheet" href="{{ module_asset('HumanResource/css/employee.css') }}">
@endpush

@section('content')

    @include('humanresource::employee_header')

    <div class="card mb-3 fixed-tab-body">
        @include('backend.layouts.common.validation')
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fs-17 fw-semi-bold mb-0">{{ localize('employee_update') }}</h6>
                </div>
                <div class="text-end">
                    <div class="actions">
                        @can('read_employee')
                            <a href="{{ route('employees.index') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-list"></i>&nbsp; {{ localize('employee_list') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-12 text-center">
                    <form action="{{ route('employees.update', $employee->uuid) }}" method="POST" enctype="multipart/form-data">
                        @method('PATCH')
                        @csrf
                        <input type="hidden" name="basic_setup_rule_id" value="{{ @$basic_setup_rule->id }}">
                        <input type="hidden" id="sub_departments" value="{{ json_encode($sub_departments) }}">

                        <fieldset>
                            <h5 class="mb-3 fw-semi-bold">{{ localize('basic_info') }}:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    @input(['input_name' => 'first_name', 'additional_class' => 'required-field', 'value' => $employee->first_name])
                                    @input(['input_name' => 'middle_name', 'value' => $employee->middle_name, 'required' => false])
                                    @input(['input_name' => 'last_name', 'additional_class' => 'required-field', 'value' => $employee->last_name])
                                    @input(['input_name' => 'email', 'type' => 'email', 'additional_class' => 'required-field', 'value' => $employee->email])
                                    @input(['input_name' => 'phone', 'additional_class' => 'required-field', 'value' => $employee->phone])

                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="country" class="col-sm-3 col-form-label ps-0">{{ localize('country') }}</label>
                                        <div class="col-lg-9">
                                            <select name="state_id" class="form-select select-basic-single">
                                                <option value="">{{ localize('select_country') }}</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}" {{ $country->id == $employee->state_id ? 'selected' : '' }}>
                                                        {{ $country->country_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('state_id')
                                                <div class="error text-danger text-start">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    @input(['input_name' => 'city', 'required' => false, 'value' => $employee->city])
                                    @input(['input_name' => 'basic_salary', 'type' => 'number', 'required' => true, ])
                                </div>

                                <div class="col-md-6">
                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="employee_type_id" class="col-sm-3 col-form-label ps-0">{{ localize('employee_type') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="employee_type_id" class="form-select required-field" required>
                                                <option value="">{{ localize('select_employee_type') }}</option>
                                                @foreach ($employee_types as $employee_type)
                                                    <option value="{{ $employee_type->id }}" {{ $employee_type->id == $employee->employee_type_id ? 'selected' : '' }}>
                                                        {{ $employee_type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('employee_type_id')
                                                <div class="error text-danger text-start">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="department_id" class="col-sm-3 col-form-label ps-0">{{ localize('department') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="department_id" class="form-select required-field" id="department">
                                                <option value="">{{ localize('select_department') }}</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}" {{ $department->id == $employee->department_id ? 'selected' : '' }}>
                                                        {{ $department->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('department_id')
                                                <div class="error text-danger text-start">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="sub_department_id" class="col-sm-3 col-form-label ps-0">{{ localize('sub_department') }}</label>
                                        <div class="col-lg-9">
                                            <select name="sub_department_id" class="form-select" id="sub_department">
                                                <option value="">{{ localize('select_sub_department') }}</option>
                                                @isset($employee->sub_department_id)
                                                    <option value="{{ $employee->sub_department->id }}" selected>
                                                        {{ $employee->sub_department->department_name }}
                                                    </option>
                                                @endisset
                                            </select>
                                            @error('sub_department_id')
                                                <div class="error text-danger text-start">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="cust_border form-group mb-3 mx-0 pb-3 row">
                                        <label for="position" class="col-sm-3 col-form-label ps-0">{{ localize('position') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="position_id" class="form-select required-field">
                                                <option value="">{{ localize('select_position') }}</option>
                                                @foreach ($positions as $position)
                                                    <option value="{{ $position->id }}" {{ $position->id == $employee->position_id ? 'selected' : '' }}>
                                                        {{ $position->position_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('position_id')
                                                <div class="error text-danger text-start">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    @input(['input_name' => 'joining_date', 'type' => 'date', 'additional_class' => 'required-field', 'value' => $employee->joining_date])
                                    @input(['input_name' => 'password', 'type' => 'password', 'additional_class' => 'required-field', 'required' => false, 'value' => ''])
                                </div>
                            </div>

                            <!-- Add any other fields from create.blade.php that are necessary -->

                            <div class="text-start mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save"></i> {{ localize('update') }}
                                </button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Include necessary JS scripts -->
    <script src="{{ asset('backend/assets/plugins/bootstrap-wizard/form.scripts.js') }}"></script>
    <script src="{{ module_asset('HumanResource/js/salary.js') }}"></script>
    <script src="{{ module_asset('HumanResource/js/employee_form_wiz.js') }}"></script>
    <script src="{{ module_asset('HumanResource/js/employee-edit.js') }}"></script>
@endpush
