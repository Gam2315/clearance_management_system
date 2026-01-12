<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity as ActivityLog;

class Activity extends Controller
{
      public function index()
    {
        $logs = ActivityLog::with('causer')->latest()->paginate(50); // Added pagination for better performance

        return view('admin.activity-logs.index', compact('logs'));
    }
}

