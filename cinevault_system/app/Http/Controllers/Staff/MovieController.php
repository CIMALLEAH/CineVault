<?php
 
namespace App\Http\Controllers\Staff;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Approval;
use App\Models\AuditLog;
use Illuminate\Http\Request;
 
class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query();
 
        if ($request->filled('genre'))  $query->where('genre', $request->genre);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where('title', 'like', '%' . $request->search . '%');
 
        $movies = $query->orderBy('title')->paginate(12)->withQueryString();
        $genres = Movie::distinct()->pluck('genre')->sort()->values();
 
        return view('staff.movies.index', compact('movies', 'genres'));
    }
 
    /** Staff submits add-movie request for admin approval */
    public function requestAdd(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'genre'         => 'required|string|max:100',
            'year'          => 'required|integer|min:1900|max:' . (date('Y') + 2),
            'director'      => 'nullable|string|max:255',
            'duration'      => 'nullable|integer|min:1',
            'rating'        => 'required|in:G,PG,PG-13,R',
            'description'   => 'nullable|string',
            'poster_emoji'  => 'nullable|string|max:10',
            'price_per_day' => 'required|numeric|min:1',
        ]);
 
        Approval::create([
            'type'         => 'add_movie',
            'requested_by' => auth()->id(),
            'payload'      => $validated,
            'reason'       => $request->reason,
        ]);
 
        AuditLog::write('APPROVAL_REQUESTED',
            "Staff " . auth()->user()->name . " requested to add movie \"{$validated['title']}\".",
            auth()->id());
 
        return redirect()->route('staff.movies.index')
                         ->with('success', 'Request submitted! Waiting for admin approval.');
    }
 
    /** Staff submits delete-movie request for admin approval */
    public function requestDelete(Request $request, Movie $movie)
    {
        if ($movie->hasActiveRental()) {
            return back()->with('error', 'Cannot request delete: movie has an active rental.');
        }
 
        Approval::create([
            'type'         => 'delete_movie',
            'requested_by' => auth()->id(),
            'movie_id'     => $movie->id,
            'reason'       => $request->reason ?? 'Staff requested deletion.',
        ]);
 
        AuditLog::write('APPROVAL_REQUESTED',
            "Staff " . auth()->user()->name . " requested to delete movie \"{$movie->title}\".",
            auth()->id(), Movie::class, $movie->id);
 
        return back()->with('success', 'Delete request submitted for admin approval.');
    }
}
 