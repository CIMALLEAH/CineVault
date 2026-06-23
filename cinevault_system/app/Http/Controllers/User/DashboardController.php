<?php
 
namespace App\Http\Controllers\User;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
 
class DashboardController extends Controller
{
    public function index()
    {
        $activeRentals = Rental::with('movie')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'overdue'])
            ->latest()
            ->get();
 
        $rentalHistory = Rental::with('movie')
            ->where('user_id', auth()->id())
            ->where('status', 'returned')
            ->latest()
            ->take(5)
            ->get();
 
        $featuredMovies = Movie::where('status', 'available')
            ->inRandomOrder()
            ->take(6)
            ->get();
 
        return view('user.dashboard', compact('activeRentals', 'rentalHistory', 'featuredMovies'));
    }
}