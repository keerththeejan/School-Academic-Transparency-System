<?php

namespace App\Support;

use App\Models\User;

class Navigation
{
    public static function items(User $user): array
    {
        $items = [
            ['label' => 'ui.dashboard', 'route' => 'dashboard', 'permission' => null],
            ['label' => 'ui.today_classes', 'route' => 'teacher.dashboard', 'permission' => 'timetable.view.own', 'only' => ['teacher']],
            ['label' => 'ui.my_children', 'route' => 'parent.dashboard', 'permission' => 'children.view.own', 'only' => ['parent']],
            ['label' => 'ui.provinces', 'route' => 'provinces.index', 'permission' => 'provinces.manage'],
            ['label' => 'ui.districts', 'route' => 'districts.index', 'permission' => 'districts.manage'],
            ['label' => 'ui.zones', 'route' => 'zones.index', 'permission' => 'zones.manage'],
            ['label' => 'ui.schools', 'route' => 'schools.index', 'permission' => 'schools.view'],
            ['label' => 'ui.academic_years', 'route' => 'academic-years.index', 'permission' => 'school.admin'],
            ['label' => 'ui.terms', 'route' => 'terms.index', 'permission' => 'school.admin'],
            ['label' => 'ui.classes', 'route' => 'classes.index', 'permission' => 'classes.manage'],
            ['label' => 'ui.subjects', 'route' => 'subjects.index', 'permission' => 'subjects.manage'],
            ['label' => 'ui.students', 'route' => 'students.index', 'permission' => 'students.manage'],
            ['label' => 'ui.parents', 'route' => 'parents.index', 'permission' => 'parents.manage'],
            ['label' => 'ui.teachers', 'route' => 'teachers.index', 'permission' => 'teachers.manage'],
            ['label' => 'ui.timetable', 'route' => 'timetable.index', 'permission' => 'timetable.manage'],
            ['label' => 'ui.calendar', 'route' => 'calendar.index', 'permission' => 'calendar.manage'],
            ['label' => 'ui.leave', 'route' => 'leave.index', 'permission' => 'leave.manage'],
            ['label' => 'ui.relief', 'route' => 'relief.index', 'permission' => 'relief.manage'],
            ['label' => 'ui.deviations', 'route' => 'deviations.index', 'permission' => 'deviations.monitor'],
            ['label' => 'ui.summaries', 'route' => 'summaries.index', 'permission' => 'summaries.view'],
            ['label' => 'ui.discrepancies', 'route' => 'discrepancies.index', 'permission' => 'discrepancies.review'],
            ['label' => 'ui.reports', 'route' => 'reports.index', 'permission' => 'reports.view.school'],
            ['label' => 'ui.reports', 'route' => 'reports.index', 'permission' => 'reports.view.aggregate'],
            ['label' => 'ui.notifications', 'route' => 'notifications.index', 'permission' => 'notifications.view'],
            ['label' => 'ui.users', 'route' => 'users.index', 'permission' => 'users.manage'],
            ['label' => 'ui.role_admin', 'route' => 'roles.index', 'permission' => 'roles.manage'],
            ['label' => 'ui.audit_logs', 'route' => 'audit.index', 'permission' => 'audit.view'],
            ['label' => 'ui.settings', 'route' => 'settings.edit', 'permission' => 'settings.manage'],
            ['label' => 'ui.search', 'route' => 'search.index', 'permission' => 'students.manage'],
        ];

        $seen = [];
        $visible = [];
        foreach ($items as $item) {
            if (isset($item['only']) && ! $user->hasAnyRole($item['only'])) {
                continue;
            }
            if ($item['permission'] && ! $user->hasPermission($item['permission'])) {
                continue;
            }
            $key = $item['route'].$item['label'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $visible[] = $item;
        }

        return $visible;
    }
}
