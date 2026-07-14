<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class AdminLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('usuario')->latest()->paginate(50);
        return view('admin.logs.index', compact('logs'));
    }
}
