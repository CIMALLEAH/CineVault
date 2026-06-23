<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\AuditLog;
use Illuminate\Http\Request;
 
class RentalController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'active');
 
        $query = Rental::with(['movie', 'processedBy']);
 
        if ($tab === 'active') {
            $query->whereIn('status', ['active', 'overdue']);
        } elseif ($tab === 'history') {
            $query->where('status', 'returned');
        }
        // 'payments' tab shows all for payment details
 
        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }
 
        $rentals = $query->latest('rental_date')->paginate(15)->withQueryString();
 
        // Auto-mark overdue
        Rental::where('status', 'active')
              ->where('due_date', '<', now()->toDateString())
              ->update(['status' => 'overdue']);
 
        return view('admin.rentals.index', compact('rentals', 'tab'));
    }
 
    public function create()
    {
        $movies = Movie::where('status', 'available')->orderBy('title')->get();
        return view('admin.rentals.create', compact('movies'));
    }
 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id'          => 'required|exists:movies,id',
            'customer_name'     => 'required|string|max:255',
            'customer_contact'  => 'nullable|string|max:30',
            'days'              => 'required|integer|min:1|max:30',
            'payment_method'    => 'required|in:cash,gcash,card',
            'payment_reference' => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);
 
        $movie = Movie::findOrFail($validated['movie_id']);
 
        if ($movie->status !== 'available') {
            return back()->with('error', 'This movie is not available for rent.');
        }
 
        $rental = Rental::create([
            'movie_id'          => $movie->id,
            'customer_name'     => $validated['customer_name'],
            'customer_contact'  => $validated['customer_contact'],
            'rental_date'       => now()->toDateString(),
            'due_date'          => now()->addDays($validated['days'])->toDateString(),
            'days'              => $validated['days'],
            'price_per_day'     => $movie->price_per_day,
            'total_amount'      => $movie->price_per_day * $validated['days'],
            'payment_method'    => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'],
            'status'            => 'active',
            'processed_by'      => auth()->id(),
            'notes'             => $validated['notes'],
        ]);
 
        $movie->update(['status' => 'rented']);
 
        AuditLog::write('RENTAL_CREATED',
            "Movie \"{$movie->title}\" rented to {$rental->customer_name} for {$rental->days} day(s).",
            auth()->id(), Rental::class, $rental->id);
 
        return redirect()->route('admin.rentals.index')
                         ->with('success', "Rental created. Total: ₱{$rental->total_amount}");
    }
 
    public function returnMovie(Rental $rental)
    {
        if ($rental->status === 'returned') {
            return back()->with('error', 'This rental is already returned.');
        }
 
        $rental->update([
            'status'        => 'returned',
            'returned_date' => now()->toDateString(),
        ]);
 
        $rental->movie->update(['status' => 'available']);
 
        AuditLog::write('RENTAL_RETURNED',
            "Movie \"{$rental->movie->title}\" returned by {$rental->customer_name}.",
            auth()->id(), Rental::class, $rental->id);
 
        return back()->with('success', "Movie marked as returned.");
    }
}