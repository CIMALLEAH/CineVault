<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\RentalController as AdminRentalController;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index()
    {
        try {
            $rentals = Rental::with('movie')
                ->where('user_id', auth()->id())
                ->latest('rental_date')
                ->paginate(15);

            return view('user.rentals.index', compact('rentals'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load rentals: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id'          => 'required|exists:movies,id',
            'rental_type'       => 'required|in:screening,days,weeks',
            'quantity'          => 'required_unless:rental_type,screening|integer|min:1|max:8',
            'payment_method'    => 'required|in:cash,gcash,card',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $movie = Movie::lockForUpdate()->findOrFail($validated['movie_id']);

            if (!$movie->isAvailable()) {
                DB::rollBack();
                return back()->with('error', 'Sorry, this movie is not available.');
            }

            [$days, $dueDate, $priceBase, $totalAmount] = AdminRentalController::calculateRental(
                $movie, $validated['rental_type'], $validated['quantity'] ?? 1
            );

            $rental = Rental::create([
                'movie_id'            => $movie->id,
                'user_id'             => auth()->id(),
                'customer_name'       => auth()->user()->name,
                'customer_contact'    => auth()->user()->phone,
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
                auth()->user()->name . " rented \"{$movie->title}\" ({$rental->getRentalTypeLabel()}).",
                auth()->id(), Rental::class, $rental->id);

            DB::commit();

            return redirect()->route('user.rentals.index')
                             ->with('success', "Rental confirmed! Total: ₱" . number_format($rental->total_amount, 2) . ". Due: {$rental->due_date->format('M d, Y')}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process rental: ' . $e->getMessage());
        }
    }
}