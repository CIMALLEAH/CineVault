<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\RentalController as AdminRentalController;
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
            $query = Rental::with('movie');

            if ($tab === 'active') $query->whereIn('status', ['active', 'overdue']);
            else                   $query->where('status', 'returned');

            $rentals = $query->latest('rental_date')->paginate(15)->withQueryString();

            return view('staff.rentals.index', compact('rentals', 'tab'));
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
            return view('staff.rentals.create', compact('movies'));
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
        ]);

        try {
            DB::beginTransaction();

            $movie = Movie::lockForUpdate()->findOrFail($validated['movie_id']);

            if (!$movie->isAvailable()) {
                DB::rollBack();
                return back()->with('error', 'This movie is not available.');
            }

            [$days, $dueDate, $priceBase, $totalAmount] = AdminRentalController::calculateRental(
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
            ]);

            $movie->rentOneCopy();

            AuditLog::write('RENTAL_CREATED',
                "Staff " . auth()->user()->name . " rented \"{$movie->title}\" to {$rental->customer_name} ({$rental->getRentalTypeLabel()}).",
                auth()->id(), Rental::class, $rental->id);

            DB::commit();

            return redirect()->route('staff.rentals.index')
                             ->with('success', "Rental confirmed! Total: ₱" . number_format($rental->total_amount, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create rental: ' . $e->getMessage());
        }
    }

    public function returnMovie(Rental $rental)
    {
        try {
            if ($rental->status === 'returned') {
                return back()->with('error', 'Already returned.');
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
}