<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\User;
use App\Models\Approval;
use Illuminate\Support\Facades\DB;
 
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_movies'   => Movie::count(),
            'active_rentals' => Rental::where('status', 'active')->count(),
            'revenue_month'  => Rental::whereMonth('rental_date', now()->month)
                                      ->whereYear('rental_date', now()->year)
                                      ->sum('total_amount'),
            'total_users'    => User::where('role', 'user')->count(),
            'pending_approvals' => Approval::pending()->count(),
            'overdue_rentals'=> Rental::where('status', 'active')
                                      ->where('due_date', '<', now()->toDateString())
                                      ->count(),
        ];
 
        // Weekly revenue (last 7 days)
        $weeklyRevenue = Rental::select(
                DB::raw('DATE(rental_date) as day'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('rental_date', '>=', now()->subDays(6)->toDateString())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');
 
        // Genre breakdown
        $genreStats = Movie::select('genre', DB::raw('COUNT(*) as count'))
            ->groupBy('genre')
            ->orderByDesc('count')
            ->get();
 
        $activeRentals = Rental::with('movie')
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();
 
        $pendingApprovals = Approval::with('requester')
            ->pending()
            ->latest()
            ->take(5)
            ->get();
 
        return view('admin.dashboard', compact(
            'stats', 'weeklyRevenue', 'genreStats',
            'activeRentals', 'pendingApprovals'
        ));
    }
}