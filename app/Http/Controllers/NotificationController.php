<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\PortalNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notes = PortalNotification::query()->where('user_id', $request->user()->id)->latest()->paginate(20, ['*'], 'notes');
        PortalNotification::query()->where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        $logs = null;
        if ($request->user()->hasPermission('notifications.view') && ! $request->user()->hasRole('parent')) {
            $logs = NotificationLog::query()
                ->when($request->user()->school_id, fn ($q) => $q->where('school_id', $request->user()->school_id))
                ->latest()
                ->paginate(20, ['*'], 'logs');
        }

        return view('notifications.index', compact('notes', 'logs'));
    }
}
