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
        try {
            $query = Movie::query();

            if ($request->filled('genre'))  $query->where('genre', $request->genre);
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('search')) $query->where('title', 'like', '%' . $request->search . '%');

            $movies = $query->orderBy('title')->paginate(12)->withQueryString();
            $genres = Movie::distinct()->pluck('genre')->sort()->values();

            return view('staff.movies.index', compact('movies', 'genres'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load movies: ' . $e->getMessage());
        }
    }

    /** Staff submits add-movie request for admin approval */
    public function requestAdd(Request $request)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'genre'               => 'required|string|max:100',
            'year'                => 'required|integer|min:1900|max:' . (date('Y') + 2),
            'director'            => 'nullable|string|max:255',
            'duration'            => 'nullable|integer|min:1',
            'rating'              => 'required|in:G,PG,PG-13,R',
            'description'         => 'nullable|string',
            'poster_icon'         => 'nullable|string|max:10',
            'price_per_day'       => 'required|numeric|min:0',
            'price_per_screening' => 'nullable|numeric|min:0',
            'price_per_week'      => 'nullable|numeric|min:0',
            'copies'              => 'required|integer|min:1',
            'reason'              => 'nullable|string|max:500',
        ]);

        try {
            $reason = $validated['reason'] ?? null;
            unset($validated['reason']);

            Approval::create([
                'type'         => 'add_movie',
                'requested_by' => auth()->id(),
                'payload'      => $validated,
                'reason'       => $reason,
            ]);

            AuditLog::write('APPROVAL_REQUESTED',
                "Staff " . auth()->user()->name . " requested to add movie \"{$validated['title']}\".",
                auth()->id());

            return redirect()->route('staff.movies.index')
                             ->with('success', 'Add request submitted! Waiting for admin approval.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }

    /** Staff submits edit-movie request for admin approval */
    public function requestEdit(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'genre'               => 'required|string|max:100',
            'year'                => 'required|integer|min:1900|max:' . (date('Y') + 2),
            'director'            => 'nullable|string|max:255',
            'duration'            => 'nullable|integer|min:1',
            'rating'              => 'required|in:G,PG,PG-13,R',
            'description'         => 'nullable|string',
            'price_per_day'       => 'required|numeric|min:0',
            'price_per_screening' => 'nullable|numeric|min:0',
            'price_per_week'      => 'nullable|numeric|min:0',
            'copies'              => 'required|integer|min:1',
            'reason'              => 'nullable|string|max:500',
        ]);

        try {
            $reason = $validated['reason'] ?? null;
            unset($validated['reason']);

            Approval::create([
                'type'         => 'edit_movie',
                'requested_by' => auth()->id(),
                'movie_id'     => $movie->id,
                'payload'      => $validated,
                'reason'       => $reason,
            ]);

            AuditLog::write('APPROVAL_REQUESTED',
                "Staff " . auth()->user()->name . " requested to edit movie \"{$movie->title}\".",
                auth()->id(), Movie::class, $movie->id);

            return redirect()->route('staff.movies.index')
                             ->with('success', 'Edit request submitted for admin approval.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to submit edit request: ' . $e->getMessage());
        }
    }

    /** Staff submits delete-movie request for admin approval */
    public function requestDelete(Request $request, Movie $movie)
    {
        try {
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
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit delete request: ' . $e->getMessage());
        }
    }

    /** Show the edit request form for staff */
    public function editRequest(Movie $movie)
    {
        return view('staff.movies.edit-request', compact('movie'));
    }
}