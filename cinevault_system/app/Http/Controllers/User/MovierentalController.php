<?php
 
namespace App\Http\Controllers\User;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
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
 
        $sortField = $request->get('sort', 'title');
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
 
// ─────────────────────────────────────────────────────────────────────────────
 
namespace App\Http\Controllers\User;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\AuditLog;
use Illuminate\Http\Request;
 
class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with('movie')
            ->where('user_id', auth()->id())
            ->latest('rental_date')
            ->paginate(15);
 
        return view('user.rentals.index', compact('rentals'));
    }
 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id'          => 'required|exists:movies,id',
            'days'              => 'required|integer|min:1|max:14',
            'payment_method'    => 'required|in:cash,gcash,card',
            'payment_reference' => 'nullable|string|max:100',
        ]);
 
        $movie = Movie::findOrFail($validated['movie_id']);
 
        if ($movie->status !== 'available') {
            return back()->with('error', 'Sorry, this movie is not available.');
        }
 
        $rental = Rental::create([
            'movie_id'         => $movie->id,
            'user_id'          => auth()->id(),
            'customer_name'    => auth()->user()->name,
            'customer_contact' => auth()->user()->phone,
            'rental_date'      => now()->toDateString(),
            'due_date'         => now()->addDays($validated['days'])->toDateString(),
            'days'             => $validated['days'],
            'price_per_day'    => $movie->price_per_day,
            'total_amount'     => $movie->price_per_day * $validated['days'],
            'payment_method'   => $validated['payment_method'],
            'payment_reference'=> $validated['payment_reference'],
            'status'           => 'active',
            'processed_by'     => auth()->id(),
        ]);
 
        $movie->update(['status' => 'rented']);
 
        AuditLog::write('RENTAL_CREATED',
            auth()->user()->name . " rented \"{$movie->title}\" for {$rental->days} day(s).",
            auth()->id(), Rental::class, $rental->id);
 
        return redirect()->route('user.rentals.index')
                         ->with('success', "Rental confirmed! Total: ₱{$rental->total_amount}. Due: {$rental->due_date}");
    }
}
