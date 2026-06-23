<?php
 
namespace App\Http\Controllers\User;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
 
class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query();
 
        if ($request->filled('genre'))  $query->where('genre', $request->genre);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where('title', 'like', '%' . $request->search . '%');
 
        $sortField = in_array($request->get('sort'), ['title','price_per_day','year']) ? $request->get('sort') : 'title';
        $query->orderBy($sortField);
 
        $movies = $query->paginate(12)->withQueryString();
        $genres = Movie::distinct()->pluck('genre')->sort()->values();
 
        return view('user.movies.index', compact('movies', 'genres'));
    }
 
    public function show(Movie $movie)
    {
        return view('user.movies.show', compact('movie'));
    }
}
