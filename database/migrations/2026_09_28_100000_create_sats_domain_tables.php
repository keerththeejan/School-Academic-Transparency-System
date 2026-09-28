<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->default('general');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'user_id']);
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['permission_id', 'role_id']);
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->unique(['province_id', 'code']);
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->unique(['district_id', 'code']);
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->foreignId('zone_id')->constrained()->restrictOnDelete();
            $table->string('school_code')->unique();
            $table->string('school_name');
            $table->string('address')->nullable();
            $table->string('language', 5)->default('en');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->index(['zone_id', 'status']);
            $table->index('school_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('planned')->index();
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('planned')->index();
            $table->timestamps();
        });

        Schema::create('school_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('calendar_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_school_day')->default(false);
            $table->unsignedTinyInteger('substitutes_day_of_week')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['school_id', 'date']);
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('grade', 20);
            $table->string('section', 10);
            $table->string('medium', 20)->default('tamil');
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->unique(['school_id', 'academic_year_id', 'grade', 'section', 'medium'], 'classes_identity_unique');
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('subject_code', 20);
            $table->string('subject_name');
            $table->string('medium', 20)->default('tamil');
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->unique(['school_id', 'subject_code']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('admission_number');
            $table->string('student_identifier')->unique();
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->unique(['school_id', 'admission_number']);
            $table->index('full_name');
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('mobile', 20)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('preferred_language', 5)->default('en');
            $table->boolean('whatsapp_available')->default(false);
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('relationship')->default('parent');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['parent_id', 'student_id']);
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_identifier');
            $table->string('full_name');
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->unique(['school_id', 'employee_identifier']);
            $table->unique('user_id');
            $table->index('full_name');
        });

        Schema::create('teacher_leave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_leave');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('students');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('school_calendar');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_years');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropConstrainedForeignId('zone_id');
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('province_id');
        });

        Schema::dropIfExists('schools');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
