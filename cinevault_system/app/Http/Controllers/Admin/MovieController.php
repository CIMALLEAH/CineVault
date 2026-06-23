<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::with('addedBy');
 
        if ($request->filled('genre')) {
            $query->where('genre', $request->genre);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
 
        $sortField = $request->get('sort', 'title');
        $sortDir   = $request->get('dir', 'asc');
        $query->orderBy($sortField, $sortDir);
 
        $movies = $query->paginate(12)->withQueryString();
        $genres = Movie::distinct()->pluck('genre')->sort()->values();
 
        return view('admin.movies.index', compact('movies', 'genres'));
    }
 
    public function create()
    {
        return view('admin.movies.create');
    }
 
    public function store(Request $request)
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
            'poster'        => 'nullable|image|max:2048',
            'price_per_day' => 'required|numeric|min:1',
            'copies'        => 'integer|min:1',
        ]);
 
        if ($request->hasFile('poster')) {
            $validated['poster_path'] = $request->file('poster')->store('posters', 'public');
        }
 
        $validated['added_by'] = auth()->id();
        $movie = Movie::create($validated);
 
        AuditLog::write('MOVIE_ADDED', "Movie \"{$movie->title}\" added by " . auth()->user()->name,
            auth()->id(), Movie::class, $movie->id, [], $movie->toArray());
 
        return redirect()->route('admin.movies.index')
                         ->with('success', "Movie \"{$movie->title}\" added successfully.");
    }
 
    public function edit(Movie $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }
 
    public function update(Request $request, Movie $movie)
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
            'poster'        => 'nullable|image|max:2048',
            'price_per_day' => 'required|numeric|min:1',
            'copies'        => 'integer|min:1',
        ]);
 
        $old = $movie->toArray();
 
        if ($request->hasFile('poster')) {
            if ($movie->poster_path) Storage::disk('public')->delete($movie->poster_path);
            $validated['poster_path'] = $request->file('poster')->store('posters', 'public');
        }
 
        $movie->update($validated);
 
        AuditLog::write('MOVIE_UPDATED', "Movie \"{$movie->title}\" updated.",
            auth()->id(), Movie::class, $movie->id, $old, $movie->fresh()->toArray());
 
        return redirect()->route('admin.movies.index')
                         ->with('success', "Movie updated successfully.");
    }
 
    public function destroy(Movie $movie)
    {
        if ($movie->hasActiveRental()) {
            return back()->with('error', 'Cannot delete: this movie has an active rental.');
        }
 
        $title = $movie->title;
        AuditLog::write('MOVIE_DELETED', "Movie \"{$title}\" deleted by " . auth()->user()->name,
            auth()->id(), Movie::class, $movie->id, $movie->toArray(), []);
 
        $movie->delete();
 
        return redirect()->route('admin.movies.index')
                         ->with('success', "Movie \"{$title}\" deleted.");
    }
}