<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $tab   = $request->get('tab', 'active');
            $query = Rental::with(['movie', 'processedBy']);

            if ($tab === 'active')  $query->whereIn('status', ['active', 'overdue']);
            elseif ($tab === 'history') $query->where('status', 'returned');

            if ($request->filled('search')) {
                $query->where('customer_name', 'like', '%' . $request->search . '%');
            }

            $rentals = $query->latest('rental_date')->paginate(15)->withQueryString();

            // Auto-mark overdue
            Rental::where('status', 'active')
                  ->where('due_date', '<', now()->toDateString())
                  ->update(['status' => 'overdue']);

            return view('admin.rentals.index', compact('rentals', 'tab'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load rentals: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $movies = Movie::where('available_copies', '>', 0)
                           ->where('status', '!=', 'inactive')
                           ->orderBy('title')
                           ->get();
            return view('admin.rentals.create', compact('movies'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load create form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id'          => 'required|exists:movies,id',
            'customer_name'     => 'required|string|max:255',
            'customer_contact'  => 'nullable|string|max:30',
            'rental_type'       => 'required|in:screening,days,weeks',
            'quantity'          => 'required_unless:rental_type,screening|integer|min:1|max:52',
            'payment_method'    => 'required|in:cash,gcash,card',
            'payment_reference' => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $movie = Movie::lockForUpdate()->findOrFail($validated['movie_id']);

            if (!$movie->isAvailable()) {
                DB::rollBack();
                return back()->with('error', 'This movie is not available for rent.');
            }

            [$days, $dueDate, $priceBase, $totalAmount] = $this->calculateRental(
                $movie, $validated['rental_type'], $validated['quantity'] ?? 1
            );

            $rental = Rental::create([
                'movie_id'            => $movie->id,
                'customer_name'       => $validated['customer_name'],
                'customer_contact'    => $validated['customer_contact'],
                'rental_date'         => now()->toDateString(),
                'due_date'            => $dueDate,
                'rental_type'         => $validated['rental_type'],
                'days'                => $days,
                'price_per_day'       => $movie->price_per_day,
                'price_per_screening' => $movie->effective_screening_price,
                'price_base'          => $priceBase,
                'total_amount'        => $totalAmount,
                'payment_method'      => $validated['payment_method'],
                'payment_reference'   => $validated['payment_reference'],
                'status'              => 'active',
                'processed_by'        => auth()->id(),
                'notes'               => $validated['notes'],
            ]);

            $movie->rentOneCopy();

            AuditLog::write('RENTAL_CREATED',
                "Movie \"{$movie->title}\" rented to {$rental->customer_name} ({$rental->getRentalTypeLabel()}).",
                auth()->id(), Rental::class, $rental->id);

            DB::commit();

            return redirect()->route('admin.rentals.index')
                             ->with('success', "Rental created. Total: ₱" . number_format($rental->total_amount, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create rental: ' . $e->getMessage());
        }
    }

    public function returnMovie(Rental $rental)
    {
        try {
            if ($rental->status === 'returned') {
                return back()->with('error', 'This rental is already returned.');
            }

            DB::beginTransaction();

            $rental->update([
                'status'        => 'returned',
                'returned_date' => now()->toDateString(),
            ]);

            $rental->movie->returnOneCopy();

            AuditLog::write('RENTAL_RETURNED',
                "Movie \"{$rental->movie->title}\" returned by {$rental->customer_name}.",
                auth()->id(), Rental::class, $rental->id);

            DB::commit();

            return back()->with('success', 'Movie marked as returned.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process return: ' . $e->getMessage());
        }
    }

    // ─── Shared calculation logic ────────────────────────────────────────────────

    public static function calculateRental(Movie $movie, string $type, int $quantity): array
    {
        switch ($type) {
            case 'screening':
                // One-time screening: lasts for the movie's duration (or 1 day if no duration set)
                $days        = 1;
                $dueDate     = now()->addHours($movie->duration > 0 ? ceil($movie->duration / 60) + 2 : 4)
                                    ->toDateString();
                $priceBase   = $movie->effective_screening_price;
                $totalAmount = $priceBase;
                break;

            case 'weeks':
                $days        = $quantity * 7;
                $dueDate     = now()->addDays($days)->toDateString();
                $priceBase   = $movie->effective_weekly_price;
                $totalAmount = $priceBase * $quantity;
                break;

            default: // 'days'
                $days        = $quantity;
                $dueDate     = now()->addDays($days)->toDateString();
                $priceBase   = (float) $movie->price_per_day;
                $totalAmount = $priceBase * $days;
                break;
        }

        return [$days, $dueDate, $priceBase, $totalAmount];
    }
}