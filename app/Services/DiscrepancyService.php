<?php

namespace App\Services;

use App\Events\DiscrepancyOpened;
use App\Models\DiscrepancyAction;
use App\Models\DiscrepancyCase;
use App\Models\ParentFeedback;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use App\Support\Classifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscrepancyService
{
    public function __construct(private AuditLogger $audit) {}

    public function report(StudentParent $parent, array $data): DiscrepancyCase
    {
        $student = Student::query()->with('schoolClass')->findOrFail($data['student_id']);
        $owns = $parent->students()->where('students.id', $student->id)->exists();

        if (! $owns) {
            abort(403);
        }

        return DB::transaction(function () use ($parent, $student, $data) {
            $feedback = ParentFeedback::query()->create([
                'parent_id' => $parent->id,
                'student_id' => $student->id,
                'school_id' => $student->school_id,
                'date' => $data['date'],
                'class_id' => $student->class_id,
                'period' => $data['period'] ?? null,
                'description' => $data['description'],
                'status' => 'open',
            ]);

            $case = DiscrepancyCase::query()->create([
                'feedback_id' => $feedback->id,
                'school_id' => $student->school_id,
                'status' => 'open',
            ]);

            DiscrepancyAction::query()->create([
                'case_id' => $case->id,
                'user_id' => $parent->user_id,
                'action' => 'reported',
                'notes' => $feedback->description,
                'created_at' => now(),
            ]);

            $this->audit->log($parent->user, 'CREATE', 'parent_feedback', $feedback->id, null, [
                'student_id' => $student->id,
                'date' => $data['date'],
                'period' => $data['period'] ?? null,
            ], null, $student->school_id);

            DiscrepancyOpened::dispatch($case);

            return $case->load('feedback');
        });
    }

    public function act(User $actor, DiscrepancyCase $case, string $action, ?string $notes = null): DiscrepancyCase
    {
        return DB::transaction(function () use ($actor, $case, $action, $notes) {
            if ($case->status === 'open') {
                $case->forceFill([
                    'status' => 'in_review',
                    'assigned_to' => $case->assigned_to ?: $actor->id,
                ])->save();
            }

            DiscrepancyAction::query()->create([
                'case_id' => $case->id,
                'user_id' => $actor->id,
                'action' => $action,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            $this->audit->log($actor, 'UPDATE', 'discrepancy_case', $case->id, null, ['action' => $action], $notes, $case->school_id);

            return $case->refresh();
        });
    }

    public function resolve(User $actor, DiscrepancyCase $case, string $classification, string $resolution): DiscrepancyCase
    {
        if (! in_array($classification, Classifications::all(), true)) {
            throw ValidationException::withMessages(['classification' => __('ui.invalid_classification')]);
        }

        return DB::transaction(function () use ($actor, $case, $classification, $resolution) {
            $old = $case->only(['status', 'classification', 'resolution']);
            $case->forceFill([
                'status' => 'resolved',
                'classification' => $classification,
                'resolution' => $resolution,
                'resolved_by' => $actor->id,
                'resolved_at' => now(),
            ])->save();

            $case->feedback?->forceFill(['status' => 'reviewed'])->save();

            DiscrepancyAction::query()->create([
                'case_id' => $case->id,
                'user_id' => $actor->id,
                'action' => 'resolved',
                'notes' => $classification.': '.$resolution,
                'created_at' => now(),
            ]);

            $this->audit->log($actor, 'CLOSE', 'discrepancy_case', $case->id, $old, $case->only(['status', 'classification', 'resolution']), $resolution, $case->school_id);

            return $case;
        });
    }
}
