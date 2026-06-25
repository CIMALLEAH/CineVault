<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Approval;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    /**
     * Admins get full access. Staff who land here get redirected — they use
     * the Staff\MovieController which routes through the approval system.
     */
    private function isAdmin(): bool
    {
        return auth()->user()->isAdmin();
    }

    // ─── Shared validation rules ────────────────────────────────────────────────

    private function movieRules(): array
    {
        return [
            'title'               => 'required|string|max:255',
            'genre'               => 'required|string|max:100',
            'year'                => 'required|integer|min:1900|max:' . (date('Y') + 2),
            'director'            => 'nullable|string|max:255',
            'duration'            => 'nullable|integer|min:1',
            'rating'              => 'required|in:G,PG,PG-13,R',
            'description'         => 'nullable|string',
            'poster_icon'         => 'nullable|string|max:10',
            'poster'              => 'nullable|image|max:2048',
            'price_per_day'       => 'required|numeric|min:0',
            'price_per_screening' => 'nullable|numeric|min:0',
            'price_per_week'      => 'nullable|numeric|min:0',
            'copies'              => 'required|integer|min:1',
        ];
    }

    // ─── Index ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
            $query = Movie::with('addedBy');

            if ($request->filled('genre'))  $query->where('genre', $request->genre);
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('search')) $query->where('title', 'like', '%' . $request->search . '%');

            $sortField = $request->get('sort', 'title');
            $sortDir   = $request->get('dir', 'asc');
            $query->orderBy($sortField, $sortDir);

            $movies = $query->paginate(12)->withQueryString();
            $genres = Movie::distinct()->pluck('genre')->sort()->values();

            return view('admin.movies.index', compact('movies', 'genres'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load movies: ' . $e->getMessage());
        }
    }

    // ─── Create ─────────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.movies.create');
    }

    // ─── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate($this->movieRules());

        try {
            DB::beginTransaction();

            if ($request->hasFile('poster')) {
                $validated['poster_path'] = $request->file('poster')->store('posters', 'public');
            }

            $validated['added_by']        = auth()->id();
            $validated['available_copies'] = $validated['copies'];
            $validated['status']           = 'available';

            $movie = Movie::create($validated);

            AuditLog::write('MOVIE_ADDED',
                "Movie \"{$movie->title}\" added by " . auth()->user()->name,
                auth()->id(), Movie::class, $movie->id, [], $movie->toArray());

            DB::commit();

            return redirect()->route('admin.movies.index')
                             ->with('success', "Movie \"{$movie->title}\" added successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to add movie: ' . $e->getMessage());
        }
    }

    // ─── Edit ───────────────────────────────────────────────────────────────────

    public function edit(Movie $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }

    // ─── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate($this->movieRules());

        try {
            DB::beginTransaction();

            $old = $movie->toArray();

            if ($request->hasFile('poster')) {
                if ($movie->poster_path) {
                    Storage::disk('public')->delete($movie->poster_path);
                }
                $validated['poster_path'] = $request->file('poster')->store('posters', 'public');
            }

            // Adjust available copies if total copies changed
            $copiesDiff = $validated['copies'] - $movie->copies;
            $validated['available_copies'] = max(0, $movie->available_copies + $copiesDiff);

            // Recalculate status based on available copies
            if ($validated['available_copies'] > 0 && $movie->status !== 'inactive') {
                $validated['status'] = 'available';
            } elseif ($validated['available_copies'] === 0) {
                $validated['status'] = 'rented';
            }

            $movie->update($validated);

            AuditLog::write('MOVIE_UPDATED',
                "Movie \"{$movie->title}\" updated by " . auth()->user()->name,
                auth()->id(), Movie::class, $movie->id, $old, $movie->fresh()->toArray());

            DB::commit();

            return redirect()->route('admin.movies.index')
                             ->with('success', "Movie updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update movie: ' . $e->getMessage());
        }
    }

    // ─── Destroy ────────────────────────────────────────────────────────────────

    public function destroy(Movie $movie)
    {
        try {
            if ($movie->hasActiveRental()) {
                return back()->with('error', 'Cannot delete: this movie has an active rental.');
            }

            DB::beginTransaction();

            $title = $movie->title;
            AuditLog::write('MOVIE_DELETED',
                "Movie \"{$title}\" deleted by " . auth()->user()->name,
                auth()->id(), Movie::class, $movie->id, $movie->toArray(), []);

            if ($movie->poster_path) {
                Storage::disk('public')->delete($movie->poster_path);
            }

            $movie->delete();

            DB::commit();

            return redirect()->route('admin.movies.index')
                             ->with('success', "Movie \"{$title}\" deleted.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete movie: ' . $e->getMessage());
        }
    }
}