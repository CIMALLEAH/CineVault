<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Movie;
use App\Models\AuditLog;
use Illuminate\Http\Request;
 
class ApprovalController extends Controller
{
    public function index()
    {
        $approvals = Approval::with(['requester', 'movie'])
            ->latest()
            ->paginate(20);
 
        return view('admin.approvals.index', compact('approvals'));
    }
 
    public function approve(Approval $approval)
    {
        if ($approval->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }
 
        // Execute the requested action
        if ($approval->type === 'add_movie') {
            $data = $approval->payload;
            $movie = Movie::create(array_merge($data, [
                'added_by' => $approval->requested_by,
                'status'   => 'available',
            ]));
            $approval->update(['movie_id' => $movie->id]);
        }
 
        if ($approval->type === 'delete_movie' && $approval->movie) {
            $movie = $approval->movie;
            if ($movie->hasActiveRental()) {
                return back()->with('error', 'Cannot approve delete: movie has active rental.');
            }
            AuditLog::write('MOVIE_DELETED', "Movie \"{$movie->title}\" deleted via approval.",
                auth()->id(), Movie::class, $movie->id);
            $movie->delete();
        }
 
        $approval->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
 
        AuditLog::write('APPROVAL_APPROVED',
            "Approval #{$approval->id} ({$approval->getTypeLabel()}) approved by " . auth()->user()->name,
            auth()->id(), Approval::class, $approval->id);
 
        return back()->with('success', 'Request approved and executed.');
    }
 
    public function reject(Request $request, Approval $approval)
    {
        if ($approval->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }
 
        $approval->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);
 
        AuditLog::write('APPROVAL_REJECTED',
            "Approval #{$approval->id} rejected by " . auth()->user()->name,
            auth()->id(), Approval::class, $approval->id);
 
        return back()->with('success', 'Request rejected.');
    }
}
