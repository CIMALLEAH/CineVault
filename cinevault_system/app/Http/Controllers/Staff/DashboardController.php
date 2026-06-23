<?php
 
namespace App\Http\Controllers\Staff;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\Approval;
 
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'available_movies'   => Movie::where('status', 'available')->count(),
            'active_rentals'     => Rental::where('status', 'active')->count(),
            'my_pending_requests'=> Approval::where('requested_by', auth()->id())->pending()->count(),
            'processed_today'    => Rental::where('processed_by', auth()->id())
                                          ->whereDate('created_at', today())->count(),
        ];
 
        $recentRentals = Rental::with('movie')
            ->where('processed_by', auth()->id())
            ->latest()
            ->take(5)
            ->get();
 
        $myApprovals = Approval::with('movie')
            ->where('requested_by', auth()->id())
            ->latest()
            ->take(5)
            ->get();
 
        $activeRentals = Rental::with('movie')
            ->where('status', 'active')
            ->latest()
            ->take(6)
            ->get();
 
        return view('staff.dashboard', compact('stats', 'recentRentals', 'myApprovals', 'activeRentals'));
    }
}