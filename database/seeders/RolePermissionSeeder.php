<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'system.manage' => 'System',
            'provinces.manage' => 'Geography',
            'districts.manage' => 'Geography',
            'zones.manage' => 'Geography',
            'schools.manage' => 'Geography',
            'schools.view' => 'Geography',
            'users.manage' => 'Access',
            'roles.manage' => 'Access',
            'permissions.manage' => 'Access',
            'settings.manage' => 'System',
            'audit.view' => 'System',
            'reports.view.aggregate' => 'Reports',
            'reports.view.school' => 'Reports',
            'reports.export' => 'Reports',
            'statistics.view.province' => 'Reports',
            'statistics.view.zone' => 'Reports',
            'aggregate.view' => 'Reports',
            'discrepancies.monitor' => 'Discrepancies',
            'discrepancies.review' => 'Discrepancies',
            'discrepancies.report.own' => 'Family',
            'feedback.view' => 'Discrepancies',
            'school.admin' => 'School',
            'classes.manage' => 'School',
            'students.manage' => 'School',
            'teachers.manage' => 'School',
            'parents.manage' => 'School',
            'subjects.manage' => 'School',
            'timetable.manage' => 'Timetable',
            'timetable.verify' => 'Timetable',
            'timetable.approve' => 'Timetable',
            'timetable.publish' => 'Timetable',
            'timetable.view.own' => 'Teaching',
            'calendar.manage' => 'School',
            'deviations.record' => 'Deviations',
            'deviations.report' => 'Deviations',
            'deviations.approve' => 'Deviations',
            'deviations.monitor' => 'Deviations',
            'leave.manage' => 'Staff',
            'leave.approve' => 'Staff',
            'relief.manage' => 'Staff',
            'relief.provide' => 'Staff',
            'summaries.view' => 'Summaries',
            'summaries.generate' => 'Summaries',
            'summaries.view.own' => 'Family',
            'notifications.view' => 'Notifications',
            'children.view.own' => 'Family',
            'profile.manage' => 'Account',
        ];

        foreach ($permissions as $slug => $group) {
            Permission::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => str($slug)->replace(['.', '_'], ' ')->title()->toString(), 'group' => $group]
            );
        }

        $matrix = [
            'super_admin' => ['*'],
            'ministry_admin' => ['schools.view', 'reports.view.aggregate', 'reports.export', 'statistics.view.province', 'aggregate.view', 'notifications.view', 'profile.manage'],
            'provincial_admin' => ['schools.view', 'statistics.view.province', 'reports.view.aggregate', 'reports.export', 'aggregate.view', 'profile.manage'],
            'zonal_admin' => ['schools.view', 'statistics.view.zone', 'reports.view.aggregate', 'reports.export', 'aggregate.view', 'discrepancies.monitor', 'profile.manage'],
            'principal' => ['school.admin', 'classes.manage', 'students.manage', 'teachers.manage', 'parents.manage', 'subjects.manage', 'timetable.manage', 'timetable.approve', 'timetable.publish', 'deviations.approve', 'deviations.monitor', 'deviations.record', 'calendar.manage', 'leave.manage', 'leave.approve', 'relief.manage', 'summaries.view', 'summaries.generate', 'feedback.view', 'discrepancies.review', 'reports.view.school', 'reports.export', 'notifications.view', 'settings.manage', 'profile.manage', 'schools.view', 'audit.view'],
            'academic_coordinator' => ['timetable.manage', 'timetable.verify', 'timetable.approve', 'deviations.monitor', 'deviations.approve', 'discrepancies.review', 'summaries.view', 'summaries.generate', 'reports.view.school', 'reports.export', 'calendar.manage', 'feedback.view', 'schools.view', 'profile.manage', 'classes.manage'],
            'school_officer' => ['deviations.record', 'deviations.monitor', 'relief.manage', 'leave.manage', 'summaries.view', 'schools.view', 'profile.manage'],
            'teacher' => ['timetable.view.own', 'deviations.report', 'relief.provide', 'profile.manage'],
            'parent' => ['children.view.own', 'summaries.view.own', 'discrepancies.report.own', 'profile.manage', 'notifications.view'],
            'sdc_viewer' => ['aggregate.view', 'reports.view.aggregate', 'reports.export', 'schools.view', 'profile.manage'],
        ];

        $allIds = Permission::query()->pluck('id', 'slug');

        foreach ($matrix as $slug => $grants) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => __('ui.roles.'.$slug), 'description' => $slug]
            );
            $ids = $grants === ['*']
                ? $allIds->values()->all()
                : $allIds->only($grants)->values()->all();
            $role->permissions()->sync($ids);
        }
    }
}
