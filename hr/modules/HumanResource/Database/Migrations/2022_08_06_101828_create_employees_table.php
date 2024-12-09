<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->unsignedBigInteger('user_id');
            $table->string('employee_id');
            $table->string('card_no')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->longText('profile_image')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('university_name')->nullable();
            $table->string('supervisor')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('is_admin')->default(0)->nullable();
            $table->string('maiden_name')->nullable();
            $table->string('state_id')->nullable();
            $table->string('city')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('nationality')->nullable();
            $table->date('rehire_date')->nullable();
            $table->double('rate')->nullable();
            $table->unsignedBigInteger('pay_frequency_id')->nullable();
            $table->unsignedBigInteger('duty_type_id')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('marital_status_id')->nullable();
            $table->unsignedInteger('attendance_time_id')->nullable();
            $table->unsignedBigInteger('employee_type_id')->nullable();

            $table->date('contract_start_date')->nullable()->comment('if duty type is contractual');
            $table->date('contract_end_date')->nullable()->comment('if duty type is contractual');

            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('sub_department_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('employee_code')->nullable();

            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('is_disable')->default(0)->nullable();

            $table->boolean('is_active')->default(1)->nullable();
            $table->boolean('is_left')->default(0)->nullable();
            $table->updateCreatedBy();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
