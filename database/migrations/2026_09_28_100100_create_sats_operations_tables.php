<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'version_number']);
            $table->index(['school_id', 'status', 'effective_from']);
        });

        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('timetable_versions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('period');
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['version_id', 'class_id', 'day_of_week', 'period'], 'timetable_slot_unique');
        });

        Schema::create('daily_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('period');
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('timetable_version_id')->nullable()->constrained('timetable_versions')->nullOnDelete();
            $table->string('status', 30)->default('scheduled')->index();
            $table->timestamps();
            $table->unique(['school_id', 'class_id', 'date', 'period'], 'daily_schedule_slot_unique');
            $table->index(['school_id', 'date']);
        });

        Schema::create('daily_deviations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unsignedTinyInteger('period');
            $table->string('scheduled_subject')->nullable();
            $table->string('scheduled_teacher')->nullable();
            $table->foreignId('scheduled_subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('scheduled_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('deviation_type')->index();
            $table->string('reason')->nullable();
            $table->foreignId('relief_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('action_taken')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'date']);
            $table->index(['class_id', 'date', 'period']);
        });

        Schema::create('relief_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_leave_id')->nullable()->constrained('teacher_leave')->nullOnDelete();
            $table->foreignId('daily_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->date('date')->nullable();
            $table->unsignedTinyInteger('period')->nullable();
            $table->foreignId('relief_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('status', 20)->default('unassigned')->index();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'date']);
        });

        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->text('day_note')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'class_id', 'date'], 'daily_summary_unique');
        });

        Schema::create('daily_summary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('summary_id')->constrained('daily_summaries')->cascadeOnDelete();
            $table->unsignedTinyInteger('period');
            $table->string('scheduled_subject')->nullable();
            $table->string('scheduled_teacher')->nullable();
            $table->string('status', 40)->default('scheduled');
            $table->foreignId('deviation_id')->nullable()->constrained('daily_deviations')->nullOnDelete();
            $table->string('display_text');
            $table->timestamps();
            $table->unique(['summary_id', 'period']);
        });

        Schema::create('parent_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->unsignedTinyInteger('period')->nullable();
            $table->text('description');
            $table->string('status', 20)->default('open')->index();
            $table->timestamps();
            $table->index(['school_id', 'date']);
        });

        Schema::create('discrepancy_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('parent_feedback')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('open')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('classification')->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discrepancy_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('discrepancy_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('parents')->nullOnDelete();
            $table->string('notification_type');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('parents')->nullOnDelete();
            $table->string('channel', 30);
            $table->string('notification_type');
            $table->string('recipient')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('pending')->index();
            $table->string('provider_message_id')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['status', 'channel']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40)->index();
            $table->string('record_type');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('timestamp')->useCurrent()->index();
            $table->index(['record_type', 'record_id']);
            $table->index(['school_id', 'timestamp']);
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('scope_key')->default('global');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->timestamps();
            $table->unique(['scope_key', 'key']);
        });

        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('successful')->default(false)->index();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['email', 'created_at']);
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('logged_out_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('discrepancy_actions');
        Schema::dropIfExists('discrepancy_cases');
        Schema::dropIfExists('parent_feedback');
        Schema::dropIfExists('daily_summary_items');
        Schema::dropIfExists('daily_summaries');
        Schema::dropIfExists('relief_assignments');
        Schema::dropIfExists('daily_deviations');
        Schema::dropIfExists('daily_schedules');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('timetable_versions');
    }
};
