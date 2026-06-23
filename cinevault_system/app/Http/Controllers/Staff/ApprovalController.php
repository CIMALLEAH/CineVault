<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Approval;

class ApprovalController extends Controller
{
    public function index()
    {
        $approvals = Approval::with('movie')
            ->where('requested_by', auth()->id())
            ->latest()
            ->paginate(20);

        return view('staff.approvals.index', compact('approvals'));
    }
}
