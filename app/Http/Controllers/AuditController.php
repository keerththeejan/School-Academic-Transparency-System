<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('audit.view'), 403);
        $records = AuditLog::query()
            ->visibleTo($request->user())
            ->with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('record_type'), fn ($q) => $q->where('record_type', $request->string('record_type')))
            ->orderByDesc('timestamp')
            ->paginate(30);

        return view('audit.index', compact('records'));
    }
}
