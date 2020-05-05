<?php

namespace App\Http\Controllers;

use App\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $audit_logs = AuditLog::orderby('datetime', 'desc')->paginate(20); //show only 5 items at a time in descending order

        return view('audit_logs.index', compact('audit_logs'));
    }
}
